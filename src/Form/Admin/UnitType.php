<?php

namespace App\Form\Admin;

use App\Entity\Admin\Force;
use App\Entity\Admin\Unit;
use App\Form\Admin\BirthCostType;
use App\Form\ForceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UnitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('birthCostOne', IntegerType::class, [
                'mapped' => false,
                'data' => $options['birthCostOne'],
            ])
            ->add('birthCostTwo', IntegerType::class, [
                'mapped' => false,
                'data' => $options['birthCostTwo'],
            ])
            ->add('forces', CollectionType::class, [
                'entry_type' => ForceType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'mapped' => true,
            ])
            ->add('health')
            ->add('birthTime')
            ->add('attack')
            ->add('defence')
            ->add('endurance')
            ->add('shield')
            ->add('speed')
            ->add('stockage')
            ->add('image')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Unit::class,
            'birthCostOne' => null,
            'birthCostTwo' => null,
        ]);
    }
}
