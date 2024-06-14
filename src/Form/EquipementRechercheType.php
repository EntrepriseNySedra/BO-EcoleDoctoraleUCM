<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\EquipementRecherche;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class EquipementRechercheType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('name', TextType::class, [
            'label' => 'Nom',
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
            'data_class' => EquipementRecherche::class,
        ]);
    }
}
