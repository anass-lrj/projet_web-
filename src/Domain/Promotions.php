<?php

namespace App\Domain;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: "promotion")]
class Promotion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 100)]
    private string $nom;

    #[ORM\ManyToOne(targetEntity: Pilote::class)]
    #[ORM\JoinColumn(name: "ID_Pilote", referencedColumnName: "ID_Utilisateur", nullable: true)]
    private ?Pilote $pilote;
}
