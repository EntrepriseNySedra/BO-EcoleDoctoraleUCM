<?php

namespace App\Form;

use App\Entity\Etudiant;
use App\Entity\ExtraNote;
use App\Entity\Inscription;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExtraNoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $note        = $options['note'];
        $mention     = $options['mention'];
        $status      = $options['status'];
        $collegeYear = $note instanceof ExtraNote ? $note->getAnneeUniversitaire() : $options['collegeYear'];

        $builder
            ->add(
                'etudiant',
                EntityType::class,
                [
                    'class'         => Etudiant::class,
                    'query_builder' => function (EntityRepository $er) use ($mention, $collegeYear, $note)
                    {
                        $qb = $er->createQueryBuilder('e')
                                 ->innerJoin(Inscription::class, 'i')
                                 ->where('i.mention = :mention')
                                 ->setParameter('mention', $mention)
                                 ->andWhere('i.etudiant = e.id')
                                 ->andWhere('i.anneeUniversitaire = :collegeYear')
                                 ->setParameter('collegeYear', $collegeYear)
                        ;
                        if ($note) {
                            $qb->andWhere('e.id = :studentId')
                               ->setParameter('studentId', $note->getEtudiant()->getId())
                            ;
                        }
                        $qb
                            ->groupBy('e.id')
                            ->orderBy('e.firstName', 'ASC')
                        ;

                        return $qb;
                    },
                    'choice_label'  => function ($e)
                    {
                        return $e->fullName();
                    },
                    'placeholder'   => '-- Séléctionnez --',
                    'expanded'      => false,
                    'required'      => true
                ]
            )
            ->add(
                'note'
            )
            ->add(
                'type',
                ChoiceType::class,
                [
                    'placeholder' => ' -- Séléctionnez -- ',
                    'choices'     => ExtraNote::$typeList,
                    'expanded'    => false,
                    'required'    => true
                ]
            )
            ->add(
                'status',
                ChoiceType::class,
                [
                    'placeholder' => ' -- Séléctionnez -- ',
                    'choices'     => $status,
                    'expanded'    => false,
                    'required'    => true
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'  => ExtraNote::class,
                'mention'     => null,
                'collegeYear' => null,
                'note'        => null,
                'status'      => ExtraNote::$statusList,
            ]
        );
    }
}
