<?php

namespace App\Domain;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: "pilote")]
class Pilote
{
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: User::class, inversedBy: "pilote")]
    #[ORM\JoinColumn(name: "ID_Utilisateur", referencedColumnName: "id", nullable: false)]
    private User $utilisateur;

    #[ORM\OneToMany(targetEntity: Promotion::class, mappedBy: "pilote")]
    private Collection $promotions;

    public function __construct(User $utilisateur)
    {
        $this->utilisateur = $utilisateur;
        $this->promotions = new ArrayCollection();
    }

    public function getUtilisateur(): User { return $this->utilisateur; }
    public function getPromotions(): Collection { return $this->promotions; }
}
