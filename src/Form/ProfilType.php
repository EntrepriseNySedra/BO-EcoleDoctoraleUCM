<?php

namespace App\Form;

use App\Entity\Profil;
use App\Entity\Roles;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ProfilType
 *
 * @package App\Form
 * @author Joelio
 */
class ProfilType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array                                        $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add(
                'status',
                ChoiceType::class,
                [
                    'choices' => Profil::$statusList
                ]
            )
            ->add(
                'roles',
                EntityType::class,
                [
                    'class'        => Roles::class,
                    'choice_label' => 'name',
                    'multiple'     => true,
                    'expanded'     => true
                ]
            )
        ;
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Profil::class,
            ]
        );
    }
}
