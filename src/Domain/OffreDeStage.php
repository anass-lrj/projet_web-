<?php

namespace App\Domain;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity, ORM\Table(name: 'offre_de_stage')]
class OffreDeStage
{
    #[ORM\Id, ORM\Column(type: 'integer'), ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(type: 'string', length: 100, nullable: false)]
    private string $titre;

    #[ORM\Column(type: 'text', nullable: false)]
    private string $description;

    #[ORM\Column(type: 'date', nullable: false)]
    private \DateTime $dateDebut;

    #[ORM\Column(type: 'date', nullable: false)]
    private \DateTime $dateFin;

    #[ORM\Column(type: 'float', nullable: false)]
    private float $remuneration;

    #[ORM\Column(name: 'date_creation', type: 'datetimetz_immutable', nullable: false)]
    private \DateTimeImmutable $dateCreation;

    #[ORM\ManyToOne(targetEntity: Entreprise::class, inversedBy: 'offresDeStage', cascade: ["persist"])]
    #[ORM\JoinColumn(name: 'entreprise_id', referencedColumnName: 'id', nullable: false)]
    private Entreprise $entreprise;

    #[ORM\OneToMany(mappedBy: 'offre', targetEntity: Wishlist::class, cascade: ['persist', 'remove'])]
    private Collection $wishlists;

    #[ORM\ManyToMany(targetEntity: Competence::class, inversedBy: 'offres', cascade: ['persist'])]
#[ORM\JoinTable(name: 'offre_competence')]
private Collection $competences;

    public function __construct(
        string $titre,
        string $description,
        \DateTime $dateDebut,
        \DateTime $dateFin,
        float $remuneration,
        Entreprise $entreprise
        
    ) {
        $this->titre = $titre;
        $this->description = $description;
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
        $this->remuneration = $remuneration;
        $this->entreprise = $entreprise;
        $this->dateCreation = new \DateTimeImmutable();
        $this->wishlists = new ArrayCollection();
        $this->competences = new ArrayCollection();


    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDateDebut(): \DateTime
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTime $dateDebut): self
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): \DateTime
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTime $dateFin): self
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    public function getRemuneration(): float
    {
        return $this->remuneration;
    }

    public function setRemuneration(float $remuneration): self
    {
        $this->remuneration = $remuneration;
        return $this;
    }

    public function getDateCreation(): \DateTimeImmutable
    {
        return $this->dateCreation;
    }

    public function getEntreprise(): Entreprise
    {
        return $this->entreprise;
    }

    public function setEntreprise(Entreprise $entreprise): self
    {
        $this->entreprise = $entreprise;
        return $this;
    }
    public function getWishlist(): Collection { return $this->wishlist; }

    public function getCompetences(): Collection
    {
        return $this->competences;
    }
    
    public function addCompetence(Competence $competence): self
    {
        if (!$this->competences->contains($competence)) {
            $this->competences->add($competence);
            $competence->getOffres()->add($this);
        }
        return $this;
    }
    
    public function removeCompetence(Competence $competence): self
    {
        if ($this->competences->contains($competence)) {
            $this->competences->removeElement($competence);
            $competence->getOffres()->removeElement($this);
        }
        return $this;
    }
}

