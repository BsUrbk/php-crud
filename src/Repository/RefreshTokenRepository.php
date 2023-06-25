<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\RefreshToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RefreshTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RefreshToken::class);
    }

    public function deleteRefreshToken(string $token): void
    {
        $this->createQueryBuilder('q')
            ->delete(RefreshToken::class, 'rt')
            ->andWhere('rt.token like :token')
            ->setParameters([
                'token' => $token
            ])
            ->getQuery()
            ->execute()
        ;
    }
}
