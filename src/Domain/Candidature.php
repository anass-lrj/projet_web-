<?php

namespace App\Domain;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "candidature")]
class Candidature
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Etudiant::class)]
    #[ORM\JoinColumn(name: "ID_Etudiant", referencedColumnName: "ID_Utilisateur", nullable: false)]
    private Etudiant $etudiant;

    #[ORM\ManyToOne(targetEntity: OffreDeStage::class)]
    #[ORM\JoinColumn(name: "ID_Offre", referencedColumnName: "id", nullable: false)]
    private OffreDeStage $offre;

    public function __construct(Etudiant $etudiant, OffreDeStage $offre)
{
    $this->etudiant = $etudiant;
    $this->offre = $offre;
}

public function getId(): ?int
{
    return $this->id;
}

public function getEtudiant(): Etudiant
{
    return $this->etudiant;
}

public function setEtudiant(Etudiant $etudiant): self
{
    $this->etudiant = $etudiant;
    return $this;
}

public function getOffre(): OffreDeStage
{
    return $this->offre;
}

public function setOffre(OffreDeStage $offre): self
{
    $this->offre = $offre;
    return $this;
}

}
