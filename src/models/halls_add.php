<?php
/**
 * Добавление нового зала
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->post('/halls/add', function(Request $request, Response $response) {

    // Проверка прав. Разрешено только директору
    preg_match('/director/', $this->user_info->role, $matches);
    if (count($matches) == 0) {
        return $response->withStatus(403)
            ->write('{"error":{"text":"Forbidden for: ' . $this->user_info->role . '"}}');
    }

    $id_firm = $this->user_info->id_firm;

    $params = $request->getParsedBody();
    $sql = "INSERT INTO `halls` SET `id_firm` = $id_firm, `name` = :name, `id_branch` = :id_branch, `decription` = :description, `ts` = NOW()";
    try {
        $db = $this->db;
        $stmt = $db->prepare($sql);
        $stmt->execute([ 'name' => $params['name'],
            'id_branch' => $params['id_branch'],
            'decription' => $params['decription'] ]);

    } catch (PDOException $e) {
        // Добавить проверку на дублирующиеся имена
        return $response->withStatus(500)
            ->write( '{"error":{"text":' . $e->getMessage() . '}}');
    }

    return $response->write('{"id_hall":'.$db->lastInsertId().'}');
});