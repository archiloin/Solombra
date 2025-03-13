<?php
namespace App\Service;

use App\Entity\Admin\Unit;
use Doctrine\ORM\EntityManagerInterface;

class SpeedCalculator
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function calculSpeed(array $selectedUnits): array
    {
        $maxSpeed = PHP_INT_MAX;

        foreach ($selectedUnits as $unitId => $quantity) {
            $unit = $this->entityManager->getRepository(Unit::class)->find($unitId);
            if ($unit && $unit->getSpeed() !== null) {
                $maxSpeed = min($maxSpeed, $unit->getSpeed());
            }
        }

        if ($maxSpeed === PHP_INT_MAX || $maxSpeed <= 0) {
            throw new \Exception("Impossible de calculer le temps d'attente : aucune unité valide avec une vitesse définie.");
        }

        $travelTimeInSeconds = (int)(2000 / $maxSpeed);
        $currentTime = new \DateTime();
        $executeTime = (clone $currentTime)->modify("+{$travelTimeInSeconds} seconds");

        return [
            'travelTimeInSeconds' => $travelTimeInSeconds,
            'executeTime' => $executeTime,
        ];
    }
}