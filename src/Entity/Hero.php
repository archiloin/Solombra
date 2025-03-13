<?php

namespace App\Entity;

use App\Entity\Admin\ListHero;
use App\Repository\HeroRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HeroRepository::class)]
class Hero
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $health = null;

    #[ORM\Column(length: 255)]
    private ?string $mood = null;

    #[ORM\Column]
    private ?int $endurance = null;

    #[ORM\Column]
    private ?int $damage = null;

    #[ORM\Column]
    private ?int $xp = null;

    #[ORM\Column]
    private ?int $level = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'heroes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ListHero $info = null;

    #[ORM\OneToMany(mappedBy: 'hero', targetEntity: Empire::class)]
    private Collection $empire;

    public function __construct()
    {
        $this->empire = new ArrayCollection();
        $this->health = 30;
        $this->mood = 'Heureux';
        $this->endurance = 10;
        $this->damage = 30;
        $this->xp = 0;
        $this->level = 1;

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHealth(): ?int
    {
        return $this->health;
    }

    public function setHealth(int $health): static
    {
        $this->health = $health;

        return $this;
    }

    public function getMood(): ?string
    {
        return $this->mood;
    }

    public function setMood(string $mood): static
    {
        $this->mood = $mood;

        return $this;
    }

    public function getEndurance(): ?int
    {
        return $this->endurance;
    }

    public function setEndurance(int $endurance): static
    {
        $this->endurance = $endurance;

        return $this;
    }

    public function getDamage(): ?int
    {
        return $this->damage;
    }

    public function setDamage(int $damage): static
    {
        $this->damage = $damage;

        return $this;
    }

    public function getXp(): ?int
    {
        return $this->xp;
    }

    public function setXp(int $xp): static
    {
        $this->xp = $xp;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getInfo(): ?ListHero
    {
        return $this->info;
    }

    public function setInfo(?ListHero $info): static
    {
        $this->info = $info;

        return $this;
    }

    /**
     * @return Collection<int, Empire>
     */
    public function getEmpire(): Collection
    {
        return $this->empire;
    }

    public function addEmpire(Empire $empire): static
    {
        if (!$this->empire->contains($empire)) {
            $this->empire->add($empire);
            $empire->setHero($this);
        }

        return $this;
    }

    public function removeEmpire(Empire $empire): static
    {
        if ($this->empire->removeElement($empire)) {
            // set the owning side to null (unless already changed)
            if ($empire->getHero() === $this) {
                $empire->setHero(null);
            }
        }

        return $this;
    }

    public function updateLevel(): void
    {
        $baseXp = 10; // XP de base pour le premier niveau
        $growthFactor = 1.3; // Facteur de croissance exponentielle
        $newLevel = $this->level; // Commencez à calculer à partir du niveau actuel
        $xpForNextLevel = ceil($baseXp * pow($growthFactor, $newLevel)); // XP requise pour le niveau suivant, arrondie
    
        // Tant que l'XP actuelle dépasse l'XP requise pour le niveau suivant
        while ($this->xp >= $xpForNextLevel) {
            $this->xp -= $xpForNextLevel; // Soustrayez l'XP requise pour le niveau actuel
            $newLevel++; // Incrémentez le niveau
            $xpForNextLevel = ceil($baseXp * pow($growthFactor, $newLevel)); // Définissez l'XP requise pour le prochain niveau
        }
    
        // Mise à jour du niveau si différent
        if ($newLevel != $this->level) {
            $oldLevel = $this->level; // Stockez l'ancien niveau avant de le mettre à jour
            $this->level = $newLevel; // Mettez à jour le niveau avec la nouvelle valeur
            $this->onLevelUp($oldLevel, $newLevel); // Appelez onLevelUp avec les deux niveaux
        }
    }
    
    
    public function getXpRequiredForNextLevel(): int
    {
        $baseXp = 10; // XP de base pour le premier niveau
        $growthFactor = 1.3; // Facteur de croissance exponentielle
        $nextLevel = $this->level + 1; // Le niveau suivant
        
        // XP requis uniquement pour le prochain niveau
        $xpRequired = ceil($baseXp * pow($growthFactor, $nextLevel - 1));
        
        return $xpRequired;
    }    
    
    protected function onLevelUp(int $oldLevel, int $newLevel): void
    {
        // Exemple : Augmenter les dommages et la santé à chaque passage de niveau
        $damageIncrease = ($newLevel - $oldLevel) * 10; // Augmente les dommages de 10 à chaque niveau gagné
        $enduranceIncrease = ($newLevel - $oldLevel) * 10; // Augmente l'endurance de 10 à chaque niveau gagné
        $healthIncrease = ($newLevel - $oldLevel) * 20; // Augmente la santé de 20 à chaque niveau gagné
        $mood = 'Se sent Confiant';
    
        // Augmenter les dommages et la santé directement
        $this->damage += $damageIncrease;
        $this->endurance += $enduranceIncrease;
        $this->health += $healthIncrease;
        $this->mood = $mood;

        // Déclencher une animation de niveau côté client, si nécessaire
        // ...
    
        // Déblocage de fonctionnalités
        $this->unlockFeatures($newLevel);
    }    

    protected function unlockFeatures(int $level): void
    {
        // Exemple de déblocage de fonctionnalités
        if ($level == 5) {
            // Débloquer une fonctionnalité spécifique
        }
    
        // Plus de logique de déblocage ici
    }    

    public function addXp(int $xpToAdd): static
    {
        $this->xp += $xpToAdd;
        $this->updateLevel(); // Mise à jour du niveau basé sur la nouvelle XP
    
        return $this;
    }

}
