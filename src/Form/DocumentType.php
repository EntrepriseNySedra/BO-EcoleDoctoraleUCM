<?php

namespace App\Form;

use App\Entity\Document;
use App\Entity\Actualite;
use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('ordre')
            ->add('actualite',
                EntityType::class,
                    [
                    'class'         => Actualite::class,
                    'choice_label'  => 'title',
                    'expanded'      => false
                ])
            ->add('article',
                EntityType::class,
                    [
                    'class'         => Article::class,
                    'choice_label'  => 'title',
                    'expanded'      => false
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Document::class,
        ]);
    }
}
