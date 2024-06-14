<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;

use FOS\CKEditorBundle\Form\Type\CKEditorType;


use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('mot_cle')
            ->add('description')
            ->add(
                'emplacement',
                ChoiceType::class,
                [
                    'choices' => Article::$emplacementTypeList,
                    'placeholder'  => '-- Selectionnez --',
                    'required' =>false
                ]
            )
            ->add('url')

            ->add(
                'detail',
                CKEditorType::class,
                [
                    'required' => false
                ]
            )


            ->add('active')
            ->add('resourceUuid',HiddenType::class,[])
            ->add('content_header')
            ->add('content_left')
            ->add('content_right')
            ->add('content_footer')
            ->add(
                'ressourceType',
                ChoiceType::class,
                [
                    'choices' => Article::$ressourceTypeList
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
                            'maxSize' => '10M',
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
            'data_class' => Article::class,
        ]);
    }
}
