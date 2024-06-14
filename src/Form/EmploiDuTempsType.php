<?php

namespace App\Form;

use App\Entity\EmploiDuTemps;
use App\Entity\Enseignant;

use App\Entity\Niveau;
use App\Entity\Parcours;
use App\Entity\Semestre;
use App\Entity\UniteEnseignements;
use App\Entity\Matiere;
use App\Entity\Salles;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmploiDuTempsType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $emploiDuTempsOptions = $options['emploiDuTemps'];
        $salleId       = $emploiDuTempsOptions instanceof EmploiDuTemps ? $emploiDuTempsOptions->getSalles()->getId() : null;
        $matiereId     = $emploiDuTempsOptions instanceof EmploiDuTemps ? $emploiDuTempsOptions->getMatiere()->getId() : null;

        $builder
            ->add('dateSchedule', DateType::class, [
                'widget' => 'single_text'
            ])
            ->add('startTime', TimeType::class, [
                'widget' => 'single_text'
            ])
            ->add('endTime', TimeType::class, [
                'widget' => 'single_text'
            ])
            ->add('connexion', CheckboxType::class, [
                'label'    => 'Connexion ',
                'required' => false,
            ])
            ->add('videoprojecteur', CheckboxType::class, [
                'label'    => 'Vidéo projecteur ',
                'required' => false,
            ])
             ->add('troncCommun', CheckboxType::class, [
                'label'    => 'Tronc commun',
                'required' => false,
            ])
            ->add(
                'salles',
                EntityType::class,
                [
                    'class'        => Salles::class,
                    'choice_label' => 'libelle',
                    'placeholder'  => '-- Salles --',
                    'data'         => ($em = $options['em']) ? $em->getReference(Salles::class, $salleId) : null
                ]
            )
            ->add(
                'niveau',
                EntityType::class,
                [
                    'class' => Niveau::class,
                    'choice_label'     => 'libelle',
                    'placeholder'  => '-- Séléctionnez --',
                    'expanded'  => false
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
            ->add(
                'semestre',
                EntityType::class,
                [
                    'class' => Semestre::class,
                    'choice_label'     => 'libelle',
                    'placeholder'  => '-- Séléctionnez --',
                    'expanded'  => false,
                    'required'  => false
                ]
            )
            ->add(
                'ue',
                EntityType::class,
                [
                    'class' => UniteEnseignements::class,
                    'choice_label'     => 'libelle',
                    'placeholder'  => '-- Séléctionnez --',
                    'expanded'  => false
                ]
            )
            ->add(
                'matiere',
                EntityType::class,
                [
                    'class'        => Matiere::class,
                    'choice_label' => 'nom',
                    'placeholder'  => '-- Matière --'
                ]
            )
            ->add(
                'salles',
                EntityType::class,
                [
                    'class' => Salles::class,
                    'choice_label'     => 'libelle',
                    'placeholder'  => '-- Séléctionnez --',
                    'expanded'  => false
                ]
            )
            ->add(
                'description',
                TextareaType::class,
                [
                    'required' => false,
                    'label'    => false,
                    'mapped'   => false
                ]
            )
            ->add(
                'descriptionPath',
                FileType::class,
                [
                    'required' => false,
                    'label'    => false,
                    'mapped'   => false
                ]
            )
            ->add(
                'commentaire',
                TextareaType::class,
                [
                    'required' => false,
                    'label'    => false,
                    'mapped'   => false
                ]
            )
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'        => EmploiDuTemps::class,
            'emploiDuTemps'     => [],
            'em'                => [],
        ]);
    }
}
