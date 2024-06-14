<?php

namespace App\Form;

use App\Entity\CalendrierExamenSurveillance;
use App\Entity\User;
use App\Entity\Profil;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CalExamenSurvType extends AbstractType
{
     public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'surveillant',
                EntityType::class,
                [
                    'class'         => User::class,
                    'query_builder' => function (EntityRepository $er)
                    {
                        $survProfileName = Profil::SURVEILLANT;
                        $qb = $er->createQueryBuilder('u')
                                ->innerJoin('u.profil', 'p')
                                ->innerJoin('p.roles', 'r')
                                ->addSelect('p')
                                ->addSelect('r');
                            $qb->where('p.name = :pProfil')
                            ->setParameter('pProfil', $survProfileName);
                        return $qb;  
                    },
                    'choice_label'  => function ($e)
                    {
                        return $e->getFirstName() . ' ' . $e->getLastName();
                    },
                    'choice_value' => 'id',
                    'group_by' => function($e) {
                        return $e->getProfil()->getName();
                    },
                    'placeholder'   => '-- Séléctionnez --',
                    'expanded'      => false,
                    'mapped'        => true,
                    'required'      => true,
                    'multiple'      => true,
                    'attr' => [
                        'class' => 'js-example-basic-multiple'
                    ]
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CalendrierExamenSurveillance::class
        ]);
    }
}
