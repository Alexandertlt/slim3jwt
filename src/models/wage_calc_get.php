<?php
/**
 * Выдача одной расчетки
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/wages/[{id_wage}]', function(Request $request, Response $response, array $args){
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

   $this->logger->info($args['id_wage']);
    $json = Array();

    $sql = 'SELECT * FROM `wage_calc` WHERE `id_wage`='. $args['id_wage'] . ' AND `id_firm`='. $id_firm;

    foreach ($db->query($sql) as $row) {
        $json[] = $row;
    }

    $db = null;

    return $response->write(json_encode($json, JSON_UNESCAPED_UNICODE ));
});