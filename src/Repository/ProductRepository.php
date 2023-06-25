<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;

class ProductRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private SerializerInterface $serializer,
    ) {
        parent::__construct($registry, Product::class);
    }

    public function deleteById(Uuid $id): void {
        $this->createQueryBuilder('q')
            ->delete(Product::class, 'product')
            ->andWhere('product.id = :id')
            ->setParameters([
                'id' => $id,
            ])
            ->getQuery()
            ->execute()
        ;
    }

    public function findByName(?string $name): ?string {
        $products = $this->createQueryBuilder('q')
            ->andWhere('q.name like :name')
            ->setParameters([
                'name' => '%' . $name . '%',
            ])
            ->getQuery()
            ->execute()
        ;

        return $this->serializer->serialize($products, JsonEncoder::FORMAT);
    }
}
