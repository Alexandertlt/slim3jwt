<?php
/**
 * Подтверждение бронирования
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->post('/booking/confirm', function(Request $request, Response $response) {

    // Проверка прав. Разрешено всем, кроме клиентов
    preg_match('/s-admin|admin|director|instructor/', $this->user_info->role, $matches);
    if (count($matches) == 0) {
        return $response->withStatus(403)
            ->write('{"error":{"text":"Forbidden for: ' . $this->user_info->role . '"}}');
    }

    $id_firm = $this->user_info->id_firm;
    $id_user = $this->user_info->id_user;

    $params = $request->getParsedBody();

    $sql = "CALL `booking_confirm`($id_firm, $id_user, :id_seas, :pay, :note)";
    try {
        $db = $this->db;
        $stmt = $db->prepare($sql);
        $stmt->execute([ 'id_seas' => $params['id_seas'],
            'pay' => $params['pay'],
            'note' => $params['note'] ]);

    } catch (PDOException $e) {

        return $response->withStatus(500)
            ->write( '{"error":{"text":' . $e->getMessage() . '}}');
    }

    return $response->write('{"res":"success"}');
});