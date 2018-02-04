<?php
/**
 * Начальный экран WEB-интерфейса
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/main', function(Request $request, Response $response){
    // Проверка прав. Разрешено всем кроме клиентов
 /*   preg_match('/admin|director|instructor/',$this->user_info->role, $matches);
    if (count($matches) == 0){
        return $response->withStatus(403)
            ->write('{"error":{"text":"Forbidden for: '.$this->user_info->role.'"}}');
    }
*/
    return $response->write('Ok!');
});