<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\JWTauth;

class AuthController extends AbstractController{

    #[Route('/login', name: 'root', methods:['POST','HEAD'])]
    public function login(Request $req): Response{
        if($req->cookies->all('BEARER')){
            return $this->json(['message' => 'You are already logged in']);
        }else{
            JWTauth::issueJWT($req->username);
        }
        
        
        return $this->json([
            'message' => 'Hi',
        ]);
    }

    #[Route('/register', name: 'root', methods:['POST','HEAD'])]
    public function register(): Response{
        $jwt = JWTauth::issueJWT('dud');
        
        return $this->json([
            'message' => 'Hi',
        ]);
    }

    #[Route('/reset', name: 'root', methods:['POST','HEAD'])]
    public function reset(): Response{
        $jwt = JWTauth::issueJWT('dud');
        
        return $this->json([
            'message' => 'Hi',
        ]);
    }
}