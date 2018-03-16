<?php
/**
 * Выдачача списка платежей по условиям
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/payments', function(Request $request, Response $response){
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

    $json = new stdclass;

    $between = '';
    if (isset($params['start']) && isset($params['stop'])) $between = ' AND `payments`.`dt` BETWEEN "'. $params['start'] . '" AND "' . $params['stop'] . '" ';
    // Получаем список платежей по клиенту
    if (isset($params['id_client'])) {
        $sql = "SELECT COUNT(*) AS `count` FROM `payments` WHERE `id_firm` = $id_firm AND `id_client`= :id_client";

        $stmt = $db->prepare($sql);
        $stmt->execute(['id_client' => $params['id_client']]);
        $fetchRow = $stmt->fetchObject();

        $sql = "SELECT * FROM `payments` WHERE `id_firm` = $id_firm AND `id_client`= :id_client ORDER BY `dt`";

        $stmt = $db->prepare($sql);
        $stmt->execute(['id_client' => $params['id_client']]);
        $json->count = $fetchRow->count;

    } else {
        // Получаем список всех операций текущего пользователя
        $fetchRow = $db->query("SELECT COUNT(*) AS `count`, SUM(`summ`) AS `sum_payments`, SUM(IF(`summ` > 0, `summ`, 0)) AS `incoming`, SUM(IF(`summ` < 0, `summ`, 0)) AS `outgoing`
FROM `payments` WHERE `id_firm` = $id_firm AND `id_user_holder`= $id_user $between")->fetch(PDO::FETCH_OBJ);

        $sql = "SELECT `payments`.*, `clients`.`name` AS `client_name`
FROM `payments` LEFT JOIN `clients` ON `payments`.`id_client` = `clients`.`id_client`
WHERE `payments`.`id_firm` = $id_firm AND `payments`.`id_user_holder`= $id_user $between ORDER BY `id_pay`";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $json->count = $fetchRow->count;
        $json->incoming = $fetchRow->incoming;
        $json->outgoing = $fetchRow->outgoing;
        $json->balance = $fetchRow->sum_payments;
    }

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        $json->list[]=$row; // array!
    }
    $db = null;

    return $response->write(json_encode($json, JSON_UNESCAPED_UNICODE ));

});