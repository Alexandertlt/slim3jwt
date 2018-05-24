<?php
/**
 * Информация по запрошенному пользователю
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/user', function(Request $request, Response $response){
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

    $sql = "SELECT * FROM `users` WHERE `id_firm` = $id_firm AND `id_user`= :id_user";

    $stmt = $db->prepare($sql);
    $stmt->execute(['id_user' => isset($params['id_user']) ? $params['id_user'] : $this->user_info->id_user ]);

    $json = new stdclass;

    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        $json = $row; // array!

    } else {
        $json->error->text = 'This id_user does not exist.';
    }
    $db = null;

    return $response->write(json_encode($json, JSON_UNESCAPED_UNICODE ));

});