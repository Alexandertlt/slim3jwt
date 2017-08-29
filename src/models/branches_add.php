<?php
/**
 * Добавление нового филиалы (адреса)
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->post('/branches/add', function(Request $request, Response $response) {

    // Проверка прав. Разрешено только директору
    preg_match('/director/', $this->user_info->role, $matches);
    if (count($matches) == 0) {
        return $response->withStatus(403)
            ->write('{"error":{"text":"Forbidden for: ' . $this->user_info->role . '"}}');
    }

    $id_firm = $this->user_info->id_firm;

    $params = $request->getParsedBody();
    $sql = "INSERT INTO `branches` SET `id_firm` = $id_firm, `name` = :name, `address` = :address, `phone` = :phone, `text` = :text, `ts` = NOW()";
    try {
        $db = $this->db;
        $stmt = $db->prepare($sql);
        $stmt->execute([ 'name' => $params['name'],
            'address' => $params['address'],
            'phone' => $params['phone'],
            'text' => $params['text'] ]);

    } catch (PDOException $e) {
        // Добавить проверку на дублирующиеся имена
        return $response->withStatus(500)
            ->write( '{"error":{"text":' . $e->getMessage() . '}}');
    }

    return $response->write('{"id_branch":'.$db->lastInsertId().'}');
});