<?php

namespace App\Form;

use App\Entity\Civility;
use App\Entity\Etudiant;
use App\Entity\Mention;
use App\Entity\Niveau;
use App\Entity\Parcours;
use App\Services\Form\EntityTypeTools;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints\Length;

class EtudiantInfoFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $etudiant  = $options['data'];
        $builder

            ->add(
                'login',
                TextType::class,
                [
                    'required' => false,
                    'mapped' => false,
                    'data' => $etudiant && $etudiant->getUser() ? $etudiant->getUser()->getLogin() : ''
                ]
            )
            ->add(
                'password',
                PasswordType::class,
                [
                    // instead of being set onto the object directly,
                    // this is read and encoded in the controller
                    'mapped'      => false,
                    'required'    => false,
                    'constraints' => [
                        new Length(
                            [
                                'min'        => 6,
                                'minMessage' => 'Your password should be at least {{ limit }} characters',
                                // max length allowed by Symfony for security reasons
                                'max'        => 4096,
                            ]
                        ),
                    ],
                ]
            )
            ->add(
                'immatricule',
                TextType::class,
                [
                    'data' => $etudiant->getImmatricule()
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
                    'data'         => ($em = $options['em']) && $etudiant->getCivility() ? $em->getReference(Civility::class, $etudiant->getCivility()->getId()) : null,
                    'required'     => true
                ]
            )
            ->add(
                'mention',
                EntityType::class,
                [
                    'label'        => 'Mention',
                    'class'        => Mention::class,
                    'choice_label' => 'nom',
                    'placeholder'  => '-- Séléctionner --',
                    'data'         => ($em = $options['em']) && $etudiant->getMention() ? $em->getReference(Mention::class, $etudiant->getMention()->getId()) : null,
                    'required'     => false
                ]
            )
            ->add(
                'niveau',
                EntityType::class,
                [
                    'label'        => 'Niveau',
                    'class'        => Niveau::class,
                    'choice_label' => 'libelle',
                    'placeholder'  => '-- Séléctionner --',
                    'data'         => ($em = $options['em']) && $etudiant->getNiveau() ? $em->getReference(Niveau::class, $etudiant->getNiveau()->getId()) : null,
                    'required'     => false
                ]
            )
            ->add(
                'parcours',
                EntityType::class,
                [
                    'label'        => 'Parcours',
                    'class'        => Parcours::class,
                    'choice_label' => 'nom',
                    'placeholder'  => '-- Séléctionner --',
                    'data'         => ($em = $options['em']) && $etudiant->getParcours() ? $em->getReference(Parcours::class, $etudiant->getParcours()->getId()) : null,
                    'required'     => false
                ]
            )
            ->add(
                'firstName',
                TextType::class,
                [
                    'required' => false,
                    'data' => $etudiant->getFirstName()
                ]
            )
            ->add(
                'lastName',
                TextType::class,
                [
                    'data' => $etudiant->getLastName()
                ]
            )
            // ->add(
            //     'birthDate',
            //     TextType::class,
            //     [
            //         'data' => $etudiant->getBirthDate() ? date_format($etudiant->getBirthDate(), 'd/m/Y') : null
            //     ]
            // )

            ->add('birthDate', DateType::class, [
                'widget' => 'single_text'
            ])

            ->add(
                'birthPlace',
                TextType::class,
                [
                    'required' => false
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
                    'data' => $etudiant->getEmail()
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
                    'data' => $etudiant->getNationality()
                ]
            )
            // cin
            ->add(
                'cinNum',
                TextType::class,
                [
                    'required' => false
                ]
            )
            // ->add(
            //     'cinDeliveryDate',
            //     TextType::class,
            //     [
            //         'required' => false
            //     ]
            // )
            ->add('cinDeliveryDate', DateType::class, [
                'widget' => 'single_text'
            ])
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
                    'label'    => false,
                    'data_class' => null
                ]
            )
            // religion
            ->add(
                'religion',
                TextType::class,
                [
                    'required' => true,
                    'data' => $etudiant->getReligion()
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
                    'label'    => false,
                    'data_class' => null
                ]
            )
            ->add(
                'photo1',
                FileType::class,
                [
                    'required' => false,
                    'label'    => false,
                    'data_class' => null
                ]
            )
            ->add(
                'photo2',
                FileType::class,
                [
                    'required' => false,
                    'label'    => false,
                    'data_class' => null
                ]
            )
            ->add(
                'acteNaissance',
                FileType::class,
                [
                    'required' => false,
                    'label'    => false,
                    'data_class' => null
                ]
            )
            ->add(
                'baccFile',
                FileType::class,
                [
                    'required' => false,
                    'label'    => false,
                    'data_class' => null
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
                    'label'    => false,
                    'data_class' => null
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
                    'label'    => false,
                    'data_class' => null
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
            // ->add(
            //     'passportDeliveryDate',
            //     TextType::class,
            //     [
            //         'required' => false,
            //     ]
            // )
            ->add('passportDeliveryDate', DateType::class, [
                'widget' => 'single_text'
            ])
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

        // $builder
        //     ->get('birthDate')
        //     ->addModelTransformer(
        //         new CallbackTransformer(
        //             function ($dateOfBirthAsDateTime)
        //             {

        //                 return $dateOfBirthAsDateTime;
        //             },
        //             function ($dateOfBirthAsString)
        //             {
        //                 $dateOfBirth = \DateTime::createFromFormat('d/m/Y', $dateOfBirthAsString);

        //                 return !$dateOfBirth ? null : $dateOfBirth;
        //             }
        //         )
        //     )
        // ;

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

        // $builder
        //     ->get('cinDeliveryDate')
        //     ->addModelTransformer(
        //         new CallbackTransformer(
        //             function ($cinDateDeliverDateTime)
        //             {
        //                 return $cinDateDeliverDateTime;
        //             },
        //             function ($cinDateDeliverAsString)
        //             {
        //                 $cinDeliverDate = \DateTime::createFromFormat('d/m/Y', $cinDateDeliverAsString);

        //                 return !$cinDeliverDate ? null : $cinDeliverDate;
        //             }
        //         )
        //     )
        // ;

        // $builder
        //     ->get('passportDeliveryDate')
        //     ->addModelTransformer(
        //         new CallbackTransformer(
        //             function ($passportDeliveryDateTime)
        //             {
        //                 return $passportDeliveryDateTime;
        //             },
        //             function ($passportDeliveryDateAsString)
        //             {
        //                 $passportDeliveryDate = \DateTime::createFromFormat('d/m/Y', $passportDeliveryDateAsString);

        //                 return !$passportDeliveryDate ? null : $passportDeliveryDate;
        //             }
        //         )
        //     )
        // ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Etudiant::class,
                'em'         => []
            ]
        );
    }
}
