<?php

namespace App\Form;

use App\Entity\AnneeUniversitaire;
use App\Entity\Inscription;
use App\Entity\FraisScolarite;
use App\Entity\Mention;
use App\Entity\Niveau;
use App\Entity\Parcours;
use App\Services\Form\EntityTypeTools;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Doctrine\ORM\EntityRepository;

class InscriptionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $student = $options['student'] ? $options['student'] : null;
        $builder
            ->add(
                'niveau',
                EntityType::class,
                [
                    'class'        => Niveau::class,
                    'choice_label' => 'libelle',
                    'data'         => $student ? $student->getNiveau() : null,
                    'required'     => true,
                    'query_builder' => function (EntityRepository $er) use ($student)
                    {
                        $qb = $er->createQueryBuilder('n');
                                    //->andWhere('n.id = :niveauId')
                                   // ->setParameter('niveauId', $student->getNiveau());
                        return $qb;
                    }
                ]
            )
            ->add(
                'mention',
                EntityType::class,
                [
                    'class'        => Mention::class,
                    'choice_label' => 'nom',
                    'data'         => $student ? $student->getMention() : null,
                    'required'     => true,
                    'query_builder' => function (EntityRepository $er) use ($student)
                    {
                        $qb = $er->createQueryBuilder('m')
                                    ->andWhere('m.id = :mentionId')
                                    ->setParameter('mentionId', $student->getMention());
                        return $qb;
                    }
                ]
            )
            ->add(
                'parcours',
                EntityType::class,
                [
                    'class'        => Parcours::class,
                    'choice_label' => 'nom',
                    'placeholder'  => '-- Parcours --',
                    'required'     => false,
                    'query_builder' => function (EntityRepository $er) use ($student)
                    {
                        $qb = $er->createQueryBuilder('p')
                                    ->andWhere('p.id = :parcoursId')
                                    ->setParameter('parcoursId', $student->getParcours());
                        return $qb;
                    }
                ]
            )
            ->add(
                'payementRef',
                TextType::class,
                [
                    'required' => true,
                    'label'    => false,
                    'mapped'   => false
                ]
            )
            ->add(
                'payementRefPath',
                FileType::class,
                [
                    'required' => false,
                    'label'    => false
                ]
            )
            ->add(
                'payementRefDate',
                TextType::class,
                [
                    'required' => false,
                    'mapped'   => false
                ]
            )
            ->add(
                'anneeUniversitaire',
                EntityType::class,
                [
                    'class'        => AnneeUniversitaire::class,
                    'query_builder' => function (EntityRepository $er)
                    {
                        $currentYear = date('Y');
                        $qb = $er->createQueryBuilder('au')
                                ->andWhere('au.annee >= :pAnnee')
                                ->setParameter('pAnnee', $currentYear);
                        return $qb;
                    },
                    'choice_label' => 'libelle',
                    'required'     => true
                ]
            )
            ->add(
                'mode_paiement',
                ChoiceType::Class,
                [
                    'choices'  => [
                                    'Virement' => FraisScolarite::MODE_PAIEMENT_VIREMENT, 
                                    'Chèque' => FraisScolarite::MODE_PAIEMENT_AGENCE,
                                    'Espèces' => FraisScolarite::MODE_PAIEMENT_CAISSE,
                                    'Autre' => FraisScolarite::MODE_PAIEMENT_AUTRE
                                ],
                    'placeholder' => '-- Séléctionnez --',
                    'expanded' => false,
                    'mapped' => false,
                    'required' => false
                ]
            )
            ->add(
                'montant',
                MoneyType::class,
                [
                    'currency' => 'MGA',
                    'divisor'  => 1,
                    'required' => true,
                    'mapped' => false
                ]
            )
        ;

        $builder
            ->get('payementRefDate')
            ->addModelTransformer(
                new CallbackTransformer(
                    function ($payementRefDateTime)
                    {
                        return $payementRefDateTime;
                    },
                    function ($payementRefDateAsString)
                    {
                        $payementRefDate = \DateTime::createFromFormat('d/m/Y', $payementRefDateAsString);

                        return !$payementRefDate ? null : $payementRefDate;
                    }
                )
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Inscription::class,
                'student'    => []
            ]
        );
    }
}
