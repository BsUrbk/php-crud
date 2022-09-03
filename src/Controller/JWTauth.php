<?php
namespace App\Controller;

use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Entity\User;
use App\Entity\RefreshToken;
use Doctrine\Persistence\ManagerRegistry;

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

    public static function issueRefresh(ManagerRegistry $doctrine, string $username): void{
        $entityManager = $doctrine->getManager();

        $iat = new DateTimeImmutable();
        $payload =[
            'iat' => $iat->getTimestamp(),
            'iss' => $_ENV['HOSTNAME'],
            'nbf' => $iat->getTimestamp(),
            'expire' => $iat->modify('+30 days')->getTimestamp(),
            'user' => $username,
        ];

        $user = $doctrine->getRepository(User::class)->findOneBy(['username' => $username]);
        $jwt = JWT::encode($payload, $_ENV['SECRET'], 'HS256');
        $rt = new RefreshToken($jwt, $user);

        $entityManager->persist($rt);
        $entityManager->flush();

        setcookie('REFRESH', $jwt, time()+108000,"/",$_ENV['HOSTNAME'],false,true);
    }

    public static function delete(ManagerRegistry $doctrine, string $cookie): void{
        $conn = $doctrine->getConnection();

        $query = '
        DELETE FROM "refresh_token"
        WHERE token LIKE :cookie
        ';

        $stmt = $conn->prepare($query);
        $stmt->executeQuery(['cookie' => $cookie]);

        setcookie('BEARER', "", time()-9999,"/",$_ENV['HOSTNAME'],false,true);
        setcookie('REFRESH', "", time()-9999,"/",$_ENV['HOSTNAME'],false,true);
    }

    public static function verify($jwt){
        JWT::$leeway = 60;
        $decoded = JWT::decode($jwt, new Key($_ENV['SECRET'], 'HS256'));
        $decoded_array = (array) $decoded;
        return $decoded_array;
    }
}

