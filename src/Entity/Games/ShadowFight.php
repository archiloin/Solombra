<?php

namespace App\Entity\Games;

use App\Entity\Empire;
use App\Repository\Games\ShadowFightRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShadowFightRepository::class)]
class ShadowFight
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'shadowFight', cascade: ['persist', 'remove'])]
    private ?Empire $empire = null;

    #[ORM\ManyToOne(inversedBy: 'shadowFights')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ShadowFightConfig $config = null;

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

    public function getConfig(): ?ShadowFightConfig
    {
        return $this->config;
    }

    public function setConfig(?ShadowFightConfig $config): static
    {
        $this->config = $config;

        return $this;
    }

}
