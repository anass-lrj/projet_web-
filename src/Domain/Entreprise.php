<?php

namespace App\Domain;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

#[Entity, Table(name: 'entreprise')]
final class Entreprise
{
    #[Id, Column(type: 'integer'), GeneratedValue(strategy: 'AUTO')]
    private int $id;

    // Obligatoire
    #[Column(type: 'string', length: 100, nullable: false)]
    private string $titre;

    #[Column(type: 'string', length: 100, unique: true, nullable: false)]
    private string $email;

    // Optionnels
    #[Column(type: 'string', length: 100, nullable: true)]
    private ?string $ville;

    #[Column(type: 'text', nullable: true)]
    private ?string $description;

    #[Column(type: 'string', length: 20, nullable: true)]
    private ?string $contactTelephone;

    #[Column(type: 'integer', nullable: true)]
    private ?int $nombreStagiaires;

    #[Column(type: 'float', nullable: true)]
    private ?float $evaluationMoyenne;

    public function __construct(string $titre, string $email)
    {
        $this->titre = $titre;
        $this->email = $email;
        $this->ville = null;
        $this->description = null;
        $this->contactTelephone = null;
        $this->nombreStagiaires = null;
        $this->evaluationMoyenne = null;
    }

    // Getters et Setters

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
