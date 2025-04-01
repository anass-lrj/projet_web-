<?php

namespace App\Domain;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "etudiant")]
class Etudiant
{
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: User::class, inversedBy: "etudiant")]
    #[ORM\JoinColumn(name: "ID_Utilisateur", referencedColumnName: "id", nullable: false)]
    private User $utilisateur;

    #[ORM\ManyToOne(targetEntity: Promotion::class, inversedBy: "etudiants")]
    #[ORM\JoinColumn(name: "ID_Promotion", referencedColumnName: "id", nullable: false)]
    private Promotion $promotion;

    public function __construct(User $utilisateur, Promotion $promotion)
    {
        $this->utilisateur = $utilisateur;
        $this->promotion = $promotion;
    }

    public function getUtilisateur(): User { return $this->utilisateur; }
    public function getPromotion(): Promotion { return $this->promotion; }
    public function setPromotion(Promotion $promotion): void { $this->promotion = $promotion; }
}
