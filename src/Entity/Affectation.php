<?php

namespace App\Entity;

use App\Repository\AffectationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AffectationRepository::class)]
class Affectation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Plus besoin de user car l'équipe contient déjà les users
    // #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "affectations")]
    // #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    // private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Equipe::class, inversedBy: "affectations")]
    #[ORM\JoinColumn(name: 'id_equipe', nullable: false, onDelete: "CASCADE")]
    private ?Equipe $equipe = null;

    #[ORM\ManyToOne(targetEntity: Chantier::class, inversedBy: "affectations")]
    #[ORM\JoinColumn(name: 'id_chantier', nullable: false, onDelete: "CASCADE")]
    private ?Chantier $chantier = null;

    // Plus besoin de dates début/fin car elles sont dans le chantier
    // #[ORM\Column(type: Types::DATE_MUTABLE)]
    // private ?\DateTimeInterface $date_debut = null;

    // #[ORM\Column(type: Types::DATE_MUTABLE)]
    // private ?\DateTimeInterface $date_fin = null;

    // Plus besoin de nom car il est dans le chantier
    // #[ORM\Column(length: 50, nullable: true)]
    // private ?string $nom = null;

    public function getId(): ?int 
    { 
        return $this->id; 
    }

    // Garder la méthode getUser pour la compatibilité, mais elle retourne null maintenant
    // ou on pourrait la supprimer complètement selon vos besoins
    public function getUser(): ?User
    {
        return null; // Plus utilisé
    }

    public function setUser(?User $user): static
    {
        // Plus utilisé mais on garde pour éviter les erreurs
        return $this;
    }

    public function getEquipe(): ?Equipe 
    { 
        return $this->equipe; 
    }

    public function setEquipe(?Equipe $equipe): static 
    { 
        $this->equipe = $equipe; 
        return $this; 
    }

    public function getChantier(): ?Chantier 
    { 
        return $this->chantier; 
    }

    public function setChantier(?Chantier $chantier): static 
    { 
        $this->chantier = $chantier; 
        return $this; 
    }

    // Méthodes pour récupérer les dates du chantier
    public function getDateDebut(): ?\DateTimeInterface 
    { 
        return $this->chantier?->getDateDebut();
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->chantier?->getDateFin();
    }

    // Méthode pour récupérer le nom du chantier
    public function getNom(): ?string
    {
        return $this->chantier?->getNom();
    }

    // Méthodes de compatibilité (optionnelles)
    public function setDateDebut(\DateTimeInterface $date_debut): static 
    { 
        // Plus utilisé mais on garde pour éviter les erreurs
        return $this; 
    }

    public function setDateFin(\DateTimeInterface $date_fin): static
    {
        // Plus utilisé mais on garde pour éviter les erreurs
        return $this;
    }

    public function setNom(?string $nom): static
    {
        // Plus utilisé mais on garde pour éviter les erreurs
        return $this;
    }
}