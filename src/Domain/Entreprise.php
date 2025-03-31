<?php

namespace App\Domain;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity, ORM\Table(name: 'entreprise')]
class Entreprise
{
    #[ORM\Id, ORM\Column(type: 'integer'), ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100, nullable: false)]
    private string $titre;

    #[ORM\Column(type: 'string', length: 100, unique: true, nullable: false)]
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

    // ✅ Ajout de valeurs par défaut pour éviter l'erreur de constructeur
    public function __construct(string $titre = '', string $email = '')
    {
        $this->titre = $titre;
        $this->email = $email;
    }

    // ✅ Ajout d'un getter pour l'ID, car Doctrine le gère automatiquement
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
}
