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
    $json  = new stdclass;

    $sql = 'SELECT *, (SELECT `clients`.`name` FROM `clients` WHERE `wage_calc`.`id_client` = `clients`.`id_client`) AS `client_name`,
(SELECT `season_types`.`short_name` FROM `season_types` WHERE `wage_calc`.`id_stype` = `season_types`.`id_stype`) AS `season_short_name`
FROM `wage_calc` WHERE `id_wage`='. $args['id_wage'] . ' AND `id_firm`='. $id_firm;

    foreach ($db->query($sql) as $row) {
        $json->list[] = $row;
    }

    $sql = "SELECT `wages`.*, `invoices`.`id_invoice`, `invoices`.`summ` AS `invoice_summ` FROM `wages`
LEFT JOIN `invoices` ON `wages`.`id_wage` = `invoices`.`id_wage`
WHERE `wages`.`id_wage` = ". $args['id_wage'].' AND `wages`.`id_firm`='. $id_firm;

    $json->info = $db->query($sql)->fetch(PDO::FETCH_OBJ);

   // $row = $stmt->fetch(PDO::FETCH_ASSOC);


    $db = null;

    return $response->write(json_encode($json, JSON_UNESCAPED_UNICODE ));
});