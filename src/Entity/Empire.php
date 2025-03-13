<?php

namespace App\Entity;

use App\Entity\Games\TileMystique;
use App\Entity\Games\ShadowFight;
use App\Entity\Resource;
use App\Entity\Universe\Players;
use App\Repository\EmpireRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmpireRepository::class)]
class Empire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'heroes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(nullable: true)]
    private ?bool $selected = null;

    #[ORM\OneToMany(mappedBy: 'empire', targetEntity: BuildingLevel::class)]
    private Collection $buildingLevels;

    #[ORM\OneToMany(mappedBy: 'empire', targetEntity: UnitInQueue::class)]
    private Collection $unitsInQueue;

    #[ORM\OneToOne(inversedBy: 'empire', cascade: ['persist', 'remove'])]
    private ?Unit $unit = null;

    #[ORM\ManyToOne]
    private ?Map $zone = null;

    #[ORM\Column(nullable: true)]
    private ?int $dimension = null;

    #[ORM\OneToMany(mappedBy: 'empire', targetEntity: Action::class)]
    private Collection $actions;

    #[ORM\OneToMany(mappedBy: 'target', targetEntity: Action::class)]
    private Collection $targetActions;

    #[ORM\OneToMany(mappedBy: 'attacker', targetEntity: BattleLog::class)]
    private Collection $battleLogs;

    #[ORM\OneToMany(mappedBy: 'empire', targetEntity: Resource::class)]
    private Collection $resources;

    #[ORM\ManyToOne(inversedBy: 'empire')]
    private ?Hero $hero = null;

    #[ORM\OneToOne(mappedBy: 'empire', cascade: ['persist', 'remove'])]
    private ?ShadowFight $shadowFight = null;

    #[ORM\OneToOne(mappedBy: 'empire', cascade: ['persist', 'remove'])]
    private ?TileMystique $tileMystique = null;

    #[ORM\OneToOne(mappedBy: 'empire', cascade: ['persist', 'remove'])]
    private ?Players $player = null;

    public function __construct()
    {
        $this->buildings = new ArrayCollection();
        $this->buildingLevels = new ArrayCollection();
        $this->unitsInQueue = new ArrayCollection();
        $this->actions = new ArrayCollection();
        $this->targetActions = new ArrayCollection();
        $this->battleLogs = new ArrayCollection();
        $this->resources = new ArrayCollection();
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Vérifie si l'empire a un hero.
     *
     * @return bool
     */
    public function hasHero(): bool
    {
        // Retourner true si hero n'est pas null, sinon false
        return $this->hero !== null;
    }

    public function isSelected(): ?bool
    {
        return $this->selected;
    }

    public function setSelected(bool $selected): static
    {
        $this->selected = $selected;

        return $this;
    }

    /**
     * @return Collection<int, BuildingLevel>
     */
    public function getBuildingLevels(): Collection
    {
        return $this->buildingLevels;
    }

    public function addBuildingLevel(BuildingLevel $buildingLevel): static
    {
        if (!$this->buildingLevels->contains($buildingLevel)) {
            $this->buildingLevels->add($buildingLevel);
            $buildingLevel->setEmpire($this);
        }

        return $this;
    }

    public function removeBuildingLevel(BuildingLevel $buildingLevel): static
    {
        if ($this->buildingLevels->removeElement($buildingLevel)) {
            // set the owning side to null (unless already changed)
            if ($buildingLevel->getEmpire() === $this) {
                $buildingLevel->setEmpire(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UnitInQueue>
     */
    public function getUnitsInQueue(): Collection
    {
        return $this->unitsInQueue;
    }

    public function addUnitInQueue(UnitInQueue $unit): static
    {
        if (!$this->unitsInQueue->contains($unit)) {
            $this->unitsInQueue->add($unit);
            $unitInQueue->setEmpire($this);
        }

        return $this;
    }

    public function removeUnit(UnitInQueue $unitInQueue): static
    {
        if ($this->unitsInQueue->removeElement($unitInQueue)) {
            // set the owning side to null (unless already changed)
            if ($unitInQueue->getEmpire() === $this) {
                $unitInQueue->setEmpire(null);
            }
        }

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

    public function getZone(): ?Map
    {
        return $this->zone;
    }

    public function setZone(?Map $zone): static
    {
        $this->zone = $zone;

        return $this;
    }

    public function getDimension(): ?int
    {
        return $this->dimension;
    }

    public function setDimension(?int $dimension): static
    {
        $this->dimension = $dimension;

        return $this;
    }

    /**
     * @return Collection<int, Action>
     */
    public function getActions(): Collection
    {
        return $this->actions;
    }

    public function addAction(Action $action): static
    {
        if (!$this->actions->contains($action)) {
            $this->actions->add($action);
            $action->setEmpire($this);
        }

        return $this;
    }

    public function removeAction(Action $action): static
    {
        if ($this->actions->removeElement($action)) {
            // set the owning side to null (unless already changed)
            if ($action->getEmpire() === $this) {
                $action->setEmpire(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Action>
     */
    public function getTargetActions(): Collection
    {
        return $this->targetActions;
    }

    public function addTargetAction(Action $targetAction): static
    {
        if (!$this->targetActions->contains($targetAction)) {
            $this->targetActions->add($targetAction);
            $targetAction->setTarget($this);
        }

        return $this;
    }

    public function removeTargetAction(Action $targetAction): static
    {
        if ($this->targetActions->removeElement($targetAction)) {
            // set the owning side to null (unless already changed)
            if ($targetAction->getTarget() === $this) {
                $targetAction->setTarget(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, BattleLog>
     */
    public function getBattleLogs(): Collection
    {
        return $this->battleLogs;
    }

    public function addBattleLog(BattleLog $battleLog): static
    {
        if (!$this->battleLogs->contains($battleLog)) {
            $this->battleLogs->add($battleLog);
            $battleLog->setAttacker($this);
        }

        return $this;
    }

    public function removeBattleLog(BattleLog $battleLog): static
    {
        if ($this->battleLogs->removeElement($battleLog)) {
            // set the owning side to null (unless already changed)
            if ($battleLog->getAttacker() === $this) {
                $battleLog->setAttacker(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Resource>
     */
    public function getResources(): Collection
    {
        return $this->resources;
    }

    public function addResource(Resource $resource): void
    {
        foreach ($this->resources as $existingResource) {
            if ($existingResource->getInfo()->getId() === $resource->getInfo()->getId()) {
                $existingResource->setQuantity($existingResource->getQuantity() + $resource->getQuantity());
                return;
            }
        }
        // Si la ressource n'existe pas déjà, l'ajouter à la collection
        $this->resources->add($resource);
        $resource->setEmpire($this);
    }
    
    public function subtractResource(Resource $resource): void
    {
        foreach ($this->resources as $existingResource) {
            if ($existingResource->getInfo()->getId() === $resource->getInfo()->getId()) {
                $newQuantity = $existingResource->getQuantity() - $resource->getQuantity();
                $existingResource->setQuantity(max($newQuantity, 0)); // Eviter les quantités négatives
                return;
            }
        }
        // Pas besoin de gérer le cas où la ressource n'existe pas, car on ne peut soustraire une ressource inexistante
    }

    public function getHero(): ?Hero
    {
        return $this->hero;
    }

    public function setHero(?Hero $hero): static
    {
        $this->hero = $hero;

        return $this;
    }

    public function getShadowFight(): ?ShadowFight
    {
        return $this->shadowFight;
    }

    public function setShadowFight(?ShadowFight $shadowFight): static
    {
        // unset the owning side of the relation if necessary
        if ($shadowFight === null && $this->shadowFight !== null) {
            $this->shadowFight->setEmpire(null);
        }

        // set the owning side of the relation if necessary
        if ($shadowFight !== null && $shadowFight->getEmpire() !== $this) {
            $shadowFight->setEmpire($this);
        }

        $this->shadowFight = $shadowFight;

        return $this;
    }

    public function getTileMystique(): ?TileMystique
    {
        return $this->tileMystique;
    }

    public function setTileMystique(TileMystique $tileMystique): static
    {
        // set the owning side of the relation if necessary
        if ($tileMystique->getEmpire() !== $this) {
            $tileMystique->setEmpire($this);
        }

        $this->tileMystique = $tileMystique;

        return $this;
    }

    public function getPlayer(): ?Players
    {
        return $this->player;
    }

    public function setPlayer(Players $player): static
    {
        // set the owning side of the relation if necessary
        if ($player->getEmpire() !== $this) {
            $player->setEmpire($this);
        }

        $this->player = $player;

        return $this;
    }

}
