<?php

namespace App\Domain;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "pilote")]
class Pilote
{
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: "ID_Utilisateur", referencedColumnName: "idUtilisateur", onDelete: "CASCADE")]
    private Utilisateur $utilisateur;
}
