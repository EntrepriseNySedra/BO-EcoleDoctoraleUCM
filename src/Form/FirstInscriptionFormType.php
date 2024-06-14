<?php

namespace App\Form;

use App\Entity\Civility;
use App\Entity\ConcoursCandidature;
use App\Entity\Etudiant;
use App\Entity\FraisScolarite;
use App\Entity\Mention;
use App\Entity\Niveau;
use App\Entity\Parcours;
use App\Services\Form\EntityTypeTools;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class FirstInscriptionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builderOptions  = $options['candidate'];
        $niveauId        = $builderOptions instanceof ConcoursCandidature ? $builderOptions->getNiveau()->getId() : null;
        $mentionId       = $builderOptions instanceof ConcoursCandidature ? $builderOptions->getMention()->getId() : null;
        $parcoursId      = $builderOptions instanceof ConcoursCandidature && $builderOptions->getParcours() ? $builderOptions->getParcours()->getId() : null;
        // $civilityId      = $builderOptions instanceof ConcoursCandidature && $builderOptions->getCivility() ? $builderOptions->getCivility()->getId() : null;
        // $tutorCivilityId = $builderOptions instanceof ConcoursCandidature ? $builderOptions->getTutorCivility()->getId() : null;
        // $candidate = $options['candidate'];

        $builder
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
            ->add(
                'immatricule',
                TextType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'civility',
                EntityType::class,
                [
                    'label'        => 'Civilité',
                    'class'        => Civility::class,
                    'choice_label' => 'libelle',
                    'placeholder'  => '-- Séléctionner --',
                    // 'data'         => ($em = $options['em']) ? $em->getReference(Civility::class, $civilityId) : null,
                    'required'     => true
                ]
            )
            ->add(
                'firstName',
                TextType::class,
                [
                    'required' => false,
                    'data' => $builderOptions->getFirstName()
                ]
            )
            ->add(
                'lastName',
                TextType::class,
                [
                    'data' => $builderOptions->getLastName()
                ]
            )
            ->add(
                'birthDate',
                TextType::class,
                [
                    'data' => $builderOptions->getDateOfBirth() ? date_format($builderOptions->getDateOfBirth(), 'd/m/Y') : null
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
                    'placeholder'  => '-- Mention --',
                    'data'         => ($em = $options['em']) && $parcoursId ? $em->getReference(Parcours::class, $parcoursId) : null,
                    'required'     => true
                ]
            )
            ->add(
                'address',
                TextType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'email',
                EmailType::class,
                [
                    'required' => true,
                    'data' => $builderOptions->getEmail()
                ]
            )
            ->add(
                'phone',
                TextType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'nationality',
                TextType::class,
                [
                    'required' => true,
                    'data' => $builderOptions->getNationality()
                ]
            )
            // cin
            ->add(
                'cinNum',
                TextType::class,
                [
                    'required' => false,
                    
                ]
            )

            ->add(
                'cinDeliveryDate',
                TextType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'cinDeliveryLocation',
                TextType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'cv',
                FileType::class,
                [
                    'required' => false,
                    'label'    => false
                ]
            )
            // religion
            ->add(
                'religion',
                TextType::class,
                [
                    'required' => true,
                    'data' => $builderOptions->getConfessionReligieuse()
                ]
            )
            ->add(
                'sport',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'situationMatrimoniale',
                ChoiceType::class,
                [
                    'label_attr'  => [
                        'class' => 'radio-inline'
                    ],
                    'choices'     => Etudiant::$maritalStatus,
                    'expanded'    => true, // use a radio list instead of a select input
                    'required'    => false,
                    'placeholder' => false
                ]
            )
            // pieces fournies
            ->add(
                'lettreMotivation',
                FileType::class,
                [
                    'required' => false,
                    'label'    => false
                ]
            )
            ->add(
                'lettrePresentation',
                FileType::class,
                [
                    'required' => false,
                    'label'    => false
                ]
            )
            ->add(
                'photo1',
                FileType::class,
                [
                    'required' => false,
                    'label'    => false
                ]
            )
            ->add(
                'photo2',
                FileType::class,
                [
                    'required' => false,
                    'label'    => false
                ]
            )
            ->add(
                'certMedical',
                FileType::class,
                [
                    'required' => false,
                    'label'    => false
                ]
            )
            ->add(
                'acteNaissance',
                FileType::class,
                [
                    'required' => false,
                    'label'    => false
                ]
            )
            ->add(
                'baccFile',
                FileType::class,
                [
                    'required' => false,
                    'label'    => false
                ]
            )
            ->add(
                'baccSerie',
                ChoiceType::class,
                [
                    'label_attr'  => [
                        'class' => 'radio-inline'
                    ],
                    'choices'     => Etudiant::$baccSeries,
                    'expanded'    => true, // use a radio list instead of a select input
                    'required'    => false,
                    'placeholder' => false
                ]
            )
            ->add(
                'baccMention',
                TextType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'bacc_annee',
                TextType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'baccNuminscription',
                TextType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'cinFile',
                FileType::class,
                [
                    'required' => false,
                    'label'    => false
                ]
            )
            ->add(
                'autreDocLibelle',
                TextType::class,
                [
                    'required' => false,
                    'label'    => false
                ]
            )
            ->add(
                'autreDocFichier',
                FileType::class,
                [
                    'required' => false,
                    'label'    => false
                ]
            )
            // Filiation
            ->add(
                'fatherName',
                TextType::class,
                [
                    'required' => true,
                    'label'    => false
                ]
            )
            ->add(
                'fatherAddressContact',
                TextType::class,
                [
                    'required' => true,
                    'label'    => false
                ]
            )
            ->add(
                'fatherJob',
                TextType::class,
                [
                    'required' => false,
                    'label'    => false
                ]
            )
            ->add(
                'fatherJobAddressContact',
                TextType::class,
                [
                    'required' => false,
                    'label'    => false
                ]
            )
            ->add(
                'motherName',
                TextType::class,
                [
                    'required' => true,
                    'label'    => false
                ]
            )
            ->add(
                'motherAddressContact',
                TextType::class,
                [
                    'required' => true,
                    'label'    => false
                ]
            )
            ->add(
                'motherJob',
                TextType::class,
                [
                    'required' => false,
                    'label'    => false
                ]
            )
            ->add(
                'motherJobAddressContact',
                TextType::class,
                [
                    'required' => false,
                    'label'    => false
                ]
            )
            ->add(
                'tutorCivility',
                EntityType::class,
                [
                    'label'        => 'Civilité',
                    'class'        => Civility::class,
                    'choice_label' => 'libelle',
                    'placeholder'  => '-- Séléctionner --',
                    // 'data'         => ($em = $options['em']) ? $em->getReference(
                    //     Civility::class, $tutorCivilityId
                    // ) : null,
                    'required' => false
                ]
            )
            ->add(
                'tutorFirstName',
                TextType::class,
                [
                    'required' => false,
                    'label'    => false
                ]
            )
            ->add(
                'tutorLastName',
                TextType::class,
                [
                    'required' => false,
                    'label'    => false
                ]
            )
            ->add(
                'tutorAddressContact',
                TextType::class,
                [
                    'required' => false,
                    'label'    => false
                ]
            )
            ->add(
                'tutorJob',
                TextType::class,
                [
                    'required' => false,
                    'label'    => false
                ]
            )
            ->add(
                'tutorJobAddressContact',
                TextType::class,
                [
                    'required' => false,
                    'label'    => false
                ]
            )
            ->add(
                'jointFirstName',
                TextType::class,
                [
                    'required' => false,
                    'label'    => false
                ]
            )
            ->add(
                'jointLastName',
                TextType::class,
                [
                    'required' => false,
                    'label'    => false
                ]
            )
            ->add(
                'jointAddressContact',
                TextType::class,
                [
                    'required' => false,
                    'label'    => false
                ]
            )
            ->add(
                'jointJob',
                TextType::class,
                [
                    'required' => false,
                    'label'    => false
                ]
            )
            ->add(
                'jointJobAddressContact',
                TextType::class,
                [
                    'required' => false,
                    'label'    => false
                ]
            )
            ->add(
                'religiousCongregationName',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'religiousAddress',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'religiousResponsibleName',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'religiousPhone',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'religiousEmail',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'religiousProfil',
                ChoiceType::class,
                [
                    'label_attr'  => [
                        'class' => 'radio-inline'
                    ],
                    'choices'     => Etudiant::$religiousProfilOptions,
                    'expanded'    => true, // use a radio list instead of a select input
                    'required'    => false,
                    'placeholder' => false
                ]
            )
            ->add(
                'passeportNum',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'passportDeliveryDate',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'passportDeliveryPlace',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'residenceTitle',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'firstCycleEtablissement',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'firstCycleDiplome',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'firstCycleYear',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'secondCycleEtablissement1',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'secondCycleDiplome1',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'secondCycleYear1',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'secondCycleEtablissement2',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'secondCycleDiplome2',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'secondCycleYear2',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'otherFormationEtab1',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'otherFormationEtab2',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'otherFormationEtab3',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'otherFormationDiplome1',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'otherFormationDiplome2',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'otherFormationDiplome3',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'otherFormationYear1',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'otherFormationYear2',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'otherFormationYear3',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'otherLevelEtab1',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'otherLevelEtab2',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'otherLevelEtab3',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'otherLevelDiplome1',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'otherLevelDiplome2',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'otherLevelDiplome3',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'otherLevelYear1',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'otherLevelYear2',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'otherLevelYear3',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'otherStageEtab1',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'otherStageEtab2',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'otherStageEtab3',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'otherStageDiplome1',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'otherStageDiplome2',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'otherStageDiplome3',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'otherStageYear1',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'payementRef',
                TextType::class,
                [
                    'required'  => true,
                    'label'     => false,
                    'mapped'    => false
                ]
            )
            ->add(
                'payementRefPath',
                FileType::class,
                [
                    'required'  => true,
                    'label'     => false,
                    'mapped'    => false
                ]
            )
            ->add(
                'payementRefDate',
                TextType::class,
                [
                    'required'  => false,
                    'mapped'    => false,
                    'constraints'       => [
                        new Assert\NotNull([
                            'message' => 'Veuillez saisir la date  de paiement'
                        ])
                    ]
                ]
            )
            ->add(
                'otherStageYear2',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'otherStageYear3',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'masterCondition',
                CheckboxType::class,
                [
                    'label' => "J'ai lu et j'accepte la procédure d'entrée en Master à l'UCM",
                    'required' => true,
                    'mapped' => false
                ]
            )
        ;

        $builder
            ->get('birthDate')
            ->addModelTransformer(
                new CallbackTransformer(
                    function ($dateOfBirthAsDateTime)
                    {
                        return $dateOfBirthAsDateTime;
                    },
                    function ($dateOfBirthAsString)
                    {
                        $dateOfBirth = \DateTime::createFromFormat('d/m/Y', $dateOfBirthAsString);

                        return !$dateOfBirth ? null : $dateOfBirth;
                    }
                )
            )
        ;

        $builder
            ->get('bacc_annee')
            ->addModelTransformer(
                new CallbackTransformer(
                    function ($baccAnneeInt)
                    {
                        return $baccAnneeInt;
                    },
                    function ($baccAnneeString)
                    {
                        return $baccAnneeString ? intval($baccAnneeString) : null ;
                    }
                )
            )
        ;

        $builder
            ->get('cinDeliveryDate')
            ->addModelTransformer(
                new CallbackTransformer(
                    function ($cinDateDeliverDateTime)
                    {
                        return $cinDateDeliverDateTime;
                    },
                    function ($cinDateDeliverAsString)
                    {
                        $cinDeliverDate = \DateTime::createFromFormat('d/m/Y', $cinDateDeliverAsString);

                        return !$cinDeliverDate ? null : $cinDeliverDate;
                    }
                )
            )
        ;

        $builder
            ->get('passportDeliveryDate')
            ->addModelTransformer(
                new CallbackTransformer(
                    function ($passportDeliveryDateTime)
                    {
                        return $passportDeliveryDateTime;
                    },
                    function ($passportDeliveryDateAsString)
                    {
                        $passportDeliveryDate = \DateTime::createFromFormat('d/m/Y', $passportDeliveryDateAsString);

                        return !$passportDeliveryDate ? null : $passportDeliveryDate;
                    }
                )
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
                'data_class' => Etudiant::class,
                'etudiant'   => [],
                'em'         => [],
                'candidate'  => []
            ]
        );
    }
}
