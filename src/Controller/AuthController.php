<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Controller\JWTauth;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;

class AuthController extends AbstractController{

    #[Route('/register', name: 'register', methods:['POST'])]
    public function register(Request $req, ManagerRegistry $doctrine): JsonResponse{
        if($req->cookies->get('BEARER')){
            return $this->json(['message' => 'You are already logged in']);
        }

        $entityManager = $doctrine->getManager();

        $content = $req->toArray();
        $passwordHash = password_hash($content['password'], PASSWORD_BCRYPT);

        $user = new User();
        $user->setUsername($content['username']);
        $user->setEmail($content['email']);
        $user->setPassword($passwordHash);
        if(isset($content['firstName'])){
            $user->setFirstName($content['firstName']);
        }
        if(isset($content['lastName'])){
            $user->setLastName($content['lastName']);
        }
        

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['message' => 'User successfully registered'], 200);
    }

    #[Route('/login', name: 'login', methods:['POST'])]
    public function login(ManagerRegistry $doctrine, Request $req): JsonResponse{
        if($req->cookies->get('BEARER')){
            return $this->json(['message' => 'You are already logged in']);
        }
        $content = $req->toArray();

        $user = $doctrine->getRepository(User::class)->findOneBy(['username' => $content['username']]);
        $hash = $user->getPassword();

        if(!$hash){
            return new JsonResponse(['message' => 'Invalid username / password'], 400);
        }
        $verify = password_verify($content['password'], $hash);

        //$user = new User();
        if($verify){
            JWTauth::issueJWT($content['username']);
            JWTauth::issueRefresh($doctrine, $content['username']);
            return new JsonResponse([
                'message' => 'Success',
            ], 200);
        }else{
            return new JsonResponse([
                'message' => 'Invalid username / password'
            ], 400);
        }
    }

    #[Route('/reset', name: 'reset', methods:['PUT'])]
    public function reset(ManagerRegistry $doctrine, Request $req): JsonResponse{
        if($req->cookies->get('BEARER')){
            return new JsonResponse(['message' => 'You are already logged in'], 400);
        }
        $content = $req->toArray();
        $user = $doctrine->getRepository(User::class)->findOneBy(['email' => $content['email']]);
        $id = $user->getId();
        if(!$id){
            return new JsonResponse(['message' => 'No user exists with such email'], 400);
        }
        $user->setEmail($content['newemail']);
        flush();
        return new JsonResponse([
            'message' => 'Success',
        ], 200);
    }

    #[Route('/logout', name: 'logout', methods:['DELETE'])]
    public function logout(ManagerRegistry $doctrine, Request $req): JsonResponse{
        if(!$req->cookies->get('BEARER')){
            return new JsonResponse(['message' => 'You\'re not logged in'], 403);
        }
        $return = '';
        $test = JWTauth::verify($req->cookies->get('BEARER'));
        foreach($test as $thing){
            $return .= $thing;
        }
        JWTauth::delete($doctrine, $req->cookies->get('REFRESH'));
        return new JsonResponse(['message' => $return], 200);
    }
}