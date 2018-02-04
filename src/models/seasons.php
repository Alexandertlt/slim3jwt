<?php
/**
 * Выдачача списка абонементов по клиенту
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/seasons', function(Request $request, Response $response){
    // Проверка прав. Разрешено всем кроме клиентов
    preg_match('/s-admin|admin|director|instructor/',$this->user_info->role, $matches);
    if (count($matches) == 0){
        return $response->withStatus(403)
            ->write('{"error":{"text":"Forbidden for: '.$this->user_info->role.'"}}');
    }

    $id_firm = $this->user_info->id_firm;
    $params = $request->getQueryParams();

    // Получаем информацию по текущему клиенту.
    // В последствии вывод меняем в зависимости от роли $this->user_info->role

    $db = $this->db;

    // По умолчанию limit = 5
    if (!isset($params['limit'])) $params['limit'] = 5;

    $sql = "SELECT COUNT(*) AS `count` FROM `seasons` WHERE `id_firm` = $id_firm AND `id_client`= :id_client";

    $stmt = $db->prepare($sql);
    $stmt->execute(['id_client' => $params['id_client']]);
    $count = $stmt->fetchObject()->count;

    $sql = "SELECT `season_types`.`name`, `season_types`.`cost`,
  `id_seas`, `id_client`, `purchase`, `has_classes`, `has_passes`, `starts`, `expiration`, `paid`, `status`, `note`
  FROM `seasons` LEFT JOIN `season_types` ON `seasons`.`stype` = `season_types`.`id_stype`
 WHERE `seasons`.`id_firm` = $id_firm AND `id_client`= :id_client ORDER BY `status`, `purchase` DESC LIMIT ".filter_var($params['limit'],FILTER_SANITIZE_NUMBER_INT);

    $stmt = $db->prepare($sql);
    $stmt->execute(['id_client' => $params['id_client'] ]);

    $json = new stdclass;
    $json->count = $count;

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        $json->list[]=$row; // array!
    }
    $db = null;

    return $response->write(json_encode($json, JSON_UNESCAPED_UNICODE ));

});