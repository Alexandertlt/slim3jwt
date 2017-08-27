<?php
/**
 * Генерация и выдача нового токена по логину-паролю
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Firebase\JWT\JWT;

$app->post('/login', function(Request $request, Response $response){

    $data = $request->getParsedBody();

    $login = $data['user_login'];
    $password = $data['user_password'];

    // Find a corresponding token.
    $sql = 'SELECT * FROM `users`
            WHERE `login` = :login AND `password` = :password';

    $token_from_db = false;
    try {
        $db = $this->db;
        $stmt = $db->prepare($sql);
        $stmt->bindParam('login', $login);
        $stmt->bindParam('password', $password);
        $stmt->execute();
        $user_from_db = $stmt->fetchObject();
        $db = null;

        if ($user_from_db) {
            $key = "your_secret_key";

            $payload = array(
                "iss"     => "http://your-domain.com",
                "iat"     => time(),
                "exp"     => time() + (3600 * 24 * 15),
                "context" => [
                    "user" => [
                        "user_login" => $login,
                        "user_id"    => $user_from_db->id_user
                    ]
                ]
            );

            $jwt = JWT::encode($payload, $key);

            return $response->withStatus(200)
                ->write(json_encode([
                    "token"      => $jwt,
                    "id_user" => $user_from_db->id_user
                ]));
        } else {
            return $response->withStatus(401)
                ->write('{"error":{"text":"Wrong login or password"}}');
        }
    } catch (PDOException $e) {
        echo '{"error":{"text":' . $e->getMessage() . '}}';
    }
        


});