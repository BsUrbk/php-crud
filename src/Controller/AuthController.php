<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
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
        if(!isset($content['firstName']) || !isset($content['lastName'])){
            return $this->json(['message' => 'User firstName and lastName cannot be empty'], 400);
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

    #[Route('/reset', name: 'reset', methods:['PUT'])]
    public function reset(ManagerRegistry $doctrine, Request $req): Response{
        if($req->cookies->get('BEARER')){
            return $this->json(['message' => 'You are already logged in'], 400);
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
        $return = '';
        $test = JWTauth::verify($req->cookies->get('BEARER'));
        foreach($test as $thing){
            $return .= $thing;
        }
        JWTauth::delete($doctrine, $req->cookies->get('REFRESH'));
        return $this->json(['message' => $return], 200);
    }
}