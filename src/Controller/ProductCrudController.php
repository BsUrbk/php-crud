<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Product;
use App\Controller\JWTauth;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;

class ProductCrudController extends AbstractController{
    #[Route('/get-all-products', name: 'get-all-products', methods: ['POST'])]
    public function getAllProducts(Request $req, ManagerRegistry $doctrine): Response{
        if(is_null($req->cookies->get('BEARER'))){
            return $this->json(['message' => 'You are not logged in']);
        }
        $verify = JWTauth::verify($req->cookies->get('BEARER'));

        if($verify){
            return $this->json(['message' => 'Invalid jwt']);
        }

        $result = $doctrine->getRepository(Product::class)->findAll();

        return $this->json(['products' => $result]);
    }

    #[Route('/add-procuct', name: 'add-product', methods: ['POST'])]
    public function addProduct(Request $req, ManagerRegistry $doctrine): Response{
        if(is_null($req->cookies->get('BEARER'))){
            return $this->json(['message' => 'You are not logged in']);
        }
        $verify = JWTauth::verify($req->cookies->get('BEARER'));

        if($verify){
            return $this->json(['message' => 'Invalid jwt']);
        }
        
        $content = $req->toArray();
        $product = new Product($content['name'], $content['quantity'], $content['location']);
        $entityManager = $doctrine->getManager();
        $entityManager->persist($product);
        $entityManager->flush();

        return $this->json(['message' => 'Product has been added to the database']);
    }

    #[Route('/update-product', name: 'update-product', methods: ['PUT'])]
    public function updateProduct(Request $req, ManagerRegistry $doctrine): Response{
        if(is_null($req->cookies->get('BEARER'))){
            return $this->json(['message' => 'You are not logged in']);
        }
        $verify = JWTauth::verify($req->cookies->get('BEARER'));

        if($verify){
            return $this->json(['message' => 'Invalid jwt']);
        }

        $content = $req->toArray();
        $product = $doctrine->getRepository(Product::class)->findOneBy(['id' => $content['id']]);

        if(!$product){
            return $this->json(['message' => 'Product not found'], 404);
        }
        $entityManager = $doctrine->getManager();
        switch($content['field']){
            case 'name':
                $product->setName($content['name']);
                break;
            case 'quantity':
                $product->setQuantity($content['quantity']);
                break;
            case 'location':
                $product->setLocation($content['location']);
                break;
            default:
                return $this->json(['message' => 'Invalid field name']);
                break;
        }
        $entityManager->flush();
        return $this->json(['message' => 'Product '.$content['field'].' has been updated']);
    }

    #[Route('/delete-product', name: 'delete-product', methods: ['DELETE'])]
    public function deleteProduct(Request $req, ManagerRegistry $doctrine): Response{
        if(is_null($req->cookies->get('BEARER'))){
            return $this->json(['message' => 'You are not logged in']);
        }
        $verify = JWTauth::verify($req->cookies->get('BEARER'));

        if($verify){
            return $this->json(['message' => 'Invalid jwt']);
        }

        $content = $req->toArray();

        $product = $doctrine->getRepository(Product::class)->findOneBy(['id' => $content['id']]);
        
        if(!$product){
            return $this->json(['message' => 'Product not found'], 404);
        }

        $query = '
        DELETE FROM "product"
        WHERE id LIKE :id
        ';

        $conn = $doctrine->getConnection();
        $stmt = $conn->prepare($query);
        $stmt->executeQuery(['id' => $content['id']]);

        return $this->json(['message' => 'Product has been deleted']);
    }
}