<?php

namespace App\Form;

use App\Entity\UniteEnseignements;
use App\Entity\Mention;
use App\Entity\Parcours;
use App\Entity\Niveau;
use App\Entity\Semestre;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\ParcoursType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class UniteEnseignementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libelle')
            ->add(
                'type',
                ChoiceType::class,
                [
                    'placeholder' => ' -- Séléctionnez -- ',
                    'choices' => UniteEnseignements::$typeList
                ]
            )
            ->add(
                'mention',
                EntityType::class,
                [
                    'class' => Mention::class,
                    'choice_label' => 'nom',
                    'expanded' => false
                ]
            )
            ->add('parcours', EntityType::class, [
                'class' => Parcours::class,
                'choice_label' => 'nom',
                'expanded'     => false,
                'required'     => false
            ])
            ->add('niveau', EntityType::class, [
                'class' => Niveau::class,
                'choice_label' => 'libelle',
                'expanded'     => false
            ])
            ->add('semestre', EntityType::class, [
                'class' => Semestre::class,
                'choice_label' => 'libelle',
                'expanded'     => false
            ])
            ->add('credit')
            ->add(
                'active',
                CheckboxType::class,
                [
                    'required'     => false
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UniteEnseignements::class,
        ]);
    }
}
