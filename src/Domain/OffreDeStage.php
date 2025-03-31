<?php

namespace App\Domain;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "offre_de_stage")]
class OffreDeStage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 100)]
    private string $titre;

    #[ORM\Column(type: "text")]
    private string $description;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $dateDebut;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $dateFin;

    #[ORM\ManyToOne(targetEntity: Entreprise::class)]
    #[ORM\JoinColumn(name: "ID_Entreprise", referencedColumnName: "id", nullable: false)]
    private Entreprise $entreprise;

    
    #[ORM\ManyToMany(targetEntity: Competence::class, inversedBy: "offres")]
    #[ORM\JoinTable(name: "offre_competence")]
    private Collection $competences;
    
    public function __construct()
    {
        $this->competences = new ArrayCollection();
    }
    
    public function getCompetences(): Collection
    {
        return $this->competences;
    }
    
    public function addCompetence(Competence $competence): self
    {
        if (!$this->competences->contains($competence)) {
            $this->competences->add($competence);
            $competence->addOffre($this);
        }
        return $this;
    }
    
    public function removeCompetence(Competence $competence): self
    {
        if ($this->competences->contains($competence)) {
            $this->competences->removeElement($competence);
            $competence->removeOffre($this);
        }
        return $this;
    }
    

}


