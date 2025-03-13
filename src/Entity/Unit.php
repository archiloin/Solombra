<?php

namespace App\Entity;

use App\Repository\UnitRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UnitRepository::class)]
class Unit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $army = null;

    #[ORM\OneToOne(mappedBy: 'unit', cascade: ['persist', 'remove'])]
    private ?Empire $empire = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArmy(): ?array
    {
        return $this->army;
    }

    public function setArmy(?array $army): static
    {
        $this->army = $army;
        return $this;
    }

    public function getEmpire(): ?Empire
    {
        return $this->empire;
    }

    public function setEmpire(?Empire $empire): static
    {
        // unset the owning side of the relation if necessary
        if ($empire === null && $this->empire !== null) {
            $this->empire->setUnit(null);
        }

        // set the owning side of the relation if necessary
        if ($empire !== null && $empire->getUnit() !== $this) {
            $empire->setUnit($this);
        }

        $this->empire = $empire;

        return $this;
    }

    public function isEmpty(): bool
    {
        return empty($this->army) || array_sum($this->army) === 0;
    }
}
