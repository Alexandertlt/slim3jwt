<?php
/**
 * Добавление нового платежа, не связанного с абонементом
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->post('/payments/add', function(Request $request, Response $response) {

    // Проверка прав. Разрешено всем, кроме клиентов
    preg_match('/s-admin|admin|director|instructor/', $this->user_info->role, $matches);
    if (count($matches) == 0) {
        return $response->withStatus(403)
            ->write('{"error":{"text":"Forbidden for: ' . $this->user_info->role . '"}}');
    }

    $id_firm = $this->user_info->id_firm;
    $id_user = $this->user_info->id_user;

    $params = $request->getParsedBody();

    $origin = ($this->user_info->role == 'instructor' ? ',`id_instr`='.$id_user :
        ($this->user_info->role == 'admin' ? ',`id_admin`='.$id_user : '') );

    $sql = "INSERT INTO `payments` SET `id_firm` = $id_firm, `dt` = NOW() $origin, `id_client` = :id_client, `summ`= :pay, `note`= :note";
    $this->logger->info('role:'.$this->user_info->role.'   sql:'.$sql);
    try {
        $db = $this->db;
        $stmt = $db->prepare($sql);
        $stmt->execute([ 'id_client' => $params['id_client'],
            'pay' => $params['pay'],
            'note' => $params['note'] ]);

    } catch (PDOException $e) {

        return $response->withStatus(500)
            ->write( '{"error":{"text":' . $e->getMessage() . '}}');
    }

    return $response->write('{"id_season":'.$db->lastInsertId().'}');
});