<?php

namespace App\Domain;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

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

    #[ORM\OneToMany(mappedBy: 'etudiant', targetEntity: Candidature::class, cascade: ['persist', 'remove'])]
    private Collection $candidatures;

    public function __construct(Utilisateur $utilisateur, ?Promotion $promotion = null)
    {
        $this->utilisateur = $utilisateur;
        $this->promotion = $promotion;
        $this->candidatures = new ArrayCollection();
    }

    public function getCandidatures(): Collection
    {
        return $this->candidatures;
    }

    public function __toString(): string
    {
        return $this->utilisateur->getNom() . " " . $this->utilisateur->getPrenom();
    }
}
