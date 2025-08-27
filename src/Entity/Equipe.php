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

    // Relation avec les utilisateurs (une équipe a plusieurs membres)
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'equipe')]
    private Collection $membres;

    // Chef d'équipe (un user qui dirige l'équipe)
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'id_user_chef_equipe', nullable: true, onDelete: 'SET NULL')]
    private ?User $chef_equipe = null;

    // Relation avec les affectations
    #[ORM\OneToMany(targetEntity: Affectation::class, mappedBy: 'equipe')]
    private Collection $affectations;

    public function __construct()
    {
        $this->membres = new ArrayCollection();
        $this->affectations = new ArrayCollection();
    }

    public function getId(): ?int 
    { 
        return $this->id; 
    }

    public function getNomEquipe(): ?string 
    { 
        return $this->nom_equipe; 
    }

    public function setNomEquipe(string $nom_equipe): static 
    { 
        $this->nom_equipe = $nom_equipe; 
        return $this; 
    }

    /**
     * @return Collection<int, User>
     */
    public function getMembres(): Collection
    {
        return $this->membres;
    }

    public function addMembre(User $membre): static
    {
        if (!$this->membres->contains($membre)) {
            $this->membres->add($membre);
            $membre->setEquipe($this);
        }
        return $this;
    }

    public function removeMembre(User $membre): static
    {
        if ($this->membres->removeElement($membre)) {
            if ($membre->getEquipe() === $this) {
                $membre->setEquipe(null);
            }
        }
        return $this;
    }

    public function getChefEquipe(): ?User 
    { 
        return $this->chef_equipe; 
    }

    public function setChefEquipe(?User $chef_equipe): static 
    { 
        $this->chef_equipe = $chef_equipe; 
        return $this; 
    }

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

    /**
     * Calcule automatiquement le nombre de membres dans l'équipe
     */
    public function getNombre(): int
    {
        return $this->membres->count();
    }

    /**
     * Récupère toutes les compétences de l'équipe (basées sur les compétences des membres)
     */
    public function getCompetenceEquipe(): array
    {
        $competences = [];
        
        foreach ($this->membres as $membre) {
            foreach ($membre->getCompetences() as $competence) {
                if (!in_array($competence, $competences)) {
                    $competences[] = $competence;
                }
            }
        }
        
        return $competences;
    }
}