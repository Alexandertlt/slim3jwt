<?php
/**
 * Добавление нового клиента
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->post('/clients/add', function(Request $request, Response $response) {

    // Проверка прав. Разрешено всем кроме клиентов
    preg_match('/s-admin|admin|director|instructor/', $this->user_info->role, $matches);
    if (count($matches) == 0) {
        return $response->withStatus(403)
            ->write('{"error":{"text":"Forbidden for: ' . $this->user_info->role . '"}}');
    }

    $id_firm = $this->user_info->id_firm;

    $params = $request->getParsedBody();
    $sql = "INSERT INTO `clients` SET `id_firm` = $id_firm, `name` = :name, `phone` = :phone, `sm_info` = :sm_info, `note` = :note, `origin` = :origin"
        .', `status` = :status, `status_change` = NOW(), `ts` = NOW()';
    try {
        $db = $this->db;
        $stmt = $db->prepare($sql);
        $stmt->execute([ 'name' => $params['name'],
            'phone' => $params['phone'],
            'sm_info' => null,
            'note' => $params['note'],
            'origin' => $params['origin'],
            'status' => $params['status'] ]);

    } catch (PDOException $e) {
        // Добавить проверку на дублирующиеся имена
        return $response->withStatus(500)
            ->write( '{"error":{"text":' . $e->getMessage() . '}}');
    }

    return $response->write('{"id_client":'.$db->lastInsertId().'}');
});