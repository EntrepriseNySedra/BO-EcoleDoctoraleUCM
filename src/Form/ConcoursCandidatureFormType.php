<?php

namespace App\Form;

use App\Entity\AnneeUniversitaire;
use App\Entity\ConcoursCandidature;
use App\Entity\ConcoursCentre;
use App\Entity\Mention;
use App\Entity\Niveau;
use App\Entity\Parcours;
use App\Services\Form\EntityTypeTools;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Doctrine\ORM\EntityRepository;

class ConcoursCandidatureFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builderOptions = $options['candidature'];
        $niveauId       = $builderOptions instanceof ConcoursCandidature ? $builderOptions->getNiveau()->getId() : null;
        $mentionId      = $builderOptions instanceof ConcoursCandidature ? $builderOptions->getMention()->getId() : null;
        $parcoursId     = $builderOptions instanceof ConcoursCandidature ? $builderOptions->getParcours()->getId() : null;
        $centreId       = $builderOptions instanceof ConcoursCandidature ? $builderOptions->getCentre()->getId() : null;
        $anneeUnivId    = $builderOptions instanceof ConcoursCandidature ? $builderOptions->getAnneeUniversitaire()->getId() : null;

        $builder
            ->add(
                'civility',
                ChoiceType::class,
                [
                    'label'      => 'Civilité',
                    'placeholder'=> ' -- Séléctionnez -- ',
                    'choices'    => ConcoursCandidature::$civilityList,
                    'expanded'   => false,
                    'required' => false
                ]
            )
            ->add('firstName',
                TextType::class,
                [
                    'required' => true
                ]
            )
            ->add('lastName',
                TextType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'dateOfBirth',
                TextType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'birthPlace',
                TextType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'diplome',
                TextType::class
            )
            ->add(
                'niveau',
                EntityType::class,
                [
                    'class'        => Niveau::class,
                    'choice_label' => 'libelle',
                    'placeholder'  => '-- Niveau --',
                    'data'         => ($em = $options['em']) ? $em->getReference(Niveau::class, $niveauId) : null,
                    'required'     => true
                ]
            )
            ->add(
                'mention',
                EntityType::class,
                [
                    'class'        => Mention::class,
                    'choice_label' => 'nom',
                    'placeholder'  => '-- Mention --',
                    'data'         => ($em = $options['em']) ? $em->getReference(Mention::class, $mentionId) : null,
                    'required'     => true
                ]
            )
            ->add(
                'parcours',
                EntityType::class,
                [
                    'class'        => Parcours::class,
                    'choice_label' => 'nom',
                    'placeholder'  => '-- Parcours --',
                    'data'         => ($em = $options['em']) ? $em->getReference(Parcours::class, $parcoursId) : null,
                    'required'     => false
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
                        // dump($currentYear);die;
                        $qb = $er->createQueryBuilder('au')
                                ->andWhere('au.annee >= :pAnnee')
                                ->setParameter('pAnnee', $currentYear);
                        return $qb;
                    },
                    'choice_label' => 'libelle',
                    'placeholder'  => '-- Année universitaire --',
                    'data'         => ($em = $options['em']) ? $em->getReference(AnneeUniversitaire::class, $anneeUnivId) : null,
                    'required'     => true
                ]
            )
            ->add(
                'centre',
                EntityType::class,
                [
                    'class'        => ConcoursCentre::class,
                    'placeholder'  => '-- Centre d\'examen --',
                    'choice_label' => 'name',
                    'data'         => ($em = $options['em']) ? $em->getReference(ConcoursCentre::class, $centreId) : null,
                    'required'     => true
                ]
            )

            ->add(
                'address',
                TextType::class,
                [
                    'required'     => false
                ]
            )
             ->add(
                'email',
                EmailType::class
            )
             ->add(
                'phone1',
                TextType::class,
                [
                    'required'     => false
                ]
            )
             ->add(
                'phone2',
                TextType::class,
                [
                    'required'  => false
                ]
            )
             ->add(
                'phone3',
                TextType::class,
                [
                    'required'  => false
                ]
            )
             ->add(
                'religion',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'job',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'status',
                ChoiceType::class,
                [
                    'label'      => 'Statut',
                    'choices'    => ConcoursCandidature::$statusList,
                    'expanded'   => false,
                    'required'   => false
                ]
            )
            ->add(
                'gender',
                ChoiceType::class,
                [
                    'label'     => 'Sexe',
                    'placeholder'  => '-- Sexe --',
                    'choices'   => ConcoursCandidature::$genderList,
                    'required'  => false,
                    'expanded'  => false
                ]
            )
            ->add(
                'lang',
                ChoiceType::class,
                [
                    'placeholder'   => '-- Langue --',
                    'choices'       => ConcoursCandidature::$langList,
                    'required'      => false,
                    'expanded'      => false
                ]
            )
            ->add(
                'nationality',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'cinNum',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'cinDeliverDate',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'cinDeliverLocation',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'diplomePath',
                FileType::class,
                [
                    'required'  => false,
                    'label'     => false
                ]
            )
            ->add(
                'cv',
                FileType::class,
                [
                    'required'  => false,
                    'label'     => false
                ]
            )
            ->add(
                'payementRef',
                TextType::class,
                [
                    'required'  => true,
                    'label'     => false
                ]
            )
            ->add(
                'payementRefPath',
                FileType::class,
                [
                    'required'  => true,
                    'label'     => false
                ]
            )
            ->add(
                'confessionReligieuse',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'fatherFirstName',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'fatherLastName',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'photo',
                FileType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'fatherJob',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'fatherJobAddress',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'fatherJobPhone',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'motherFirstName',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'motherLastName',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'motherJob',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'motherJobAddress',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'motherJobPhone',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'conjointFirstName',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'conjointLastName',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'conjointJob',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'conjointJobAddress',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'conjointJobPhone',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'tuteurCivility',
                ChoiceType::class,
                [
                    'label'      => 'Civilité',
                    'placeholder'=> ' -- Séléctionnez -- ',
                    'choices'    => ConcoursCandidature::$civilityList,
                    'expanded'   => false,
                    'required'   => false
                ]
            )
            ->add(
                'tuteurFirstName',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'tuteurLastName',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'tuteurJob',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'tuteurJobAddress',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'tuteurJobPhone',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'religiousProfil',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'religiousCongregationName',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'religiousAddress',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'religiousResponsableFoyerName',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'religiousPhone',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'religiousEmail',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'baccAutreName',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'baccSerie',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'baccAutreSerie',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'baccMention',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'baccSession',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'baccAnnee',
                NumberType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'baccNumInscription',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'originEtablissement',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'universityName',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'faculteName',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'universityCountry',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'universityDiplome',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'universityDiplomeDate',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'situationMatrimoniale',
                TextType::class,
                [
                    'required'  => false
                ]
            )
            ->add(
                'payement_ref_date',
                TextType::class
            )
        ;

        $builder
            ->get('dateOfBirth')
            ->addModelTransformer(
                new CallbackTransformer(
                    function ($dateOfBirthAsDateTime)
                    {
                        return $dateOfBirthAsDateTime;
                    },
                    function ($dateOfBirthAsString)
                    {
                        $dateOfBirth = \DateTime::createFromFormat('d/m/Y', $dateOfBirthAsString);
                        return !$dateOfBirth ? null : $dateOfBirth ;
                    }
                )
            )
        ;

        $builder
            ->get('cinDeliverDate')
            ->addModelTransformer(
                new CallbackTransformer(
                    function ($cinDateDeliverDateTime) {
                        return $cinDateDeliverDateTime;
                    },
                    function ($cinDateDeliverAsString) {
                        $cinDeliverDate = \DateTime::createFromFormat('d/m/Y', $cinDateDeliverAsString);
                        return !$cinDeliverDate ? null : $cinDeliverDate ;
                    }
                )
            )
        ;

        $builder
            ->get('universityDiplomeDate')
            ->addModelTransformer(
                new CallbackTransformer(
                    function ($universityDiplomeDateTime) {
                        return $universityDiplomeDateTime;
                    },
                    function ($universityDiplomeAsString) {
                        $universityDiplomeDate = \DateTime::createFromFormat('d/m/Y', $universityDiplomeAsString);
                        return !$universityDiplomeDate ? null : $universityDiplomeDate ;
                    }
                )
            )
        ;

        $builder
            ->get('payement_ref_date')
            ->addModelTransformer(
                new CallbackTransformer(
                    function ($payementRefDateTime) {
                        return $payementRefDateTime;
                    },
                    function ($payementRefAsString) {
                        $payementRefDate = \DateTime::createFromFormat('d/m/Y', $payementRefAsString);
                        return !$payementRefDate ? null : $payementRefDate ;
                    }
                )
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'  => ConcoursCandidature::class,
                'em'          => [],
                'candidature' => [],
            ]
        );
    }
}
