<?php

namespace App\Entity;

use App\Repository\RefreshTokenRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: RefreshTokenRepository::class)]
class RefreshToken
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private $id;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $token = null;

    #[ORM\OneToOne(inversedBy: 'refreshToken', cascade: ['persist', 'remove'])]
    private ?User $usertoken = null;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getUsertoken(): ?User
    {
        return $this->usertoken;
    }

    public function setUsertoken(?User $usertoken): self
    {
        $this->usertoken = $usertoken;

        return $this;
    }
}
