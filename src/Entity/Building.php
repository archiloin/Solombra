<?php

namespace App\Entity;

use App\Repository\BuildingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BuildingRepository::class)]
class Building
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column]
    private array $upgradeCost = [];

    #[ORM\Column(length: 255)]
    private ?string $resourceType = null;

    #[ORM\Column(nullable: true)]
    private ?int $resourcePerLevel = null;

    #[ORM\Column]
    private ?int $upgradeTime = null;

    #[ORM\Column]
    private ?float $timeMultiplier = null;

    #[ORM\Column(nullable: true)]
    private ?int $productionRate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $lastUpdate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $coords = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUpgradeCost(): array
    {
        return $this->upgradeCost;
    }

    public function setUpgradeCost(array $upgradeCost): static
    {
        $this->upgradeCost = $upgradeCost;

        return $this;
    }

    public function getResourceType(): ?string
    {
        return $this->resourceType;
    }

    public function setResourceType(string $resourceType): static
    {
        $this->resourceType = $resourceType;

        return $this;
    }

    public function getResourcePerLevel(): ?int
    {
        return $this->resourcePerLevel;
    }

    public function setResourcePerLevel(?int $resourcePerLevel): static
    {
        $this->resourcePerLevel = $resourcePerLevel;

        return $this;
    }

    public function getUpgradeTime(): ?int
    {
        return $this->upgradeTime;
    }

    public function setUpgradeTime(int $upgradeTime): static
    {
        $this->upgradeTime = $upgradeTime;

        return $this;
    }

    public function getTimeMultiplier(): ?float
    {
        return $this->timeMultiplier;
    }

    public function setTimeMultiplier(float $timeMultiplier): static
    {
        $this->timeMultiplier = $timeMultiplier;

        return $this;
    }

    public function getProductionRate(): ?int
    {
        return $this->productionRate;
    }

    public function setProductionRate(?int $productionRate): static
    {
        $this->productionRate = $productionRate;

        return $this;
    }

    public function getLastUpdate(): ?\DateTimeInterface
    {
        return $this->lastUpdate;
    }

    public function setLastUpdate(?\DateTimeInterface $lastUpdate): static
    {
        $this->lastUpdate = $lastUpdate;

        return $this;
    }

    public function getCoords(): ?string
    {
        return $this->coords;
    }

    public function setCoords(?string $coords): static
    {
        $this->coords = $coords;

        return $this;
    }
}
