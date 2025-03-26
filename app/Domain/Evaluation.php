<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "evaluation")]
class Evaluation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Entreprise::class)]
    #[ORM\JoinColumn(name: "ID_Entreprise", referencedColumnName: "id", nullable: false)]
    private Entreprise $entreprise;

    #[ORM\ManyToOne(targetEntity: Etudiant::class)]
    #[ORM\JoinColumn(name: "ID_Etudiant", referencedColumnName: "ID_Utilisateur", nullable: false)]
    private Etudiant $etudiant;
}
