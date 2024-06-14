<?php

namespace App\Form;

use App\Entity\Cours;
use App\Entity\Matiere;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\File;

class CoursType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libelle')
            ->add(
                'description',
                CKEditorType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'matiere',
                EntityType::class,
                    [
                    'class'         => Matiere::class,
                    'choice_label'  => 'nom',
                    'expanded'      => false
                ]
            )

            ->add(
                'coursMedia',
                CollectionType::class,
                [
                    'entry_type'   => CoursMediaType::class,
                    'prototype'    => true,
                    'allow_add'    => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'required'     => false,
                    'label'        => false,
                ]
            )
            ->add(
                'nbSequence',
                IntegerType::class,
                [
                    'required' => false
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Cours::class,
        ]);
    }
}
