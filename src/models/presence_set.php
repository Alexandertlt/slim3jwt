<?php
/**
 * Отмечаем присутствие или неприсутсвие клиента на занятии.
 * Может всё перенести в SQL ???
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->post('/presence', function(Request $request, Response $response) {

    // Проверка прав. Разрешено всем кроме клиентов
    preg_match('/s-admin|admin|director|instructor/', $this->user_info->role, $matches);
    if (count($matches) == 0) {
        return $response->withStatus(403)
            ->write('{"error":{"text":"Forbidden for: ' . $this->user_info->role . '"}}');
    }

    $id_firm = $this->user_info->id_firm;
    $id_instr = $this->user_info->id_instr;

    $params = $request->getParsedBody();
    $db = $this->db;

    $p = explode('-', $params['cb']);

    $sql = "CALL `presence_set`($id_firm, :id_group, :id_seas, :dt, $id_instr)";


    $stmt = $db->prepare($sql);
    $stmt->execute([ 'id_group' => $p[1],
        'id_seas' => $p[3],
        'dt' => $p[0] ]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (isset($row['error'])) return $response->write($row['error']);

    if (isset($row['presence'])) return $response->write('{"cb":"'.$params['cb'].'","presence":'. $row['presence'] .'}');
});