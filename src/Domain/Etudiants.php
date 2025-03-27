<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "etudiant")]
class Etudiant
{
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: "ID_Utilisateur", referencedColumnName: "idUtilisateur", onDelete: "CASCADE")]
    private Utilisateur $utilisateur;

    #[ORM\ManyToOne(targetEntity: Promotion::class)]
    #[ORM\JoinColumn(name: "ID_Promotion", referencedColumnName: "id", nullable: true)]
    private ?Promotion $promotion;
}
