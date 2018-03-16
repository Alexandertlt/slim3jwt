<?php
/**
 * Редактирование клиента
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->post('/client/edit', function(Request $request, Response $response) {

    // Проверка прав. Разрешено всем кроме клиентов
    preg_match('/s-admin|admin|director|instructor/', $this->user_info->role, $matches);
    if (count($matches) == 0) {
        return $response->withStatus(403)
            ->write('{"error":{"text":"Forbidden for: ' . $this->user_info->role . '"}}');
    }

    $id_firm = $this->user_info->id_firm;
    $db = $this->db;

    $params = $request->getParsedBody();
    $sql = "UPDATE `clients` SET `name` = :name, `phone` = :phone, `note` = :note"
        .', `status` = :status, `status_change` = NOW() WHERE `id_client` = :id_client';
    try {

        $stmt = $db->prepare($sql);
        $stmt->execute([ 'name' => $params['name'],
            'phone' => $params['phone'],
            'note' => $params['note'],
            'status' => 'edited',
            'id_client' => $params['id_client']]);

    } catch (PDOException $e) {
        // Добавить проверку на дублирующиеся имена
        return $response->withStatus(500)
            ->write( '{"error":{"text":' . $e->getMessage() . '}}');
    }

    return $response->write('{"res":"success"}');
});