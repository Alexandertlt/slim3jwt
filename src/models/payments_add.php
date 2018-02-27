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
    $db = $this->db;



    $row = $db->query("SELECT `balance` FROM `payments` WHERE `id_firm` = $id_firm AND  `id_user_holder` = $id_user ORDER BY `id_pay` DESC LIMIT 1")->fetch(PDO::FETCH_OBJ);

    $balance = $row->balance == null ? 0 : $row->balance;

    $sql = "INSERT INTO `payments` SET `id_firm` = $id_firm, `id_user_holder` = $id_user,`dt` = NOW(), `id_client` = :id_client, `summ`= :pay, `balance` = $balance + :pay, `note`= :note";

    $this->logger->info($balance);

    try {

        $stmt = $db->prepare($sql);
        $stmt->execute([ 'id_client' => $params['id_client'],
            'pay' => $params['pay'],
            'note' => $params['note'] ]);

    } catch (PDOException $e) {

        return $response->withStatus(500)
            ->write( '{"error":{"text":' . $e->getMessage() . '}}');
    }

    return $response->write('{"id_pay":'.$db->lastInsertId().'}');
});