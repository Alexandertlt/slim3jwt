<?php
/**
 * Рассчет занятия
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->post('/classes/calc', function(Request $request, Response $response) {

    // Проверка прав. Разрешено всем, кроме клиентов
    preg_match('/s-admin|admin|director|instructor/', $this->user_info->role, $matches);
    if (count($matches) == 0) {
        return $response->withStatus(403)
            ->write('{"error":{"text":"Forbidden for: ' . $this->user_info->role . '"}}');
    }

    $id_firm = $this->user_info->id_firm;
    $id_user = $this->user_info->id_user;
    $db = $this->db;
    $params = $request->getParsedBody();



    $sql = "CALL `class_calc`(:id_group, :dt, :id_instr, $id_firm)";

    $stmt = $db->prepare($sql);
    $stmt->execute([ 'id_group' => $params['id_group'],
        'dt' => $params['dt'],
        'id_instr' => $this->user_info->id_instr ]);

    $res = $stmt->fetchObject();

    return $response->write($res->res);


});