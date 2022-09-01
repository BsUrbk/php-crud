<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity('username')]
class User
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private $id;

    #[ORM\Column(length: 30)]
    #[Assert\Unique]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lastName = null;

    #[ORM\OneToOne(mappedBy: 'usertoken', cascade: ['persist', 'remove'])]
    private ?RefreshToken $refreshToken = null;

    public function __construct(string $username, string $email, string $password, ?string $firstName, ?string $lastName){
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->firstName = $firstName;
        if(!is_null($firstName)){
            $this->firstName = $firstName;
        }
        if(!is_null($firstName)){
            $this->lastName = $lastName;
        }
       return $this; 
    }  

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }


    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getRefreshToken(): ?RefreshToken
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(?RefreshToken $refreshToken): self
    {
        // unset the owning side of the relation if necessary
        if ($refreshToken === null && $this->refreshToken !== null) {
            $this->refreshToken->setUsertoken(null);
        }

        // set the owning side of the relation if necessary
        if ($refreshToken !== null && $refreshToken->getUsertoken() !== $this) {
            $refreshToken->setUsertoken($this);
        }

        $this->refreshToken = $refreshToken;

        return $this;
    }
}
