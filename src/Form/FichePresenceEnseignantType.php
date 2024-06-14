<?php

namespace App\Form;

use App\Entity\Departement;
use App\Entity\Mention;
use App\Entity\Niveau;
use App\Entity\Parcours;
use App\Entity\UniteEnseignements;
use App\Entity\Matiere;
use App\Entity\FichePresenceEnseignant;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FichePresenceEnseignantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fichePresenceEnseignantOptions = $options['fichePresenceEnseignant'];
        $enseignantId   = $fichePresenceEnseignantOptions instanceof FichePresenceEnseignant ? $fichePresenceEnseignantOptions->getEnseignant()->getId() : null;
        $matiereId      = $fichePresenceEnseignantOptions instanceof FichePresenceEnseignant ? $fichePresenceEnseignantOptions->getMatiere()->getId() : null;
        $domaineId      = $fichePresenceEnseignantOptions instanceof FichePresenceEnseignant ? $fichePresenceEnseignantOptions->getDomaine()->getId() : null;
        $parcoursId     = $fichePresenceEnseignantOptions instanceof FichePresenceEnseignant && $fichePresenceEnseignantOptions->getParcours() ? $fichePresenceEnseignantOptions->getParcours()->getId() : null;

        $niveauId       = $fichePresenceEnseignantOptions instanceof FichePresenceEnseignant ? $fichePresenceEnseignantOptions->getNiveau()->getId() : null;
        $ueId           = $fichePresenceEnseignantOptions instanceof FichePresenceEnseignant ? $fichePresenceEnseignantOptions->getUe()->getId() : null;

        // if(count($fichePresenceEnseignantOptions->getParcours()) > 0 && $fichePresenceEnseignantOptions->getParcours() !== null){
        //     $parcoursId = $fichePresenceEnseignantOptions instanceof FichePresenceEnseignant ? $fichePresenceEnseignantOptions->getParcours()->getId() : null;
        //     $data       = true;
        // }
        // else{
            $parcoursId = null;
            $data       = false;
        // }


        $builder
            ->add(
                'domaine',
                EntityType::class,
                [
                    'class'             => Departement::class,
                    'choice_label'      => 'nom',
                    'placeholder'       => '-- Séléctionnez --',
                    'expanded'          => false
                ]
            )
            ->add(
                'mention',
                EntityType::class,
                [
                    'class'         => Mention::class,
                    'choice_label'  => 'nom',
                    'placeholder'   => '-- Séléctionnez --',
                    'expanded'      => false
                ]
            )
            ->add(
                'niveau',
                EntityType::class,
                [
                    'class'         => Niveau::class,
                    'choice_label'  => 'libelle',
                    'placeholder'   => '-- Séléctionnez --',
                    'expanded'      => false,
                    'data'          => ($em = $options['em']) ? $em->getReference(Niveau::class, $niveauId) : null
                ]
            )
            ->add(

                'parcours',
                EntityType::class,
                [
                    'class' => Parcours::class,
                    'choice_label'     => 'nom',
                    'placeholder'  => '-- Séléctionnez --',
                    'expanded'  => false,
                    'required'  => false
                ]
            )
/*
                    'parcours',
                    EntityType::class,
                    [
                        'class'         => Parcours::class,
                        'choice_label'  => 'nom',
                        'placeholder'   => '-- Séléctionnez --',
                        'expanded'      => false,
                        'required'      => false,
                        'data'          => ($data===true) && ($em = $options['em']) ? $em->getReference(Parcours::class, $parcoursId) : null
                    ]
                )*/

            ->add(
                'ue',
                EntityType::class,
                [
                    'class'             => UniteEnseignements::class,
                    'choice_label'      => 'libelle',
                    'placeholder'       => '-- Séléctionnez --',
                    'expanded'          => false,
                    'data'              => ($em = $options['em']) ? $em->getReference(UniteEnseignements::class, $ueId) : null
                ]
            )
            ->add(
                'matiere',
                EntityType::class,
                [
                    'class'             => Matiere::class,
                    'choice_label'      => 'nom',
                    'placeholder'       => '-- Séléctionnez --',
                    'expanded'          => false,
                    'data'              => ($em = $options['em']) ? $em->getReference(Matiere::class, $matiereId) : null
                ]
            )
            ->add(
                'enseignant',
                HiddenType::class
            )
            ->add(
                'date',
                DateType::class,
                [
                    'widget' => 'single_text'
                ]
            )
            ->add(
                'theme',
                TextareaType::class, [
                    'attr'  => [
                        'rows'  =>4,
                        'cols'  =>50
                    ],
                ]
            )
            ->add(
                'startTime',
                TimeType::class,
                [
                    'widget' => 'single_text'
                ]
            )
            ->add(
                'endTime',
                TimeType::class,
                [
                    'widget' => 'single_text'
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
            'data_class'                => FichePresenceEnseignant::class,
            'allow_extra_fields'        => true,
            'fichePresenceEnseignant'   => [],
            'em'                        => [],
        ]);
    }
}
