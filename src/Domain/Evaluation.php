<?php

namespace App\Domain;
use Doctrine\ORM\Mapping as ORM;
use App\Domain\Entreprise;
use App\Domain\User;

#[ORM\Entity]
#[ORM\Table(name: "evaluation")]
class Evaluation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "float", nullable: false)]
    private float $note;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $commentaire = null;

    #[ORM\Column(type: "datetime", nullable: false)]
    private \DateTime $createdAt;

    #[ORM\ManyToOne(targetEntity: Entreprise::class, inversedBy: 'evaluations')]
    #[ORM\JoinColumn(name: 'ID_Entreprise', referencedColumnName: 'id', nullable: false)]
    private Entreprise $entreprise;

    public function getEntreprise(): Entreprise
    {
        return $this->entreprise;
    }

    public function setEntreprise(Entreprise $entreprise): void
    {
        $this->entreprise = $entreprise;
    }


    public function getId(): int
    {
        return $this->id;
    }

    public function getNote(): float
    {
        return $this->note;
    }

    public function setNote(float $note): void
    {
        $this->note = $note;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): void
    {
        $this->commentaire = $commentaire;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}