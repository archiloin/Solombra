<?php

namespace App\Entity\Games;

use App\Repository\Games\ShadowFightConfigRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShadowFightConfigRepository::class)]
class ShadowFightConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $health = null;

    #[ORM\Column]
    private ?int $lvl = null;

    #[ORM\Column]
    private ?int $xp = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, ShadowFight>
     */
    #[ORM\OneToMany(mappedBy: 'config', targetEntity: ShadowFight::class)]
    private Collection $shadowFights;

    public function __construct()
    {
        $this->shadowFights = new ArrayCollection();
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

    public function getLvl(): ?int
    {
        return $this->lvl;
    }

    public function setLvl(int $lvl): static
    {
        $this->lvl = $lvl;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, ShadowFight>
     */
    public function getShadowFights(): Collection
    {
        return $this->shadowFights;
    }

    public function addShadowFight(ShadowFight $shadowFight): static
    {
        if (!$this->shadowFights->contains($shadowFight)) {
            $this->shadowFights->add($shadowFight);
            $shadowFight->setConfig($this);
        }

        return $this;
    }

    public function removeShadowFight(ShadowFight $shadowFight): static
    {
        if ($this->shadowFights->removeElement($shadowFight)) {
            // set the owning side to null (unless already changed)
            if ($shadowFight->getConfig() === $this) {
                $shadowFight->setConfig(null);
            }
        }

        return $this;
    }
}
