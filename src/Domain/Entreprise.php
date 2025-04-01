<?php

namespace App\Domain;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity, ORM\Table(name: 'entreprise')]
class Entreprise
{
    #[ORM\Id, ORM\Column(type: 'integer'), ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    private string $titre;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    private string $email;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $ville = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $contactTelephone = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $nombreStagiaires = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $evaluationMoyenne = null;

    #[ORM\OneToMany(mappedBy: 'entreprise', targetEntity: OffreDeStage::class, cascade: ['persist', 'remove'])]
    private Collection $offresDeStage;

    #[ORM\ManyToOne(targetEntity: Domaine::class)]
    #[ORM\JoinColumn(name: "domaine_id", referencedColumnName: "id", nullable: true)]
    private ?Domaine $domaine = null;
    
    public function __construct(string $titre = '', string $email = '')
    {
        $this->titre = $titre;
        $this->email = $email;
        $this->offresDeStage = new ArrayCollection();
    }
    
    public function getOffresDeStage(): Collection
    {
        return $this->offresDeStage;
    }
    
    public function addOffreDeStage(OffreDeStage $offre): self
    {
        if (!$this->offresDeStage->contains($offre)) {
            $this->offresDeStage[] = $offre;
            $offre->setEntreprise($this);
        }
        return $this;
    }
    
    public function removeOffreDeStage(OffreDeStage $offre): self
    {
        if ($this->offresDeStage->removeElement($offre)) {
            if ($offre->getEntreprise() === $this) {
                $offre->setEntreprise(null);
            }
        }
        return $this;
    }
    
    public function getId(): ?int
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

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(?string $ville): self
    {
        $this->ville = $ville;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getContactTelephone(): ?string
    {
        return $this->contactTelephone;
    }

    public function setContactTelephone(?string $contactTelephone): self
    {
        $this->contactTelephone = $contactTelephone;
        return $this;
    }

    public function getNombreStagiaires(): ?int
    {
        return $this->nombreStagiaires;
    }

    public function setNombreStagiaires(?int $nombreStagiaires): self
    {
        $this->nombreStagiaires = $nombreStagiaires;
        return $this;
    }

    public function getEvaluationMoyenne(): ?float
    {
        return $this->evaluationMoyenne;
    }

    public function setEvaluationMoyenne(?float $evaluationMoyenne): self
    {
        $this->evaluationMoyenne = $evaluationMoyenne;
        return $this;
    }

    public function getDomaine(): ?Domaine
    {
        return $this->domaine;
    }

    public function setDomaine(?Domaine $domaine): self
    {
        $this->domaine = $domaine;
        return $this;
    }
}
