<?php
/**
 * Выдачача списка направлений
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/directions', function(Request $request, Response $response){
    // Проверка прав. Разрешено всем кроме клиентов
    preg_match('/admin|director|instructor|client/',$this->user_info->role, $matches);
    if (count($matches) == 0){
        return $response->withStatus(403)
            ->write('{"error":{"text":"Forbidden for: '.$this->user_info->role.'"}}');
    }

    $id_firm = $this->user_info->id_firm;

    // Получаем всех клиентов по текущей фирме. Будут фильтры, но потом...

    $sql = "SELECT COUNT(*) AS `count` FROM `directions` WHERE `id_firm` = $id_firm ORDER BY `name` LIMIT 100";
    $db = $this->db;
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $count = $stmt->fetchObject()->count;

    $sql = "SELECT * FROM `directions` WHERE `id_firm` = $id_firm ORDER BY `name` LIMIT 100";
    $db = $this->db;
    $stmt = $db->prepare($sql);
    $stmt->execute();

    $json = new stdclass;
    $json->count = $count;

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        $json->directions[]=$row; // array!
    }

    $db = null;

    return $response->write(json_encode($json));

});