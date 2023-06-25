<?php

declare(strict_types=1);

namespace App\Util\JWT;

use App\Entity\RefreshToken;
use App\Repository\RefreshTokenRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

final class JWTauth {
    private const LEEWAY = 60;

    public function __construct(
        private RefreshTokenRepository $refreshTokenRepository,
        private UserRepository $userRepository,
        private EntityManagerInterface $em,
    ) {
    }

    public function issueJWT(string $username): void{
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

    public function issueRefresh(string $username): void{
        $iat = new DateTimeImmutable();
        $payload =[
            'iat' => $iat->getTimestamp(),
            'iss' => $_ENV['HOSTNAME'],
            'nbf' => $iat->getTimestamp(),
            'expire' => $iat->modify('+30 days')->getTimestamp(),
            'user' => $username,
        ];

        $user = $this->userRepository->findOneBy(['username' => $username]);
        $jwt = JWT::encode($payload, $_ENV['SECRET'], 'HS256');
        $rt = new RefreshToken($jwt, $user);

        $this->em->persist($rt);
        $this->em->flush();

        setcookie('REFRESH', $jwt, time()+108000,"/",$_ENV['HOSTNAME'],false,true);
    }

    public function delete(string $token): void{
        $this->refreshTokenRepository->deleteRefreshToken($token);

        setcookie('BEARER', "", time()-9999,"/",$_ENV['HOSTNAME'],false,true);
        setcookie('REFRESH', "", time()-9999,"/",$_ENV['HOSTNAME'],false,true);
    }

    public static function verify($jwt): void{
        JWT::$leeway = self::LEEWAY;
        $decoded = JWT::decode($jwt, new Key($_ENV['SECRET'], 'HS256'));
        if(null === $decoded){
            throw new Exception('Invalid JWT', 403);
        }
    }
}

