<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\ProjetRecherche;
use App\Entity\SubventionRecherche;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class SubventionRechercheType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('projetRecherche', EntityType::class, [
                'class' => ProjetRecherche::class,
                'choice_label' => 'name', 
                'placeholder' => '-----  Sélectionnez ------',
                'expanded' => false,
            ])
            ->add('sourceFinancement', TextType::class, [
                'label' => 'SourceFinancement',
            ])
            ->add('exemple', TextareaType::class, [
                'label' => 'Exemple',
            ])
            ->add('critere', TextareaType::class, [
                'label' => 'Critere',
            ])
            ->add('procedureCandidature', TextareaType::class, [
                'label' => 'ProcedureCandidature',
            ])
            ->add('avantages', TextareaType::class, [
                'label' => 'Avantages',
            ])
            ->add('image', FileType::class, [
                'label' => 'Image (JPEG, PNG, GIF)',
                'mapped' => false,
                'required' => false,
                'attr' => ['accept' => 'image/*'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SubventionRecherche::class,
        ]);
    }
}
