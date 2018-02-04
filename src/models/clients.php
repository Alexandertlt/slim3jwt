<?php
/**
 * Выдачача списка клиентов
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/clients', function(Request $request, Response $response){
    // Проверка прав. Разрешено всем кроме клиентов
    preg_match('/s-admin|admin|director|instructor/',$this->user_info->role, $matches);
    if (count($matches) == 0){
        return $response->withStatus(403)
            ->write('{"error":{"text":"Forbidden for: '.$this->user_info->role.'"}}');
    }

    $id_firm = $this->user_info->id_firm;
    $data = $request->getQueryParams();

    // Получаем всех клиентов по текущей фирме. Запрос меняем в зависимости от наличия фильтра

    If (isset($data['substr'])){
        $substr = trim($data['substr']);
        $json = [];
        if (iconv_strlen($substr) >= 3 && !strpos($substr, '%') && !strpos($substr, '_') ) {
          /*  $sql = "SELECT COUNT(*) AS `count` FROM `clients` WHERE `id_firm` = $id_firm AND `deleted` = 0 AND `name` LIKE :str ORDER BY `name` LIMIT 1000";
            $db = $this->db;
            $stmt = $db->prepare($sql);
            $stmt->execute(['str' => '%' . $data['substr'] . '%']);
            $count = $stmt->fetchObject()->count;
*/
            $sql = "SELECT * FROM `clients` WHERE `id_firm` = $id_firm AND `deleted` = 0 AND `name` LIKE :str  ORDER BY `name` LIMIT 1000";
            $db = $this->db;
            $stmt = $db->prepare($sql);
            $stmt->execute(['str' => '%' . $data['substr'] . '%']);


            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $json[] = $row; // array!
            }
        }

       // $json->count = $count;
    } else {
        $sql = "SELECT COUNT(*) AS `count` FROM `clients` WHERE `id_firm` = $id_firm AND `deleted` = 0 ORDER BY `name` LIMIT 1000";
        $db = $this->db;
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $count = $stmt->fetchObject()->count;

        $sql = "SELECT * FROM `clients` WHERE `id_firm` = $id_firm AND `deleted` = 0 ORDER BY `name` LIMIT 1000";
        $db = $this->db;
        $stmt = $db->prepare($sql);
        $stmt->execute();

        $json = new stdclass;
        $json->count = $count;
    }

    if ($count > 0) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $json->clients[] = $row; // array!
        }
    }
    $db = null;

    return $response->write(json_encode($json, JSON_UNESCAPED_UNICODE ));
  //  return $response->write('[{"id_client":"192","slug":"client-side-validation.html","name":"Варвара","meta_keyword":"","meta_description":"","created_at":"2016-04-07 18:52:07","updated_at":"2016-04-07 18:52:07"},{"id_client":"196","slug":"form-validation.html","name":"Варя","meta_keyword":"","meta_description":"","created_at":"2016-04-07 18:53:17","updated_at":"2016-04-07 18:53:17"}]');

});