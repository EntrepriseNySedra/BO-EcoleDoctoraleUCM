<?php

namespace App\Form;

use App\Entity\BankCompte;
use App\Entity\Mention;
use App\Entity\Niveau;
use App\Entity\Parcours;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BankCompteType extends AbstractType
{
     public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('number',
                TextType::class,
                ['required' => true]
            )
            ->add('resource',
                ChoiceType::class,
                [
                    'choices' => BankCompte::$resourceList
                ]
            )
            ->add(
                'mention',
                EntityType::class,
                [
                    'class'        => Mention::class,
                    'choice_label' => 'nom',
                    'required'     => true
                ]
            )
            ->add(
                'niveau',
                EntityType::class,
                [
                    'class'        => Niveau::class,
                    'choice_label' => 'libelle',
                    'required'     => false
                ]
            )
            ->add(
                'parcours',
                EntityType::class,
                [
                    'class'        => Parcours::class,
                    'choice_label' => 'nom',
                    'required'     => false
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BankCompte::class,
        ]);
    }
}
