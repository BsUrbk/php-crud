<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\RefreshToken;
use App\Repository\RefreshTokenRepository;
use App\Service\AuthService;
use App\Util\AuthorizationTrait;
use App\Util\JWT\JWTauth;
use JetBrains\PhpStorm\Deprecated;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class AuthController extends AbstractController{
    use AuthorizationTrait;

    #[Route('/register', name: 'register', methods:['POST'])]
    public function register(
        Request $req,
        AuthService $authService,
    ): Response {
        try {
            $this->denyAuthorizedRequest($req);
            $authService->registerNewUser($req->toArray());
        } catch (Throwable $e) {
            return $this->json([
                'message' => $e->getMessage(),
            ], $e->getCode());
        }

        return $this->json(['message' => 'User successfully registered'], 200);
    }

    #[Route('/login', name: 'login', methods:['POST'])]
    public function login(
        Request $req,
        AuthService $authService,
    ): Response{
        try {
            $this->denyAuthorizedRequest($req);
            $authService->loginUser($req);
        } catch (Throwable $e) {
            return $this->json([
                'message' => $e->getMessage(),
            ], $e->getCode());
        }

        return $this->json([
            'message' => 'Login successful'
        ], 200);
    }

    #[Route('/refresh', name: 'refresh', methods:['GET'])]
    public function refresh(
        Request $req,
        AuthService $authService,
    ): Response{
        try {
            $this->denyUnauthorizedRequest($req);
            $authService->refreshToken($req);
        } catch (Throwable $e) {
            return $this->json([
                'message' => $e->getMessage(),
            ], $e->getCode());
        }

        return $this->json([
            'meesage' => 'Success'
        ], 200);
    }

    #[Route('/reset', name: 'reset', methods:['PUT'])]
    #[Deprecated('No 3rd party verification, anyone can reset the password')]
    public function reset(
        Request $req,
        AuthService $authService,
    ): Response{
        try {
            $this->denyAuthorizedRequest($req);
            $authService->resetPassword($req);
        } catch (Throwable $e) {
            return $this->json([
                'message' => $e->getMessage()
            ], $e->getCode());
        }

        return $this->json([
            'message' => 'Success',
        ], 200);
    }

    #[Route('/logout', name: 'logout', methods:['DELETE'])]
    public function logout(
        Request $req,
        RefreshTokenRepository $refreshTokenRepository,
        JWTauth $JWTauth,
    ): Response{
        try {
            $this->denyUnauthorizedRequest($req);
            $token = $refreshTokenRepository->findOneBy([RefreshToken::TOKEN => $req->cookies->get(RefreshToken::REFRESH)]);
            if(null !== $token){
                $JWTauth->delete($token->getToken());
            }
        } catch (Throwable $e) {
            return $this->json([
                'message' => $e->getMessage(),
            ], $e->getCode());
        }

        return $this->json(['message' => 'Success'], 200);
    }
}