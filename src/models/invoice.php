<?php
/**
 * Выдачача актуального инвойса
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/invoice', function(Request $request, Response $response){
    // Проверка прав. Разрешено всем кроме клиентов
    preg_match('/s-admin|admin|director|instructor/',$this->user_info->role, $matches);
    if (count($matches) == 0){
        return $response->withStatus(403)
            ->write('{"error":{"text":"Forbidden for: '.$this->user_info->role.'"}}');
    }

    $id_firm = $this->user_info->id_firm;
    $id_user = $this->user_info->id_user;
    $params = $request->getQueryParams();
    $db = $this->db;


    $sql = "SELECT * FROM `invoices` WHERE `id_firm` = $id_firm AND `id_user`= $id_user AND `closed` = 0 
ORDER BY `id_invoice` DESC LIMIT 1";

    $row = new stdClass();
    $row->invoice = $db->query($sql)->fetch(PDO::FETCH_ASSOC);


    $db = null;

  //  if (!$row) $row = new stdClass();

    return $response->write(json_encode($row, JSON_UNESCAPED_UNICODE ));

});