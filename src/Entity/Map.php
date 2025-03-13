<?php

namespace App\Entity;

use App\Repository\MapRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MapRepository::class)]
class Map
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $coord = null;

    #[ORM\Column(nullable: true)]
    private ?int $markerX = null;

    #[ORM\Column(nullable: true)]
    private ?int $markerY = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
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

    public function getMarkerX(): ?int
    {
        return $this->markerX;
    }

    public function setMarkerX(?int $markerX): static
    {
        $this->markerX = $markerX;

        return $this;
    }

    public function getMarkerY(): ?int
    {
        return $this->markerY;
    }

    public function setMarkerY(?int $markerY): static
    {
        $this->markerY = $markerY;

        return $this;
    }
}
