<?php
/**
 * Разморозить замороженный абонемент
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->post('/seasons/unfreeze', function(Request $request, Response $response) {

    // Проверка прав. Разрешено всем, кроме клиентов
    preg_match('/s-admin|admin|director|instructor/', $this->user_info->role, $matches);
    if (count($matches) == 0) {
        return $response->withStatus(403)
            ->write('{"error":{"text":"Forbidden for: ' . $this->user_info->role . '"}}');
    }

    $id_firm = $this->user_info->id_firm;

    $id_instr = $this->user_info->id_instr;
    $db = $this->db;
    $params = $request->getParsedBody();

    // Определяем стутус абонемента

    $sql = "SELECT `status` FROM `seasons` WHERE `seasons`.`id_seas` = :id_seas AND `seasons`.`id_firm` = $id_firm";

    $stmt = $db->prepare($sql);
    $stmt->execute([ 'id_seas' => $params['id_seas'] ]);

    $res = $stmt->fetchObject();
    if ($res->status == 'isover' || $res->status == 'closed') return $response->write('{"error":"Абонемент недействительный"}');
    if ($res->status != 'frozen') return $response->write('{"error":"Абонемент не заморожен"}');

    // Исполняем
    $sql = "UPDATE `seasons` SET `status` = 'active', `freeze_stop` = :unfreeze_dt - INTERVAL 1 DAY, `status_dt` = NOW()
WHERE `id_firm` = $id_firm AND `id_seas`= :id_seas";
    $stmt = $db->prepare($sql);
    $stmt->execute([ 'id_seas' => $params['id_seas'],
        'unfreeze_dt' => $params['unfreeze_dt'] ]);

    return $response->write('{"result":"success"}');

});