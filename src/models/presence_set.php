<?php
/**
 * Отмечаем присутствие или неприсутсвие клиента на занятии.
 * Может всё перенести в SQL ???
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->post('/presence', function(Request $request, Response $response) {

    // Проверка прав. Разрешено всем кроме клиентов
    preg_match('/s-admin|admin|director|instructor/', $this->user_info->role, $matches);
    if (count($matches) == 0) {
        return $response->withStatus(403)
            ->write('{"error":{"text":"Forbidden for: ' . $this->user_info->role . '"}}');
    }

    $id_firm = $this->user_info->id_firm;
    $id_instr = $this->user_info->id_instr;

    $params = $request->getParsedBody();
    $db = $this->db;

    $p = explode('-', $params['cb']);

    // Проверка абонемент
    $seas = $db->query("SELECT * FROM `seasons` WHERE `id_seas` = ".$p[3]." AND `id_firm`= $id_firm")->fetch(PDO::FETCH_ASSOC);

    if ($seas['status'] != 'active' && $seas['status'] != 'new') {
        return $response->write('{"err":{"error":"no_season", "err_text":"Нет активного абонемента."}}');
    }


    $sql = "SELECT `exercises`.`presence`, `classes`.`id_class`, `exercises`.`id_exer` FROM `exercises` 
LEFT JOIN `classes` ON `exercises`.`id_group` = `classes`.`id_group` AND `exercises`.`dt` = `classes`.`dt`
WHERE `exercises`.`id_firm` = $id_firm AND `exercises`.`id_group` = :id_group AND `exercises`.`id_client` = :id_client AND 
`exercises`.`id_seas` = :id_seas AND `exercises`.`dt` = :dt";


    $stmt = $db->prepare($sql);
    $stmt->execute([ 'id_group' => $p[1],
        'id_client' => $p[2],
        'id_seas' => $p[3],
        'dt' => $p[0] ]);

    $pres = 1;

    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        $sql = 'UPDATE `exercises` SET `presence` = !`presence` WHERE `id_exer` = '.$row['id_exer'];
        if ($row['presence'] === '1') $pres = 0;
    } else {
        $sql = 'INSERT INTO `exercises` SET `id_firm`='.$id_firm.', `id_client`='.$p[2].', `id_seas`='
            .$p[3].', `id_group`= '.$p[1].',`dt`="'.$p[0].'", `id_instr`= '.$id_instr.', `presence`=1, `type`="test"';
    }

    $stmt->closeCursor();

    $db->exec($sql);
    $cb = preg_replace('/[^0-9]/', '', '2018-01-29 18:00:00');
    return $response->write('{"cb":"'.$params['cb'].'","presence":'.$pres.'}');
});