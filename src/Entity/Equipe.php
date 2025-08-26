<?php

namespace App\Entity;

use App\Repository\EquipeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EquipeRepository::class)]
class Equipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom_equipe = null;

    // Utiliser un tableau pour les compétences
    #[ORM\Column(type: "json")]
    private array $competance_equipe = []; // Cela stockera un tableau JSON dans la base de données

    #[ORM\Column]
    private ?int $nombre = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $chef_equipe = null;

    #[ORM\OneToMany(targetEntity: Affectation::class, mappedBy: 'equipe')]
    private Collection $affectations;

    public function __construct()
    {
        $this->affectations = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getNomEquipe(): ?string { return $this->nom_equipe; }
    public function setNomEquipe(string $nom_equipe): static { $this->nom_equipe = $nom_equipe; return $this; }

    // Getter et Setter pour competance_equipe
    public function getCompetanceEquipe(): array { return $this->competance_equipe; }

    public function setCompetanceEquipe(array $competance_equipe): static { 
        $this->competance_equipe = $competance_equipe; 
        return $this; 
    }

    public function getNombre(): ?int { return $this->nombre; }
    public function setNombre(int $nombre): static { $this->nombre = $nombre; return $this; }

    public function getChefEquipe(): ?User { return $this->chef_equipe; }
    public function setChefEquipe(?User $chef_equipe): static { $this->chef_equipe = $chef_equipe; return $this; }

    /**
     * @return Collection<int, Affectation>
     */
    public function getAffectations(): Collection 
    { 
        return $this->affectations; 
    }

    public function addAffectation(Affectation $affectation): static 
    {
        if (!$this->affectations->contains($affectation)) {
            $this->affectations->add($affectation);
            $affectation->setEquipe($this);
        }
        return $this;
    }

    public function removeAffectation(Affectation $affectation): static 
    {
        if ($this->affectations->removeElement($affectation) && $affectation->getEquipe() === $this) {
            $affectation->setEquipe(null);
        }
        return $this;
    }
}