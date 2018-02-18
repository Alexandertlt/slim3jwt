<?php
/**
 * Удаление абонемента
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->post('/seasons/del', function(Request $request, Response $response) {

    // Проверка прав. Разрешено всем, кроме клиентов
    preg_match('/s-admin|admin|director|instructor/', $this->user_info->role, $matches);
    if (count($matches) == 0) {
        return $response->withStatus(403)
            ->write('{"error":{"text":"Forbidden for: ' . $this->user_info->role . '"}}');
    }

    $id_firm = $this->user_info->id_firm;
    $id_user = $this->user_info->id_user;

    $params = $request->getParsedBody();
    $db = $this->db;


    // Инструктор может удалаять только абонемент, по которому нет посещений. ..И который добавил сам инструктор??

    if ($this->user_info->role == 'instructor'){
        $sql = "SELECT COUNT(*) AS `count` FROM `exercises` WHERE `id_firm` = $id_firm AND `id_seas`= :id_seas";

        $stmt = $db->prepare($sql);
        $stmt->execute([ 'id_seas' => $params['id_seas'] ]);

        if ($stmt->fetchObject()->count == 0){
            // Исполняем
            $sql = "DELETE FROM `seasons` WHERE `id_firm` = $id_firm AND `id_seas`= :id_seas";
            $stmt = $db->prepare($sql);
            $stmt->execute([ 'id_seas' => $params['id_seas'] ]);
            return $response->write('{"result":"success"}');
        } else {
            return $response->write('{"error":"Forbidden for used season"}');
        }
    }

    if ($this->user_info->role == 'director'){
        $sql = "DELETE FROM `seasons` WHERE `id_firm` = $id_firm AND `id_seas`= :id_seas";
        $stmt = $db->prepare($sql);
        $stmt->execute([ 'id_seas' => $params['id_seas'] ]);
        return $response->write('{"result":"success"}');
    }




});