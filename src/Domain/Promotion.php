<?php

namespace App\Domain;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: "promotion")]
class Promotion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 50, nullable: false)]
    private string $nom;

    #[ORM\ManyToOne(targetEntity: Pilote::class, inversedBy: "promotions")]
    #[ORM\JoinColumn(name: "ID_Pilote", referencedColumnName: "ID_Utilisateur", nullable: true)]
    private ?Pilote $pilote;

    #[ORM\OneToMany(targetEntity: Etudiant::class, mappedBy: "promotion")]
    private Collection $etudiants;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: "promotions", cascade: ["persist", "remove"])]
    private Collection $users;

    public function __construct(string $nom, ?Pilote $pilote = null)
    {
        $this->nom = $nom;
        $this->pilote = $pilote;
        $this->etudiants = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    public function getId(): int { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): void { $this->nom = $nom; }
    public function getPilote(): ?Pilote { return $this->pilote; }
    public function setPilote(?Pilote $pilote): void { $this->pilote = $pilote; }
    public function getEtudiants(): Collection { return $this->etudiants; }

       
    public function getUsers(): Collection {
        return $this->users;
    }

    public function addUser(User $user): void
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }
    }

    public function removeUser(User $user): void
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
        }
    }
}