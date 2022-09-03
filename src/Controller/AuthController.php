<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Entity\RefreshToken;
use App\Controller\JWTauth;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;


class AuthController extends AbstractController{

    #[Route('/register', name: 'register', methods:['POST'])]
    public function register(Request $req, ManagerRegistry $doctrine): Response{
        if($req->cookies->get('BEARER')){
            return $this->json(['message' => 'You are already logged in'], 200);
        }

        $entityManager = $doctrine->getManager();

        $content = $req->toArray();

        $unique = $doctrine->getRepository(User::class)->findOneBy(['username' => $content['username']]);

        if($unique){
            return $this->json(['message' => 'User with such username already exists']);
        }

        $passwordHash = password_hash($content['password'], PASSWORD_BCRYPT, ['cost' => 13]);
        if(!isset($content['firstName'])){
            $content['firstName'] = "";
        }
        if(!isset($content['lastName'])){
            $content['lastName'] = "";
        }
        
        $user = new User($content['username'], $content['email'], $passwordHash, $content['firstName'], $content['lastName']);

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json(['message' => 'User successfully registered'], 200);
    }

    #[Route('/login', name: 'login', methods:['POST'])]
    public function login(ManagerRegistry $doctrine, Request $req): Response{
        if($req->cookies->get('BEARER')){
            return $this->json(['message' => 'You are already logged in']);
        }
        $content = $req->toArray();

        $token = $doctrine->getRepository(RefreshToken::class)->findOneBy(['token' => $req->cookies->get('REFRESH')]);
        if($token){
            JWTauth::delete($doctrine, $req->cookies->get('REFRESH'));
        }else{
            $user = $doctrine->getRepository(User::class)->findOneBy(['username' => $content['username']]);
            $token = $doctrine->getRepository(RefreshToken::class)->findOneBy(['usertoken' => $user]);
            if($token){
                JWTauth::delete($doctrine, $token->getToken());
            }     
        }

        $user = $doctrine->getRepository(User::class)->findOneBy(['username' => $content['username']]);
        $hash = $user->getPassword();

        if(is_null($hash)){
            return $this->json(['message' => 'Invalid username / password'], 400);
        }
        $verify = password_verify($content['password'], $hash);

        if($verify){
            JWTauth::issueJWT($content['username']);
            JWTauth::issueRefresh($doctrine, $content['username']);
            return $this->json([
                'message' => 'Success',
            ], 200);
        }else{
            return $this->json([
                'message' => 'Invalid username / password'
            ], 400);
        }
    }

    #[Route('/refresh', name: 'refresh', methods:['POST'])]
    public function refresh(ManagerRegistry $doctrine, Request $req): Response{
        if(is_null($req->cookies->get('REFRESH'))){
            return $this->json(['message' => 'You\'re not logged in']);
        }
        $token = $doctrine->getRepository(RefreshToken::class)->findOneBy(['token' => $req->cookies->get('REFRESH')]);
        if($token){
            $user = $token->getUsertoken();
            JWTauth::issueJWT($user->getUsername());
            return $this->json(['message' => 'success']);
        }else{
            return $this->json(['message' => 'Invalid refresh token'], 403);
        }
    }

    #[Route('/reset', name: 'reset', methods:['PUT'])]
    public function reset(ManagerRegistry $doctrine, Request $req): Response{
        if($req->cookies->get('BEARER')){
            return $this->json(['message' => 'You are already logged in'], 403);
        }
        $content = $req->toArray();
        $user = $doctrine->getRepository(User::class)->findOneBy(['email' => $content['email']]);
        $id = $user->getId();
        if(!$id){
            return $this->json(['message' => 'No user exists with such email'], 400);
        }
        $user->setEmail($content['newemail']);
        flush();
        return $this->json([
            'message' => 'Success',
        ], 200);
    }

    #[Route('/logout', name: 'logout', methods:['DELETE'])]
    public function logout(ManagerRegistry $doctrine, Request $req): Response{
        if(!$req->cookies->get('BEARER')){
            return $this->json(['message' => 'You\'re not logged in'], 403);
        }
        $token = $doctrine->getRepository(RefreshToken::class)->findOneBy(['token' => $req->cookies->get('REFRESH')]);
        if($token){
            JWTauth::delete($doctrine, $req->cookies->get('REFRESH'));
        }
        return $this->json(['message' => $token], 200);
    }
}