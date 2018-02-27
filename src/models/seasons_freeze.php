<?php
/**
 * Заморозить абонемент
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->post('/seasons/freeze', function(Request $request, Response $response) {

    // Проверка прав. Разрешено всем, кроме клиентов
    preg_match('/s-admin|admin|director|instructor/', $this->user_info->role, $matches);
    if (count($matches) == 0) {
        return $response->withStatus(403)
            ->write('{"error":{"text":"Forbidden for: ' . $this->user_info->role . '"}}');
    }

    $id_firm = $this->user_info->id_firm;
    $id_user = $this->user_info->id_user;
    $db = $this->db;
    $params = $request->getParsedBody();

    $start_date = date_create_from_format('Ymd', $params['start_date']);
    $end_date = date_create_from_format('Ymd', $params['end_date']);
    $interval = $start_date->diff($end_date);


    // Сначала получаем инфрмацию по текущему абонементу, есть ли возможноть заморозить
    // Если есть рассчитанные занятия то заморозки проводить нельзя.

    $sql = "SELECT `seasons`.`has_frozes`, `seasons`.`status`, `has_days_frozes`, `num_freezes`, `days_freeze`,
(SELECT COUNT(*) FROM `exercises` WHERE `exercises`.`id_seas` = :id_seas AND `exercises`.`id_firm` = $id_firm AND `exercises`.`id_class` IS NOT NULL AND `exercises`.`dt` >= :start_date) AS `sum_exercises`
FROM `seasons`
LEFT JOIN `season_types` ON `seasons`.`stype` = `season_types`.`id_stype`
WHERE `seasons`.`id_seas` = :id_seas AND `seasons`.`id_firm` = $id_firm";

    $stmt = $db->prepare($sql);
    $stmt->execute([ 'id_seas' => $params['id_seas'],
        'start_date' => $params['start_date']]);

    $res = $stmt->fetchObject();
    if ($res->status == 'frozen') return $response->write('{"error":"Абонемент уже заморожен"}');
    if ($res->num_frozes != null and $res->has_frozes == 0) return $response->write('{"error":"Колечество достыпных заморозок было исчерпано"}');
    if ($res->days_freeze != null){
        if ($res->days_freeze == 0) return $response->write('{"error":"Для этого абонемента заморозки запрещены"}');
        $has_interval = new DateInterval('P'.$res->has_days_frozes.'D');
        if ($interval > $has_interval) return $response->write('{"error":"Запрашиваемый интервал заморозки больше возможного. Максимальное количество дней заморозки: '.$res->has_days_frozes.'"}');
    }
    if ($res->sum_exercises > 0) return $response->write('{"error":"За выбранный период уже есть рассчитанные занятия"}');


    // Исполняем
    $sql = "UPDATE `seasons` SET `status` = 'frozen', `freeze_start` = :start_date, `freeze_stop` = :end_date
WHERE `id_firm` = $id_firm AND `id_seas`= :id_seas";
    $stmt = $db->prepare($sql);
    $stmt->execute([ 'id_seas' => $params['id_seas'],
        'start_date' => $params['start_date'],
        'end_date' => $params['end_date'] ]);

    return $response->write('{"result":"success"}');


});