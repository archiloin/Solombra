<?php

namespace App\Entity\Admin;

use App\Repository\Admin\ForceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ForceRepository::class)]
#[ORM\Table(name: '`force`')]
class Force
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'forces')]
    private ?ListHero $hero = null;

    #[ORM\ManyToOne(inversedBy: 'forces')]
    private ?Unit $unit = null;

    #[ORM\Column(nullable: true)]
    private ?bool $weak = null;

    #[ORM\Column]
    private ?bool $strong = null;

    #[ORM\Column]
    private ?bool $electromagnetic = null;

    #[ORM\Column]
    private ?bool $gravity = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHero(): ?ListHero
    {
        return $this->hero;
    }

    public function setHero(?ListHero $hero): static
    {
        $this->hero = $hero;

        return $this;
    }

    public function getUnit(): ?Unit
    {
        return $this->unit;
    }

    public function setUnit(?Unit $unit): static
    {
        $this->unit = $unit;

        return $this;
    }

    public function isWeak(): ?bool
    {
        return $this->weak;
    }

    public function setWeak(?bool $weak): static
    {
        $this->weak = $weak;

        return $this;
    }

    public function isStrong(): ?bool
    {
        return $this->strong;
    }

    public function setStrong(bool $strong): static
    {
        $this->strong = $strong;

        return $this;
    }

    public function isElectromagnetic(): ?bool
    {
        return $this->electromagnetic;
    }

    public function setElectromagnetic(bool $electromagnetic): static
    {
        $this->electromagnetic = $electromagnetic;

        return $this;
    }

    public function isGravity(): ?bool
    {
        return $this->gravity;
    }

    public function setGravity(bool $gravity): static
    {
        $this->gravity = $gravity;

        return $this;
    }
}
