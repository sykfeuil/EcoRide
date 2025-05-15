<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class AccountDataForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('picture', FileType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Image([
                        'mimeTypesMessage' => 'Merci de choisir une image valide'
                    ]),
                ]
            ])
            ->add('driver')
            ->add('passenger')
            ->add('smoke')
            ->add('animal')
            ->add('preferences')
            ->add('credit', IntegerType::class, [
                'required' => false,
                'constraints' => [
                    new PositiveOrZero([
                        'message' => 'Merci de saisir une quanité de crédits positive ou nulle'
                    ]),
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
