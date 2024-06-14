<?php

namespace App\Form;

use App\Entity\Actualite;
use App\Entity\Rubrique;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Validator\Constraints\File;

class ActualiteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('mot_cle')
            ->add('description')
            ->add('detail', CKEditorType::class, [
                'label' => 'Detail',
            ])
            ->add('active')
            ->add('resourceUuid',HiddenType::class,[])
            ->add(
                'ressourceType',
                ChoiceType::class,
                [
                    'choices' => Actualite::$ressourceTypeList
                ]
            )
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
                            'maxSize' => '20M',
                            'mimeTypes' => [
                                'image/*',
                            ],
                            'mimeTypesMessage' => 'Entrer une image valide!',
                        ])
                    ]
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Actualite::class,
        ]);
    }
}
