<?php

namespace App\Domain;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "wishlist")]
class Wishlist
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Etudiant::class)]
    #[ORM\JoinColumn(name: "ID_Etudiant", referencedColumnName: "ID_Utilisateur", nullable: false)]
    private Etudiant $etudiant;

    #[ORM\ManyToOne(targetEntity: OffreDeStage::class)]
    #[ORM\JoinColumn(name: "ID_Offre", referencedColumnName: "id", nullable: false)]
    private OffreDeStage $offre;
}
