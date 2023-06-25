<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Exception;

class ProductService
{
    public function __construct(
        private ProductRepository $productRepository,
        private EntityManagerInterface $em,
    ) {
    }

    public function addNewProduct(Request $request): void {
        $content = $request->toArray();
        $product = (new Product())
            ->setName($content[Product::NAME])
            ->setQuantity($content[Product::QUANTITY])
            ->setLocation($content[Product::LOCATION])
        ;
        $this->em->persist($product);
        $this->em->flush();
    }

    public function updateProduct(Request $request): void {
        $content = $request->toArray();
        $product = $this->productRepository->findOneBy(['id' => $content['id']]);

        if (null === $product) throw new Exception('Product not found', 404);

        $product
            ->setName($content[Product::NAME] ?? $product->getName())
            ->setQuantity($content[Product::QUANTITY] ?? $product->getQuantity())
            ->setLocation($content[Product::LOCATION] ?? $product->getLocation())
        ;
        $this->em->persist($product);
        $this->em->flush();
    }

    public function deleteProduct(Request $request): void {
        $content = $request->toArray();
        $product = $this->productRepository->findOneBy(['id' => $content['id']]);

        if (null === $product) {
            throw new Exception('Product not found', 404);
        }

        $this->productRepository->deleteById($product->getId());
    }
}