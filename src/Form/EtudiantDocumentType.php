<?php

namespace App\Form;

use App\Entity\EtudiantDocument;
use Doctrine\ORM\EntityRepository;

use App\Services\Form\EntityTypeTools;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EtudiantDocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $etudiantDocOptions = $options['data'];        
        // $cours   = $options['cours'];
        $builder
            ->add(
                'nom',
                TextType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'path',
                FileType::class,
                [
                    'data_class' => null,
                    'required' => false,
                    'label'    => false
                ]
            )
            ->add(
                'description',
                TextareaType::class,
                [
                    'required' => false
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'        => EtudiantDocument::class,
                'cours'             => [],  
                'em'                => []                
            ]
        );
    }
}
