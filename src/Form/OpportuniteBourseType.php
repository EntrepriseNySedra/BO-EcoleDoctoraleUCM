<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\BourseDistinction;
use App\Entity\OpportuniteBourse;
//use App\FormDomaineRechercheType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class OpportuniteBourseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('name', TextType::class, [
            'label' => 'Nom',
        ])
        ->add('bourse', EntityType::class, [
            'class' => BourseDistinction::class,
            'choice_label' => 'name', 
            'placeholder' => '-----  Sélectionnez ------',
            'expanded' => false,
        ])
        ->add('delais', DateType::class, [
            'label' => 'Delais',
            'widget' => 'single_text', 
            'format' => 'yyyy-MM-dd', 
            'html5' => true, 
        ])
        ->add('montant', NumberType::class, [
            'label' => 'Montant',
            'scale' => 2, 
        ])
        ->add('critere', TextareaType::class, [
            'label' => 'Critere',
        ])
        ->add('image', FileType::class, [
            'label' => 'Image (JPEG, PNG, GIF)',
            'mapped' => false,
            'required' => false,
            'attr' => ['accept' => 'image/*'],
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
