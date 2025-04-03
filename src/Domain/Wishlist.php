<?php

namespace App\Domain;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'wishlist')]
class Wishlist
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'wishlist')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\ManyToOne(targetEntity: OffreDeStage::class, inversedBy: 'wishlist')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private OffreDeStage $offre;

    public function __construct(User $user, OffreDeStage $offre)
    {
        $this->user = $user;
        $this->offre = $offre;
    }

    public function getId(): int { return $this->id; }
    public function getUser(): User { return $this->user; }
    public function getOffre(): OffreDeStage { return $this->offre; }
}
