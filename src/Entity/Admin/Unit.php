<?php

namespace App\Entity\Admin;

use App\Repository\Admin\UnitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UnitRepository::class)]
#[ORM\Table(name: "list_unit")]
class Unit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $attack = null;

    #[ORM\Column(nullable: true)]
    private ?int $defence = null;

    #[ORM\Column(nullable: true)]
    private ?int $shield = null;

    #[ORM\Column(nullable: true)]
    private ?int $speed = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $birthCost = [];

    #[ORM\Column]
    private ?int $birthTime = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    #[ORM\Column]
    private ?int $endurance = null;

    #[ORM\Column]
    private ?int $health = null;

    #[ORM\OneToMany(mappedBy: 'unit', targetEntity: Force::class)]
    private Collection $forces;

    #[ORM\Column]
    private ?int $stockage = null;

    public function __construct()
    {
        $this->forces = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAttack(): ?int
    {
        return $this->attack;
    }

    public function setAttack(?int $attack): static
    {
        $this->attack = $attack;

        return $this;
    }

    public function getDefence(): ?int
    {
        return $this->defence;
    }

    public function setDefence(?int $defence): static
    {
        $this->defence = $defence;

        return $this;
    }

    public function getShield(): ?int
    {
        return $this->shield;
    }

    public function setShield(?int $shield): static
    {
        $this->shield = $shield;

        return $this;
    }

    public function getSpeed(): ?int
    {
        return $this->speed;
    }

    public function setSpeed(?int $speed): static
    {
        $this->speed = $speed;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getBirthCost(): array
    {
        return $this->birthCost;
    }

    public function setBirthCost(array $birthCost): static
    {
        $this->birthCost = $birthCost;

        return $this;
    }

    public function getBirthTime(): ?int
    {
        return $this->birthTime;
    }

    public function setBirthTime(int $birthTime): static
    {
        $this->birthTime = $birthTime;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

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

    public function getHealth(): ?int
    {
        return $this->health;
    }

    public function setHealth(int $health): static
    {
        $this->health = $health;

        return $this;
    }

    /**
     * @return Collection<int, Force>
     */
    public function getForces(): Collection
    {
        return $this->forces;
    }

    public function addForce(Force $force): static
    {
        if (!$this->forces->contains($force)) {
            $this->forces->add($force);
            $force->setUnit($this);
        }

        return $this;
    }

    public function removeForce(Force $force): static
    {
        if ($this->forces->removeElement($force)) {
            // set the owning side to null (unless already changed)
            if ($force->getUnit() === $this) {
                $force->setUnit(null);
            }
        }

        return $this;
    }

    public function getStockage(): ?int
    {
        return $this->stockage;
    }

    public function setStockage(int $stockage): static
    {
        $this->stockage = $stockage;

        return $this;
    }
}
