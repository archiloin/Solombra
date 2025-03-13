<?php
namespace App\Form;

use App\Entity\Admin\Unit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UnitSelectionType extends AbstractType
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $availableUnits = $options['available_units'];

        foreach ($availableUnits as $unitId => $quantity) {
            $fieldName = 'unit_quantity_' . $unitId;
            $unitName = $this->getUnitNameById($unitId); // Utilisez le gestionnaire d'entité injecté
    
            $builder->add($fieldName, NumberType::class, [
                'label' => $unitName . ' (Quantité : ' . $quantity . ')',
                'attr' => [
                    'min' => 0,
                    'max' => $quantity,
                    'value' => 0
                ]
            ]);
        }
    }
    
    public function getUnitNameById(int $unitId): string
    {
        $unit = $this->entityManager->getRepository(Unit::class)->find($unitId);

        if ($unit) {
            return $unit->getName(); // Remplacez 'getName()' par la méthode réelle pour obtenir le nom depuis l'entité ListUnit
        }

        return ''; // Gérez le cas où l'unité n'est pas trouvée
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'available_units' => [],
            // Autres options par défaut
        ]);
    }
}