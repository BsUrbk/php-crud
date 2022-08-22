<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Controller\JWTauth;
use Doctrine\Persistence\ManagerRegistry;


class AuthController extends AbstractController{

    #[Route('/login', name: 'login', methods:['POST'])]
    public function login(Request $req): Response{
        if($req->cookies->get('BEARER')){
            return $this->json(['message' => 'You are already logged in']);
        }
        $content = $req->toArray();
        $user = new User();
        $user->setUsername($content['username']);
        $hash = $user->getPassword();
        if(!$hash){
            return $this->json(['message' => 'Invalid username / password']);
        }
        $verify = password_verify($content['password'], $hash);

        //$user = new User();
        if($verify){
            JWTauth::issueJWT($content['username']);
            JWTauth::issueRefresh($content['username']);
            return $this->json([
                'message' => 'Success',
            ]);
        }else{
            return $this->json([
                'message' => 'Invalid username / password'
            ]);
        }
    }

    #[Route('/reset', name: 'reset', methods:['PUT'])]
    public function reset(Request $req): Response{
        if($req->cookies->get('BEARER')){
            return $this->json(['message' => 'You are already logged in']);
        }
        $content = $req->toArray();
        $user = new User();
        $user->setEmail($content['email']);
        $id = $user->getId();
        if(!$id){
            return $this->json(['message' => 'No user exists with such email']);
        }
        $user->setEmail($content['newemail']);
        flush();
        return $this->json([
            'message' => 'Success',
        ]);
    }

    #[Route('/logout', name: 'logout', methods:['DELETE'])]
    public function logout(ManagerRegistry $doctrine, Request $req): Response{
        if(!$req->cookies->get('BEARER')){
            return $this->json(['message' => 'You\'re not logged in']);
        }
        JWTauth::delete($doctrine, $req->cookies->get('REFRESH'));
        return $this->json(['message' => 'Logged out']);
    }
}