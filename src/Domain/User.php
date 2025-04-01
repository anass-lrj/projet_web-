<?php

namespace App\Domain;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Domain\Etudiant;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 50, nullable: false)]
    private string $prenom;

    #[ORM\Column(type: 'string', length: 50, nullable: false)]
    private string $nom;

    #[ORM\Column(type: 'date', nullable: false)]
    private \DateTime $dateNaissance;

    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    private string $email;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $motDePasse;

    #[ORM\Column(name: 'date_creation', type: 'datetimetz_immutable', nullable: false)]
    private \DateTimeImmutable $dateCreation;

    #[ORM\Column(type: 'string', length: 20, nullable: false)]
    private string $role;

    #[ORM\OneToOne(mappedBy: "utilisateur", targetEntity: Etudiant::class)]
    private ?Etudiant $etudiant = null;

    #[ORM\OneToOne(mappedBy: "utilisateur", targetEntity: Pilote::class)]
    private ?Pilote $pilote = null;

    #[ORM\ManyToMany(targetEntity: Promotion::class, inversedBy: "users")]
    #[ORM\JoinTable(name: "user_promotion")]
    private Collection $promotions;

    public function __construct(string $prenom, string $nom, \DateTime $dateNaissance, string $email, string $motDePasse, string $role)
    {
        $this->promotions = new ArrayCollection();

        $this->prenom = $prenom;
        $this->nom = $nom;
        $this->dateNaissance = $dateNaissance;
        $this->email = $email;
        $this->motDePasse = password_hash($motDePasse, PASSWORD_DEFAULT);
        $this->dateCreation = new \DateTimeImmutable('now');
        $this->role = $role;
    }

    public function getId(): int { return $this->id; }
    public function getPrenom(): string { return $this->prenom; }
    public function setPrenom(string $prenom): self { $this->prenom = $prenom; return $this; }
    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): void { $this->nom = $nom; }
    public function getDateNaissance(): \DateTime { return $this->dateNaissance; }
    public function setDateNaissance(\DateTime $dateNaissance): void { $this->dateNaissance = $dateNaissance; }
    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function getMotDePasse(): string { return $this->motDePasse; }
    public function setMotDePasse(string $motDePasse): void { $this->motDePasse = password_hash($motDePasse, PASSWORD_DEFAULT); }
    public function getDateCreation(): \DateTimeImmutable { return $this->dateCreation; }
    public function getRole(): string { return $this->role; }
    public function setRole(string $role): void { $this->role = $role; }
   
   
    public function getPromotions(): Collection {
        return $this->promotions;
    }
    
    public function addPromotion(Promotion $promotion): void {
        if (!$this->promotions->contains($promotion)) {
            $this->promotions->add($promotion);
        }
    }
    
    public function removePromotion(Promotion $promotion): void {
        $this->promotions->removeElement($promotion);
    }

}

