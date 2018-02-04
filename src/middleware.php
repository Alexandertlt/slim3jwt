<?php
// Application middleware

use \Firebase\JWT\JWT;


$app->add(function ($req, $res, $next) {
    // CORS! For preflight:
    if ($req->isOptions()){
        $response = $next($req, $res);
        return $response
            ->withHeader('Access-Control-Allow-Origin', 'http://myseason')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        exit;
    }

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
            $sql = 'SELECT `users`.*, `instructors`.`id_instr` FROM `users`
LEFT JOIN `instructors` ON `users`.`id_user` = `instructors`.`id_user`
WHERE `users`.`id_user` = :id_user AND `users`.`disabled` = 0';
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
            ->withHeader('Access-Control-Allow-Origin', 'http://myseason')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
            ->withHeader('Content-Type', 'application/json; charset=utf-8');

    } else {
        http_response_code(403);
        exit;
    }

});

/*
$app->response->headers->set('Access-Control-Allow-Origin', 'http://myseason');
$app->response->headers->set('Access-Control-Allow-Headers','Content-Type, Authorization, Accept, X-Requested-With');
 */