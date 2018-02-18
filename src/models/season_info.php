<?php
/**
 * Получаем всю информацию по абонементу
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/season', function(Request $request, Response $response){
    // Проверка прав. Разрешено всем кроме клиентов
    preg_match('/s-admin|admin|director|instructor/',$this->user_info->role, $matches);
    if (count($matches) == 0){
        return $response->withStatus(403)
            ->write('{"error":{"text":"Forbidden for: '.$this->user_info->role.'"}}');
    }

    $id_firm = $this->user_info->id_firm;
    $params = $request->getQueryParams();
    $db = $this->db;

    $sql = "SELECT `seasons`.*, `season_types`.`name` AS `season_name`, `clients`.`name` AS `client_name`, `num_classes`, `num_freezes`, `days_freeze`,
(SELECT COUNT(*) FROM `exercises` WHERE `exercises`.`id_seas` = :id_seas AND `exercises`.`id_firm` = $id_firm AND `exercises`.`id_class` IS NOT NULL) AS `sum_exercises` FROM `seasons`
LEFT JOIN `season_types` ON `seasons`.`stype` = `season_types`.`id_stype`
LEFT JOIN `clients` ON `seasons`.`id_client` = `clients`.`id_client`
WHERE `seasons`.`id_seas` = :id_seas AND `seasons`.`id_firm` = $id_firm";

    $stmt = $db->prepare($sql);
    $stmt->execute(['id_seas' => $params['id_seas'] ]);


    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $db = null;

    return $response->write(json_encode($row, JSON_UNESCAPED_UNICODE ));

});