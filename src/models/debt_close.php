<?php
/**
 * Клиент гасит долг
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->post('/debts/close', function(Request $request, Response $response) {

    // Проверка прав. Разрешено всем кроме клиентов
    preg_match('/s-admin|admin|director|instructor/', $this->user_info->role, $matches);
    if (count($matches) == 0) {
        return $response->withStatus(403)
            ->write('{"error":{"text":"Forbidden for: ' . $this->user_info->role . '"}}');
    }

    $id_firm = $this->user_info->id_firm;
    $id_user = $this->user_info->id_user;
    $db = $this->db;
    $params = $request->getParsedBody();

    $sql = "CALL `debt_close`($id_firm, $id_user, :id_debt, :summ)";
    try {

        $stmt = $db->prepare($sql);
        $stmt->execute([ 'id_debt' => $params['id_debt'],
            'summ' => $params['summ'] ]);

    } catch (PDOException $e) {
        // Добавить проверку на дублирующиеся имена
        return $response->withStatus(500)
            ->write( '{"error":{"text":' . $e->getMessage() . '}}');
    }

    return $response->write('{"success":true}');
});