<?php

namespace App\Form;

use App\Entity\Examens;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExamenType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libelle')
            ->add(
                'deliberation',
                NumberType::class,
                [
                    'invalid_message' => 'Merci d\'entrer un nombre !'
                ]
            )
            ->add(
                'starDate',
                DateType::class,
                [
                    'widget' => 'single_text'
                ]
            )
            ->add(
                'endDate',
                DateType::class,
                [
                    'widget' => 'single_text'
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Examens::class,
            ]
        );
    }
}
