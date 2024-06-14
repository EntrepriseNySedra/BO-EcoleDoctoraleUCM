<?php

namespace App\Form;

use App\Entity\Employe;
use App\Services\Form\EntityTypeTools;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class EmployeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'lastName',
                TextType::class,
                [
                    'label' => 'Nom'
                ]
            )
            ->add(
                'firstName',
                TextType::class,
                [
                    'label' => 'Prénom',
                    'required' => true,
                ]
            )
            ->add(
                'matricule',
                TextType::class,
                [
                    'label' => 'Matricule',
                    'required' => false,
                ]
            )
            ->add(
                'email',
                EmailType::class, [
                    'label' => 'Email',
                    'required' => true
                ]
            )
            ->add(
                'bankNum',
                TextType::class,
                [
                    'label' => 'Compte bancaire',
                    'required' => false,
                ] 
            )
            ->add(
                'tiersNum',
                TextType::class,
                [
                    'label' => 'Compte tiers',
                    'required' => false,
                ] 
            )
            ->add(
                'phone',
                TextType::class,
                [
                    'label' => 'Téléphone',
                    'required' => false,
                ] 
            )
        ;;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Employe::class,
                'em'         => [],
            ]
        );
    }
}
