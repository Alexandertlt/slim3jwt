<?php
/**
 * Добавление нового абонемента
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->post('/seasons/add', function(Request $request, Response $response) {

    // Проверка прав. Разрешено всем, кроме клиентов
    preg_match('/s-admin|admin|director|instructor/', $this->user_info->role, $matches);
    if (count($matches) == 0) {
        return $response->withStatus(403)
            ->write('{"error":{"text":"Forbidden for: ' . $this->user_info->role . '"}}');
    }

    $id_firm = $this->user_info->id_firm;
    $id_user = $this->user_info->id_user;

    $params = $request->getParsedBody();

    // Перед добавлением сделаем несколько проверок.
    // Не пересекается период действия с уже действующим абонементом. ПОТОМ.
    // Еще можно будет добавить алгоритм для учета праздничных дней

    $sql = "INSERT INTO `seasons` (`id_firm`, `id_client`, `purchase`, `origin`, `stype`, `has_classes`, `has_passes`, `starts`, `expiration`, `paid`, `promo`, `status`, `freeze_start`, `freeze_stop`, `id_group`, `note`)
SELECT $id_firm, :id_client, NOW(), $id_user, :id_stype, `num_classes`, `num_passes`, :dstart, :dstart + INTERVAL `period`-1 DAY, :pay, NULL, 'new', NULL, NULL, :id_group, :note
FROM `season_types` WHERE `id_stype` = :id_stype";
    try {
        $db = $this->db;
        $stmt = $db->prepare($sql);
        $stmt->execute([ 'id_client' => $params['id_client'],
            'id_stype' => $params['id_stype'],
            'pay' => $params['pay'],
            'note' => $params['note'],
            'id_group' => $params['id_group'],
            'dstart' => $params['dstart'] ]);

    } catch (PDOException $e) {

        return $response->withStatus(500)
            ->write( '{"error":{"text":' . $e->getMessage() . '}}');
    }

    return $response->write('{"id_season":'.$db->lastInsertId().'}');
});