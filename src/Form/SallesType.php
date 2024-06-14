<?php

namespace App\Form;

use App\Entity\Salles;
use App\Entity\Batiment;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SallesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            
            ->add('libelle')
            ->add('capacite',
                IntegerType::class,
                    [
                        'required'  => false
                    ]
            )
            ->add(
                'batiment',
                EntityType::class,
                [
                    'class' => Batiment::class,
                    'choice_label'     => 'nom',
                    'placeholder'  => '-- Séléctionnez --',
                    'expanded'  => false
                ]
            )
            ->add('internetConnexionOn', CheckboxType::class, [
                'label'    => 'Connexion ',
                'required' => false,
            ])
            ->add('videoProjecteurOn', CheckboxType::class, [
                'label'    => 'Vidéo projecteur ',
                'required' => false,
            ])
            ->add('status')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Salles::class,
        ]);
    }
}
