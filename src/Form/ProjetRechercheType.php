<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\ProjetRecherche;
use App\Entity\DomaineRecherche;
use App\FormDomaineRechercheType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class ProjetRechercheType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('domaineRecherche', EntityType::class, [
                'class' => DomaineRecherche::class,
                'choice_label' => 'name', 
                'placeholder' => '-----  Sélectionnez ------',
                'expanded' => false,
            ])
            ->add('contribution', TextType::class, [
                'label' => 'Contribution',
            ])
            ->add('methodologie', TextType::class, [
                'label' => 'Methodologie',
            ])
            ->add('objectif', TextType::class, [
                'label' => 'Objectif',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
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
            'data_class' => ProjetRecherche::class,
        ]);
    }
}



