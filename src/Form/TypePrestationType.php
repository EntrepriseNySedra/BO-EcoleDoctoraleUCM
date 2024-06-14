<?php

namespace App\Form;

use App\Entity\TypePrestation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TypePrestationType extends AbstractType
{
     public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('designation',
                TextType::class,
                ['required' => true]
            )
            ->add('type',
                TextType::class,
                ['required' => false]
            )
            ->add('unite',
                TextType::class,
                ['required' => true]
            )
            ->add('taux', 
                MoneyType::class,
                [
                    'currency' => false,
                    'grouping' => 3,
                    'required' => false
                ]
            )
            ->add(
                'code',
                NumberType::class,
                [
                    'required' => false
                ]            
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TypePrestation::class,
        ]);
    }
}
