<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\RefreshToken;
use App\Entity\User;
use App\Exception\UniqueUserException;
use App\Repository\RefreshTokenRepository;
use App\Repository\UserRepository;
use App\Util\JWT\JWTauth;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;

final class AuthService
{
    private const REFRESH_TOKEN = 'REFRESH';


    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $em,
        private RefreshTokenRepository $refreshTokenRepository,
        private JWTauth $JWTauth,
    ) {
    }

    public function registerNewUser(array $userData): void {
        $user  = $this->userRepository->findOneBy([User::USERNAME => $userData[User::USERNAME]]);
        if (null !== $user) {
            throw new UniqueUserException();
        }

        $newUser = (new User())
            ->setUsername($userData[User::USERNAME])
            ->setPassword(password_hash($userData[User::PASSWORD], PASSWORD_BCRYPT, ['cost' => 13]))
            ->setFirstName($userData[User::FIRSTNAME] ?? '')
            ->setLastName($userData[User::LASTNAME] ?? '')
            ->setEmail($userData[User::EMAIL])
        ;

        $this->em->persist($newUser);
        $this->em->flush();
    }

    public function loginUser(Request $requestData): void {
        $content = $requestData->toArray();
        $user = $this->userRepository->findOneBy([User::USERNAME => $content[User::USERNAME]]);
        $token = $this->refreshTokenRepository->findOneBy([RefreshToken::USERTOKEN => $user]);

        if(null !== $token){
            $this->JWTauth->delete($token->getToken());
        } elseif (null === $user) {
            throw new Exception('Invalid username or password', 400);
        }
        $hash = $user->getPassword();

        if(null === $hash){
            throw new Exception('Invalid username or password', 400);
        }
        $verify = password_verify($content[User::PASSWORD], $hash);

        if($verify){
            $this->JWTauth->issueJWT($content[User::USERNAME]);
            $this->JWTauth->issueRefresh($content[User::USERNAME]);
        }else{
            throw new Exception('Invalid username or password', 400);
        }
    }

    public function refreshToken(Request $request): void {
        $token = $this->refreshTokenRepository->findOneBy([RefreshToken::TOKEN => $request->cookies->get(RefreshToken::REFRESH)]);
        if($token){
            $user = $token->getUsertoken();
            $this->JWTauth->issueJWT($user->getUsername());
        }else{
            throw new Exception('Invalid refresh token', 403);
        }
    }

    public function resetPassword(Request $request): void {
        $content = $request->toArray();
        $user = $this->userRepository->findOneBy([User::EMAIL => $content[User::EMAIL]]);
        if (null === $user) {
            throw new Exception('No user was found with given email', 404);
        }

        $user->setPassword(password_hash($content[User::PASSWORD], PASSWORD_BCRYPT, ['cost' => 13]));
        $this->em->persist($user);
        $this->em->flush();
    }
}