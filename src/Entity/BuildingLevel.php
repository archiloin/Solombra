<?php

namespace App\Entity;

use App\Repository\BuildingLevelRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BuildingLevelRepository::class)]
class BuildingLevel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'buildingLevels')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Empire $empire = null;

    #[ORM\Column]
    private ?int $level = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Building $building = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $upgradeStartTime = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $upgradeEndTime = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmpire(): ?Empire
    {
        return $this->empire;
    }

    public function setEmpire(?Empire $empire): static
    {
        $this->empire = $empire;

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

    public function getBuilding(): ?Building
    {
        return $this->building;
    }

    public function setBuilding(?Building $building): static
    {
        $this->building = $building;

        return $this;
    }

    public function getUpgradeStartTime(): ?\DateTimeInterface
    {
        return $this->upgradeStartTime;
    }

    public function setUpgradeStartTime(?\DateTimeInterface $upgradeStartTime): static
    {
        $this->upgradeStartTime = $upgradeStartTime;

        return $this;
    }

    public function getUpgradeEndTime(): ?\DateTimeInterface
    {
        return $this->upgradeEndTime;
    }

    public function setUpgradeEndTime(?\DateTimeInterface $upgradeEndTime): static
    {
        $this->upgradeEndTime = $upgradeEndTime;

        return $this;
    }
}
