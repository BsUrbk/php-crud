<?php
namespace App\Controller;

use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Firebase\JWT\JWT;
use Firebase\Jwt\Key;

class JWTauth extends AbstractController{
    public static function issueJWT(string $username): void{
        $iat = new DateTimeImmutable();
        $payload =[
            'iat' => $iat->getTimestamp(),
            'iss' => $_ENV['HOSTNAME'],
            'nbf' => $iat->getTimestamp(),
            'expire' => $iat->modify('+15 minutes')->getTimestamp(),
            'user' => $username,

        ];
        
        $jwt = JWT::encode($payload, $_ENV['SECRET'], 'HS256');
        setcookie('BEARER', $jwt, time()+900,"/",$_ENV['HOSTNAME'],false,true);
    }

    public static function verify($jwt){
        JWT::$leeway = 60;
        $decoded = JWT::decode($jwt, new Key($_ENV['SECRET'], 'HS256'));
        return $decoded;
    }
}

