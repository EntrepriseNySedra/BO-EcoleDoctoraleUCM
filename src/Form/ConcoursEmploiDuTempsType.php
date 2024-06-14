<?php

namespace App\Form;

use App\Entity\Concours;
use App\Entity\ConcoursEmploiDuTemps;
use App\Entity\ConcoursMatiere;
use App\Entity\Salles;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConcoursEmploiDuTempsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $emploiDuTempsOptions = $options['emploiDuTemps'];

        $salleId    = $emploiDuTempsOptions instanceof ConcoursEmploiDuTemps ?
            $emploiDuTempsOptions
                ->getSalles()
                ->getId() : null;
        $matiereId  = $emploiDuTempsOptions instanceof ConcoursEmploiDuTemps ?
            $emploiDuTempsOptions
                ->getConcoursMatiere()
                ->getId() : null;
        $concoursId = $emploiDuTempsOptions instanceof ConcoursEmploiDuTemps ?
            $emploiDuTempsOptions
                ->getConcours()
                ->getId() : null;

        $builder
            ->add(
                'startDate',
                DateTimeType::class,
                [
                    'widget' => 'single_text'
                ]
            )
            ->add(
                'endDate',
                DateTimeType::class,
                [
                    'widget' => 'single_text'
                ]
            )
            ->add(
                'salle',
                EntityType::class,
                [
                    'class'        => Salles::class,
                    'choice_label' => 'libelle',
                    'placeholder'  => '-- Salles --',
                    'data'         => ($em = $options['em']) ? $em->getReference(Salles::class, $salleId) : null
                ]
            )
            ->add(
                'concoursMatiere',
                EntityType::class,
                [
                    'class'        => ConcoursMatiere::class,
                    'choice_label' => 'libelle',
                    'placeholder'  => '-- Matière --'
                ]
            )
            ->add(
                'concours',
                EntityType::class,
                [
                    'class'        => Concours::class,
                    'choice_label' => 'libelle',
                    'placeholder'  => '-- Concours --',
                    'data'         => ($em = $options['em']) ? $em->getReference(Concours::class, $concoursId) : null
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => ConcoursEmploiDuTemps::class,
                'emploiDuTemps'      => [],
                'em'                 => [],
                'allow_extra_fields' => true
            ]
        );
    }
}
