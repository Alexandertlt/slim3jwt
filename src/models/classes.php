<?php
/**
 * Выдачача списка групп на текущий день
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/classes', function(Request $request, Response $response){
    // Проверка прав. Разрешено всем кроме клиентов
    preg_match('/admin|director|instructor/',$this->user_info->role, $matches);
    if (count($matches) == 0){
        return $response->withStatus(403)
            ->write('{"error":{"text":"Forbidden for: '.$this->user_info->role.'"}}');
    }

    $id_firm = $this->user_info->id_firm;

    $data = $request->getQueryParams();

    $curdate = $data['date'];
    $db = $this->db;

    // Cписок групп
    $sql_ex = "SELECT `groups`.`id_group`, DATE(:cur_date) AS `date`, TIME_FORMAT(`time`, '%H:%i') AS `time`, `branches`.`name` AS `branch`, `directions`.`name` AS `direction`,
`groups`.`name` AS `group_name`, 'расписание' AS `schedule`, DATE_FORMAT(`classes`.`ts`, '%d.%m.%Y %H:%i') AS `calculated`, `classes`.`id_class` AS `id_class`,
IF(`canceleds`.`date` IS NOT NULL,`canceleds`.`reason` ,NULL) AS `canceled`,
(SELECT CONCAT(`surname`,' ',`name`) FROM `instructors` WHERE `instructors`.`id_instr` = `substitutions`.`id_instr`) AS `substitute`,
IF(`groups`.`id_instr` != :id_instr, 1, 0) AS `foreign_group`
FROM `groups`
JOIN `weekly_slots` ON `groups`.`id_group` = `weekly_slots`.`id_group`
LEFT JOIN `branches` ON `groups`.`id_branch` = `branches`.`id_branch`
LEFT JOIN `directions` ON `groups`.`id_dir` = `directions`.`id_dir`
LEFT JOIN `classes` ON `groups`.`id_group` = `classes`.`id_group` AND :cur_date = DATE(`classes`.`dt`)
LEFT JOIN `canceleds` ON `groups`.`id_group` = `canceleds`.`id_group` AND :cur_date = `canceleds`.`date`
LEFT JOIN `substitutions` ON `groups`.`id_group` = `substitutions`.`id_group` AND :cur_date = DATE(`substitutions`.`dt`)
WHERE `weekly_slots`.`day_of_week` = DAYOFWEEK(DATE(:cur_date)) AND `groups` .`id_firm` = :id_firm AND (`groups`.`id_instr`= :id_instr OR `substitutions`.`id_instr`= :id_instr)
AND DATE(:cur_date) BETWEEN `groups`.`start` AND `groups`.`end`
ORDER BY `weekly_slots`.`time`";

    // Список клиентов в группе
    $sql_in = "SELECT `seasons`.`id_client`, `seasons`.`id_seas`, `clients`.`name` AS `name`, `season_types`.`name` AS `ticket_name`, `season_types`.`short_name` AS `ticket_short_name`, (`bookings`.`id_book` IS NOT NULL) AS `booking`, `bookings`.`presence` AS `booking_presence`,
`season_types`.`num_classes`, `seasons`.`has_classes`,DATE_FORMAT(`seasons`.`expiration`,'%e %b') AS `expiration` , get_season_status(:id_firm, `seasons`.`id_seas`, DATE(:cur_datetime)) AS `status`, `exercises`.`presence`, `seasons`.`used_classes`, `exercises`.`count_class`,
`debts`.`val` AS `debt`, `debts`.`id_debt`
FROM `seasons`
LEFT JOIN `clients` ON `seasons`.`id_client` = `clients`.`id_client`
LEFT JOIN `season_types` ON `seasons`.`stype` = `season_types`.`id_stype`
LEFT JOIN `debts` ON `seasons`.`id_client` = `debts`.`id_client` AND `debts`.`canceled` IS NULL
LEFT JOIN `bookings` ON `seasons`.`id_seas` = `bookings`.`id_seas` AND `bookings`.`dt` = :cur_datetime AND `bookings`.`canceled` IS NULL
LEFT JOIN `exercises` ON `seasons`.`id_group` = `exercises`.`id_group` AND `seasons`.`id_client` = `exercises`.`id_client` AND `exercises`.`dt` = :cur_datetime
WHERE `seasons`.`id_group` = :id_group AND get_season_status(:id_firm, `seasons`.`id_seas`, DATE(:cur_datetime)) IN ('active', 'new', 'frozen') AND `seasons`.`starts` <= :cur_datetime AND `seasons`.`id_firm` = :id_firm
ORDER BY `clients`.`name`";

    // Список недавних клиентов для кнопки "+"
    $sql_lt = "SELECT `seasons`.`id_client`, `seasons`.`id_seas`, `clients`.`name` AS `name`
FROM `seasons`
LEFT JOIN `clients` ON `seasons`.`id_client` = `clients`.`id_client`
WHERE `seasons`.`id_group` = :id_group AND get_season_status(:id_firm, `seasons`.`id_seas`, DATE(:cur_datetime)) = 'isover' AND `seasons`.`starts` <= :cur_datetime AND `seasons`.`id_firm` = :id_firm";

    $db->exec("SET lc_time_names = 'ru_RU'");

    $stmt = $db->prepare($sql_ex);
    $stmt->execute([ 'cur_date' => $curdate,
        'id_firm' => $id_firm,
        'id_instr' => $this->user_info->id_instr ]);

   // Внешний цикл. Перебор занятий по текущему дню
    $json = new stdclass;
    $json->curdate = $curdate;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){

        $istmt = $db->prepare($sql_in);
        $istmt->execute([ 'cur_datetime' => $row['date'].' '.$row['time'],
                            'id_group' => $row['id_group'],
                            'id_firm' => $id_firm ]);
        while ($client = $istmt->fetch(PDO::FETCH_ASSOC)){
            $row['clients'][] = $client;
        }

        $ltstmt = $db->prepare($sql_lt);
        $ltstmt->execute([ 'cur_datetime' => $row['date'].' '.$row['time'],
            'id_group' => $row['id_group'],
            'id_firm' => $id_firm ]);
        while ($ltclient = $ltstmt->fetch(PDO::FETCH_ASSOC)){
            $row['later_clients'][] = $ltclient;
        }

        $json->r[] = $row;
    }

    $db = null;

    return $response->write(json_encode($json,JSON_UNESCAPED_UNICODE));
});