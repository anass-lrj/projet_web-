<?php

namespace App\Domain;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity, ORM\Table(name: 'domaine')]
class Domaine
{
    #[ORM\Id, ORM\Column(type: 'integer'), ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    private string $nom;

    #[ORM\OneToMany(mappedBy: 'domaine', targetEntity: Entreprise::class)]
    private Collection $entreprises;

    public function __construct(string $nom = '')
    {
        $this->nom = $nom;
        $this->entreprises = new ArrayCollection();
    }

    public function getId(): ?int
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

    public function getEntreprises(): Collection
    {
        return $this->entreprises;
    }
}
