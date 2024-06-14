<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\ArticleEcole;
use App\Entity\TypeArticleEcole;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Validator\Constraints\File;

class ArticleEcoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('titre', TextType::class, [
            'label' => 'Titre',
        ])
        ->add('typeArticleEcole', EntityType::class, [
            'class' => TypeArticleEcole::class,
            'choice_label' => 'name', 
            'placeholder' => '-----  Sélectionnez ------',
            'expanded' => false,
        ])
        ->add('detail', CKEditorType::class, [
            'label' => 'Detail',
        ])
        ->add('description', CKEditorType::class, [
            'label' => 'Description',
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
            'data_class' => ArticleEcole::class,
        ]);
    }
}
