<?php

namespace App\Form;

use App\Entity\Semestre;
use App\Entity\Niveau;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\GreaterThan;

class SemestreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libelle')
            ->add(
                'niveau',
                EntityType::class,
                [
                    'class' => Niveau::class,
                    'choice_label'  =>  'libelle',
                    'placeholder'   =>  ' -- Séléctionnez -- ',
                    'expanded' => false
                ]
            )
            ->add('startDate', DateType::class, [
                'widget' => 'single_text'
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new GreaterThan(
                        [
                            'propertyPath' => 'parent.all[startDate].data',
                            'message' => 'Date de fin devrait être plus récente que date de début !'
                        ]
                    )
                ]
            ])
            ->add(
                'ecolage', 
                NumberType::class,
                [
                    'required'    => false,
                    'invalid_message' => 'Merci d\'entrer un nombre !'
                ]
            ) 
            ->add(
                'credit', 
                IntegerType::class,
                [
                    'required'    => false
                ]
            ) 
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Semestre::class,
        ]);
    }
}
