<?php

namespace App\Form;

use App\Entity\Ecolage;
use App\Entity\Mention;
use App\Entity\Parcours;
use App\Entity\Niveau;
use App\Entity\Semestre;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class EcolageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $ecolage = $options['data'];
        $builder
            ->add('montant',
                MoneyType::class,
                [
                    'currency' => 'Ar',
                    'divisor'  => 1,
                    'required' => true
                ])
            ->add('mention', EntityType::class, [
                'class' => Mention::class,
                'choice_label' => 'nom',
                'expanded'     => false
            ])
            ->add('niveau', EntityType::class, [
                'class' => Niveau::class,
                'choice_label' => 'libelle',
                'expanded'     => false,
                'required'     => true
            ])
            ->add('parcours', EntityType::class, [
                'class' => Parcours::class,
                'choice_label' => 'nom',
                'expanded'     => false,
                'required'     => false
            ])
            ->add('semestre', EntityType::class, [
                'class' => Semestre::class,
                'choice_label' => 'libelle',
                'expanded'     => false,
                'required'     => false
            ])
            ->add('limit_date', DateType::class, [
                'widget' => 'single_text'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Ecolage::class,
        ]);
    }
}
