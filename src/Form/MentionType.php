<?php

namespace App\Form;

use App\Entity\Mention;
use App\Entity\Departement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class MentionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('departement', EntityType::class, [
                'class' => Departement::class,
                'choice_label' => 'nom',
                'expanded'     => false
            ])
            ->add('nom')
            ->add('diminutif', TextType::class, [
                'required' => true
            ])
            ->add('active')
            ->add('description')
            ->add('objectif')
            ->add('admission')
            ->add('diplomes')
            ->add('debouches')
            ->add('dmio')
            ->add('file',FileType::class,[
                'label'=> false,
                'attr'=>[
                    'type'=>'image',
                    'accept'=>'.png,.jpg,.jpeg',
                    'name'=>'data',
                ], 
                'mapped' => false,
                'required' =>false,
                'constraints' => [
                        new File([
                            'maxSize' => '10M',
                            'mimeTypes' => [
                                'image/*',
                            ],
                            'mimeTypesMessage' => 'Entrer une image valide!',
                        ])
                    ]
                ]
            )
            ->add('numCompteGenerale', TextType::class, [
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Mention::class,
        ]);
    }
}
