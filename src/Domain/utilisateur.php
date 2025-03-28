<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "utilisateur")]
class Utilisateur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $idUtilisateur;

    #[ORM\Column(type: "string", length: 180, unique: true)]
    private string $email;

    #[ORM\Column(type: "string")]
    private string $password;

    #[ORM\Column(type: "string", length: 100)]
    private string $prenom;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $dateDeNaissance;

    #[ORM\Column(type: "string", length: 20)]
    private string $role;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $creationCompte;
}

    public function __construct(string $email)
    {
        $this->email = $email;
        
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
    public function setEmail($email)
    {
        $this->email = $email;
    }

   
}