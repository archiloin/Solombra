<?php

namespace App\Entity;

use App\Repository\ResearchRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResearchRepository::class)]
class Research
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $coord = null;

    #[ORM\OneToMany(mappedBy: 'research', targetEntity: ResearchLevel::class)]
    private Collection $researchLevels;

    public function __construct()
    {
        $this->researchLevels = new ArrayCollection();
    }

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

    public function getCoord(): ?string
    {
        return $this->coord;
    }

    public function setCoord(string $coord): static
    {
        $this->coord = $coord;

        return $this;
    }

    /**
     * @return Collection<int, ResearchLevel>
     */
    public function getResearchLevels(): Collection
    {
        return $this->researchLevels;
    }

    public function addResearchLevel(ResearchLevel $researchLevel): static
    {
        if (!$this->researchLevels->contains($researchLevel)) {
            $this->researchLevels->add($researchLevel);
            $researchLevel->setResearch($this);
        }

        return $this;
    }

    public function removeResearchLevel(ResearchLevel $researchLevel): static
    {
        if ($this->researchLevels->removeElement($researchLevel)) {
            // set the owning side to null (unless already changed)
            if ($researchLevel->getResearch() === $this) {
                $researchLevel->setResearch(null);
            }
        }

        return $this;
    }
}
