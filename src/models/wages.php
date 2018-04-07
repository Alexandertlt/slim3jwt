<?php
/**
 * Выдачача расчетов зп по клиенту
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/wages', function(Request $request, Response $response){
    // Проверка прав. Разрешено всем кроме клиентов
    preg_match('/s-admin|admin|director|instructor/',$this->user_info->role, $matches);
    if (count($matches) == 0){
        return $response->withStatus(403)
            ->write('{"error":{"text":"Forbidden for: '.$this->user_info->role.'"}}');
    }

    $id_firm = $this->user_info->id_firm;
    $data = $request->getQueryParams();
    $id_instr = $this->user_info->id_instr;
    $db = $this->db;

   // $this->logger->info($data->yaer);

    $y = isset($data['year']) ? $data['year'] : date('Y');

    $sql = "SELECT `id_wage`, CONCAT(`dstart`, ' -- ', `dstop`) AS `period` FROM `wages` WHERE `id_firm` = $id_firm
 AND `id_instr` = $id_instr AND YEAR(`dstart`) = $y ORDER BY `dstart` DESC";

    $json = new stdclass;


    foreach ($db->query($sql) as $row) {
        $json->wages[] = $row;
    }

    $db = null;

    return $response->write(json_encode($json, JSON_UNESCAPED_UNICODE ));
});