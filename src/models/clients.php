<?php
/**
 * Генерация и выдача нового токена по логину-паролю
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Firebase\JWT\JWT;

$app->post('/clients', function(Request $request, Response $response){

    print_r($this->user_info);

});