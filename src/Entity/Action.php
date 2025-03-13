<?php

namespace App\Entity;

use App\Repository\ActionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActionRepository::class)]
class Action
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $army = [];

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $startTime = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $endTime = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\ManyToOne(inversedBy: 'actions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Empire $empire = null;

    #[ORM\ManyToOne(inversedBy: 'targetActions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Empire $target = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $lootedResources = null;
    
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

    public function getArmy(): array
    {
        return $this->army;
    }

    public function setArmy(array $army): static
    {
        $this->army = $army;

        return $this;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeInterface $startTime): static
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTimeInterface $endTime): static
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
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

    public function getTarget(): ?Empire
    {
        return $this->target;
    }

    public function setTarget(?Empire $target): static
    {
        $this->target = $target;

        return $this;
    }

    public function getLootedResources(): ?array
    {
        return $this->lootedResources;
    }
    
    public function setLootedResources(?array $lootedResources): self
    {
        $this->lootedResources = $lootedResources;
        return $this;
    }
}