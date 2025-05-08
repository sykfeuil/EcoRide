<?php

namespace App\Form;

use App\Entity\Car;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\CssColor;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Regex;

class CarForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('SerialNumber', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Regex([
                        'pattern' => '/[A-HJ-NP-TV-Z]{2}[\s-]{0,1}[0-9]{3}[\s-]{0,1}[A-HJ-NP-TV-Z]{2}|[0-9]{2,4}[\s-]{0,1}[A-Z]{1,3}[\s-]{0,1}[0-9]{2}/',
                        'match' => true,
                        'message' => 'Merci de renseigner un numéro de plaque valide'
                    ]),
                ]
            ])
            ->add('SerialDate', DateType::class, [
                'required' => true,
            ])
            ->add('Type', TextType::class, [
                'required' => true,
            ])
            ->add('Color', ColorType::class, [
                'required' => true,
                'constraints' => [
                    new CssColor([
                        'formats' => 'hex_long',
                        'message' => 'Merci de choisir une couleur valide'
                    ]),
                ]
            ])
            ->add('Brand', TextType::class, [
                'required' => true,
            ])
            ->add('Seats', NumberType::class, [
                'required' => true,
                'constraints' => [
                    new Positive([
                        'message' => 'Merci de choisir un nombre entier strictement positif'
                    ]),
                ]
            ])
            ->add('Electrical')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Car::class,
        ]);
    }
}
