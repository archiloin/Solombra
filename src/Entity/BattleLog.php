<?php

namespace App\Entity;

use App\Repository\BattleLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BattleLogRepository::class)]
class BattleLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'battleLogs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Empire $attacker = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Empire $defender = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $details = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $battleTime = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAttacker(): ?Empire
    {
        return $this->attacker;
    }

    public function setAttacker(?Empire $attacker): static
    {
        $this->attacker = $attacker;

        return $this;
    }

    public function getDefender(): ?Empire
    {
        return $this->defender;
    }

    public function setDefender(?Empire $defender): static
    {
        $this->defender = $defender;

        return $this;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(?string $details): static
    {
        $this->details = $details;

        return $this;
    }

    public function getBattleTime(): ?\DateTimeInterface
    {
        return $this->battleTime;
    }

    public function setBattleTime(?\DateTimeInterface $battleTime): static
    {
        $this->battleTime = $battleTime;

        return $this;
    }
}
