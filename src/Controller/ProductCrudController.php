<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Product;
use App\Entity\RefreshToken;
use App\Repository\ProductRepository;
use App\Service\ProductService;
use App\Util\AuthorizationTrait;
use App\Util\JWT\JWTauth;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;

class ProductCrudController extends AbstractController{
    use AuthorizationTrait;

    #[Route('/get-all-products', name: 'get-all-products', methods: ['GET'])]
    public function getAllProducts(
        Request $req,
        ProductRepository $productRepository,
        SerializerInterface $serializer,
    ): Response {
        try {
            $this->denyUnauthorizedRequest($req);
            JWTauth::verify($req->cookies->get(RefreshToken::BEARER));
            $result = $serializer->serialize($productRepository->findAll() ?? [], JsonEncoder::FORMAT);;
        } catch (Throwable $e) {
            return $this->json([
                'message' => $e->getMessage()
            ], $e->getCode());
        }

        return $this->json($result);
    }

    #[Route('/add-product', name: 'add-product', methods: ['POST'])]
    public function addProduct(
        Request $req,
        ProductService $productService,
    ): Response {
        try {
            $this->denyUnauthorizedRequest($req);
            JWTauth::verify($req->cookies->get(RefreshToken::BEARER));
            $productService->addNewProduct($req);
        } catch (Throwable $e) {
            return $this->json([
                'message' => $e->getMessage(),
            ], $e->getCode());
        }

        return $this->json(['message' => 'Product has been added to the database']);
    }

    #[Route('/update-product', name: 'update-product', methods: ['PUT'])]
    public function updateProduct(
        Request $req,
        ProductService $productService,
    ): Response {
        try {
            $this->denyUnauthorizedRequest($req);
            JWTauth::verify($req->cookies->get(RefreshToken::BEARER));
            $productService->updateProduct($req);
        } catch (Throwable $e) {
            return $this->json([
                'message' => $e->getMessage(),
            ], $e->getCode());
        }

        return $this->json(['message' => 'Product has been updated']);
    }

    #[Route('/delete-product', name: 'delete-product', methods: ['DELETE'])]
    public function deleteProduct(
        Request $req,
        ProductService $productService,
    ): Response {
        try {
            $this->denyUnauthorizedRequest($req);
            JWTauth::verify($req->cookies->get(RefreshToken::BEARER));
            $productService->deleteProduct($req);
        } catch (Throwable $e) {
            return $this->json([
                'message' => $e->getMessage(),
            ], $e->getCode());
        }

        return $this->json(['message' => 'Product has been deleted']);
    }

    #[Route("/get-product-by-name", name: 'get-product-by-name', methods: ['POST'])]
    public function getProductsByName(
        Request $req,
        ProductRepository $productRepository,
    ): Response {
        try {
            $this->denyUnauthorizedRequest($req);
            JWTauth::verify($req->cookies->get(RefreshToken::BEARER));
            $products = $productRepository->findByName($req->toArray()[Product::NAME] ?? null);
        } catch (Throwable $e) {
            return $this->json([
                'message' => $e->getMessage(),
            ], $e->getCode());
        }

        return $this->json($products);
    }
}