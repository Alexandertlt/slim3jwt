<?php
/**
 * Результат бронирования
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->post('/booking', function(Request $request, Response $response) {

    // Проверка прав. Разрешено всем, кроме клиентов
    preg_match('/s-admin|admin|director|instructor/', $this->user_info->role, $matches);
    if (count($matches) == 0) {
        return $response->withStatus(403)
            ->write('{"error":{"text":"Forbidden for: ' . $this->user_info->role . '"}}');
    }

    $id_firm = $this->user_info->id_firm;
    $id_user = $this->user_info->id_user;
    $id_instr = $this->user_info->id_instr;

    $params = $request->getParsedBody();

    switch ($params['result']){
        case 'confirmed':
            $sql = "CALL `booking_confirm`($id_firm, $id_user, :id_seas, :dt, :pay, :note)";
            try {
                $db = $this->db;
                $stmt = $db->prepare($sql);
                $stmt->execute([ 'id_seas' => $params['id_seas'],
                    'dt' =>$params['dt'],
                    'pay' => $params['pay'],
                    'note' => $params['note'] ]);

            } catch (PDOException $e) {

                return $response->withStatus(500)
                    ->write( '{"error":{"text":' . $e->getMessage() . '}}');
            }
            break;
        case 'notshow':
            $sql = "CALL `booking_notshow`($id_firm, :id_seas, :dt, $id_instr)";
            try {
                $db = $this->db;
                $stmt = $db->prepare($sql);
                $stmt->execute([ 'id_seas' => $params['id_seas'], 'dt' =>$params['dt'] ]);

            } catch (PDOException $e) {

                return $response->withStatus(500)
                    ->write( '{"error":{"text":' . $e->getMessage() . '}}');
            }
            break;
        case 'canceled':
            break;
    }



    return $response->write('{"res":"success"}');
});