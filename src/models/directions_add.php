<?php
/**
 * Добавление нового клиента
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->post('/directions/add', function(Request $request, Response $response) {

    // Проверка прав. Разрешено всем кроме клиентов
    preg_match('/director/', $this->user_info->role, $matches);
    if (count($matches) == 0) {
        return $response->withStatus(403)
            ->write('{"error":{"text":"Forbidden for: ' . $this->user_info->role . '"}}');
    }

    $id_firm = $this->user_info->id_firm;

    $params = $request->getParsedBody();
    $sql = "INSERT INTO `directions` SET `id_firm` = $id_firm, `name` = :name, `text` = :text, `wear_info` = :wear_info, `cost_info` = :cost_info, `picture` = :picture"
        .', `ts` = NOW()';
    try {
        $db = $this->db;
        $stmt = $db->prepare($sql);
        $stmt->execute([ 'name' => $params['name'],
            'text' =>$params['text'],
            'wear_info' => $params['wear_info'],
            'cost_info' => $params['cost_info'],
            'picture' => $params['picture'] ]);

    } catch (PDOException $e) {
        // Добавить проверку на дублирующиеся имена
        return $response->withStatus(500)
            ->write( '{"error":{"text":' . $e->getMessage() . '}}');
    }

    return $response->write('{"id_direction":'.$db->lastInsertId().'}');
});