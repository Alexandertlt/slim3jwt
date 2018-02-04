<?php
/**
 * Выдачача списка типов абонементов
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/tseasons', function(Request $request, Response $response){
    // Проверка прав. Разрешено всем кроме клиентов
    preg_match('/admin|director|instructor|client/',$this->user_info->role, $matches);
    if (count($matches) == 0){
        return $response->withStatus(403)
            ->write('{"error":{"text":"Forbidden for: '.$this->user_info->role.'"}}');
    }

    $id_firm = $this->user_info->id_firm;

    $data = $request->getQueryParams();

    // Получаем список всех типов абонементов, разрешенных для текущей группы

    $sql = "SELECT `season_types`.* FROM `season_types` RIGHT JOIN `groups_stypes` ON `season_types`.`id_stype` = `groups_stypes`.`id_stype`
WHERE `season_types`.`id_firm` = $id_firm AND `groups_stypes`.`id_firm` = $id_firm AND `groups_stypes`.`id_group` = :id_group";
    $db = $this->db;
    $stmt = $db->prepare($sql);
    $stmt->execute(['id_group' => $data['id_group'] ]);

    $json = new stdclass;

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        $json->tseasons[]=$row; // array!
    }

    $db = null;

    return $response->write(json_encode($json));

});