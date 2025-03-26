<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: "competence")]
class Competence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 100)]
    private string $nom;

    #[ORM\ManyToMany(targetEntity: OffreDeStage::class, mappedBy: "competences")]
    private Collection $offres;

    public function __construct()
    {
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

    public function addOffre(OffreDeStage $offre): self
    {
        if (!$this->offres->contains($offre)) {
            $this->offres->add($offre);
            $offre->addCompetence($this);
        }
        return $this;
    }

    public function removeOffre(OffreDeStage $offre): self
    {
        if ($this->offres->contains($offre)) {
            $this->offres->removeElement($offre);
            $offre->removeCompetence($this);
        }
        return $this;
    }
}
