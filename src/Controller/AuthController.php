<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Controller\JWTauth;


class AuthController extends AbstractController{

    #[Route('/login', name: 'login', methods:['POST'])]
    public function login(Request $req): Response{
        if($req->cookies->get('BEARER')){
            return $this->json(['message' => 'You are already logged in']);
        }
        $content = $req->toArray();
        $user = new User();
        $user->setEmail($content['email']);
        $hash = $user->getPassword();
        if(!$hash){
            return $this->json(['message' => 'Invalid username / password']);
        }
        $verify = password_verify($content['password'], $hash);

        //$user = new User();
        if($verify){
            JWTauth::issueJWT($content['username']);
            return $this->json([
                'message' => 'Success',
            ]);
        }else{
            return $this->json([
                'message' => 'Invalid username / password'
            ]);
        }
    }

    #[Route('/reset', name: 'root', methods:['POST'])]
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
}