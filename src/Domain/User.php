<?php

namespace App\Domain;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

#[Entity, Table(name: 'users')]
final class User
{
    #[Id, Column(type: 'integer'), GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[Column(type: 'string', length: 50, nullable: false)]
    private string $prenom;

    #[Column(type: 'string', length: 50, nullable: false)]
    private string $nom;

    #[Column(type: 'date', nullable: false)]
    private \DateTime $dateNaissance;

    #[Column(type: 'string', unique: true, nullable: false)]
    private string $email;

    #[Column(type: 'string', nullable: false)]
    private string $motDePasse;

    #[Column(name: 'date_creation', type: 'datetimetz_immutable', nullable: false)]
    private \DateTimeImmutable $dateCreation;

    #[Column(type: 'string', length: 20, nullable: false)]
    private string $role;

    public function __construct(string $prenom, string $nom, \DateTime $dateNaissance, string $email, string $motDePasse, string $role)
    {
        $this->prenom = $prenom;
        $this->nom = $nom;
        $this->dateNaissance = $dateNaissance;
        $this->email = $email;
        $this->motDePasse = password_hash($motDePasse, PASSWORD_DEFAULT);
        $this->dateCreation = new \DateTimeImmutable('now');
        $this->role = $role;
    }

    public function getId(): int 
    { 
        return $this->id; 
    }

    public function getPrenom(): string 
    { 
        return $this->prenom; 
    }
    public function setPrenom(string $prenom): void 
    { 
        $this->prenom = $prenom; 
    }

    public function getNom(): string 
    { 
        return $this->nom; 
    }
    public function setNom(string $nom): void 
    { 
        $this->nom = $nom; 
    }

    public function getDateNaissance(): \DateTime 
    { 
        return $this->dateNaissance; 
    }
    public function setDateNaissance(\DateTime $dateNaissance): void 
    { 
        $this->dateNaissance = $dateNaissance; 
    }

    public function getEmail(): string 
    { 
        return $this->email; 
    }
    public function setEmail(string $email): void 
    { 
        $this->email = $email; 
    }

    public function getMotDePasse(): string 
    { 
        return $this->motDePasse; 
    }
    public function setMotDePasse(string $motDePasse): void 
    { 
        $this->motDePasse = password_hash($motDePasse, PASSWORD_DEFAULT); 
    }

    public function getDateCreation(): \DateTimeImmutable 
    { 
        return $this->dateCreation; 
    }

    public function getRole(): string 
    { 
        return $this->role; 
    }

    public function setRole(string $role): void 
    { 
        $this->role = $role; 
    }
}
