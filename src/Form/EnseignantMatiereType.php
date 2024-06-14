<?php

namespace App\Form;

use App\Entity\EnseignantMatiere;
use App\Entity\Enseignant;
use App\Entity\Matiere;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EnseignantMatiereType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'Enseignant',
                EntityType::class,
                [
                    'class'        => Enseignant::class,
                    'choice_label' => 'firstName',
                    'placeholder'  => '-- Enseignant --',
                    'required'     => true
                ]
            )
            ->add(
                'Matiere',
                EntityType::class,
                [
                    'class'        => Matiere::class,
                    'choice_label' => 'nom',
                    'placeholder'  => '-- Matiere --',
                    'required'     => true
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EnseignantMatiere::class,
        ]);
    }
}
