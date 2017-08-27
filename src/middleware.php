<?php
// Application middleware

use \Firebase\JWT\JWT;


$app->add(function ($req, $res, $next) {
    $path = $req->getUri()->getPath();
    $matches = null;
    preg_match('/login|signup/', $path, $matches);  // Запросы, не требующие авторизации


    if (count($matches) == 0){
        $jwt = $req->getHeaders();

        $key = "your_secret_key";

        try {
            $decoded = JWT::decode($jwt['HTTP_AUTHORIZATION'][0], $key, array('HS256'));
        } catch (UnexpectedValueException $e) {
            echo $e->getMessage();
        }

        if (isset($decoded)) {
            // Ищем клиента в базе
            // Find a corresponding token.
            $sql = 'SELECT * FROM `users` WHERE `id_user` = :id_user AND `disabled` = 0';
            $this->user_info = $decoded;

            try {
                $db = $this->db;
                $stmt = $db->prepare($sql);
                $stmt->bindParam('id_user', $decoded->context->user->user_id);
                $stmt->execute();
                $user_from_db = $stmt->fetchObject();
                $db = null;

                if ($user_from_db) {
                    $this->user_info = $user_from_db;
                    $identified = true;
                } else {
                    $identified = false;
                }
            } catch (PDOException $e) {
                exit ('{"error":{"text":' . $e->getMessage() . '}}');
            }

        }
    } else {
        $identified = true;
    }



    if($identified) {

        $response = $next($req, $res);

        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST')
            ->withHeader('Content-Type', 'application/json');
    } else {
        http_response_code(403);
        exit;
    }

});