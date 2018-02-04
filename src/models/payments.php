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
    $params = $request->getQueryParams();
    $db = $this->db;

    $json = new stdclass;
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

    }

    // Получаем список платежей по инстсруктору
    if (isset($params['id_instr'])) {
        $sql = "SELECT COUNT(*) AS `count`, SUM(`summ`) AS `sum_payments` FROM `payments` WHERE `id_firm` = $id_firm AND `id_instr`= :id_instr";

        $stmt = $db->prepare($sql);
        $stmt->execute(['id_instr' => $params['id_instr']]);
        $fetchRow = $stmt->fetchObject();

        $sql = "SELECT * FROM `payments` WHERE `id_firm` = $id_firm AND `id_instr`= :id_instr ORDER BY `dt`";

        $stmt = $db->prepare($sql);
        $stmt->execute(['id_instr' => $params['id_instr']]);
        $json->count = $fetchRow->count;
        $json->balance = $fetchRow->sum_payments;
    }

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        $json->list[]=$row; // array!
    }
    $db = null;

    return $response->write(json_encode($json, JSON_UNESCAPED_UNICODE ));

});