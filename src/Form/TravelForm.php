<?php

namespace App\Form;

use App\Entity\Car;
use App\Entity\Travel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Positive;

class TravelForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startingDate', DateType::class, [
                'required' => true,
            ])
            ->add('startingHour', TimeType::class, [
                'required' => true,
            ])
            ->add('startingPlace', TextType::class, [
                'required' => true,
                'sanitize_html' => true,
            ])
            ->add('endingDate', DateType::class, [
                'required' => true,
            ])
            ->add('endingHour', TimeType::class, [
                'required' => true,
            ])
            ->add('endingPlace', TextType::class, [
                'required' => true,
                'sanitize_html' => true,
            ])
            ->add('price', IntegerType::class, [
                'required' => true,
                'constraints' => [
                    new GreaterThanOrEqual([
                        'value' => 2,
                        'message' => 'Merci de choisir un nombre entier supérieur à 2'
                    ]),
                ]
            ])
            ->add('car', EntityType::class, [
                'class' => Car::class,
                'choice_label' => function(Car $car) {
                    return $car->getBrand() . ' ' . $car->getType() . ' : Immatriculé ' . $car->getSerialNumber();
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Travel::class,
        ]);
    }
}
