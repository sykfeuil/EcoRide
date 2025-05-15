<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class TravelFilterForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startingPlace', TextType::class, [
                'required' => false,
            ])
            ->add('endingDate', DateType::class, [
                'required' => true,
            ])
            ->add('endingPlace', TextType::class, [
                'required' => true,
            ])
            ->add('ecoFilter', CheckboxType::class, [
                'required' => false,
            ])
            ->add('maxPriceFilter', IntegerType::class, [
                'required' => false,
                'constraints' => [
                    new PositiveOrZero([
                        'message' => 'Merci de saisir un prix positif ou nul'
                    ]),
                ]
            ])
            ->add('timeFilter', IntegerType::class, [
                'required' => false,
                'constraints' => [
                    new PositiveOrZero([
                        'message' => 'Merci de saisir une durée positive ou nulle'
                    ]),
                ]
            ])
            ->add('markFilter', IntegerType::class, [
                'required' => false,
                'constraints' => [
                    new PositiveOrZero([
                        'message' => 'Merci de saisir une note positive ou nulle'
                    ]),
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
