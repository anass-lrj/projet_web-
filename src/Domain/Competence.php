<?php

namespace App\Domain;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'competence')]
class Competence
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(type: 'string', length: 100, nullable: false)]
    private string $nom;

    #[ORM\ManyToMany(targetEntity: OffreDeStage::class, mappedBy: 'competences')]
    private Collection $offres;

    public function __construct(string $nom)
    {
        $this->nom = $nom;
        $this->offres = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getOffres(): Collection
    {
        return $this->offres;
    }
}