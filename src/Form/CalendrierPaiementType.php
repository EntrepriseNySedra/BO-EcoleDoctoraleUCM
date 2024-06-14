<?php

namespace App\Form;

use App\Entity\CalendrierPaiement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CalendrierPaiementType extends AbstractType
{
     public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libelle')
            ->add('dateDebut',
                DateType::class,
                ['widget' => 'single_text']
            )
            ->add('dateFin',
                DateType::class,
                ['widget' => 'single_text']
            )
            ->add('statut', 

                TextType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'syllabus',
                TextareaType::class,
                [
                    'required' => false
                ]            
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CalendrierPaiement::class,
        ]);
    }
}
