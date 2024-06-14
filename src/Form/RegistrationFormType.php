<?php

namespace App\Form;

use App\Entity\Profil;
use App\Entity\Mention;
use App\Entity\User;
use App\Entity\Departement;
use App\Services\Form\EntityTypeTools;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $options['data'];
        $mappedEmploye = true;
        switch ($user->getProfil()) {
            case Profil::ETUDIANT:
            case Profil::ENSEIGNANT:
                $mappedEmploye = false;
                break;            
            default:
                $mappedEmploye = true;
                break;
        }
        $userOptions = $options['user'];
        $profilId    = $userOptions instanceof User ? $userOptions->getProfil()->getId() : null;
        $mentionId   = $userOptions instanceof User &&  $userOptions->getMention() ? $userOptions->getMention()->getId() : null;
        $readonly    = $userOptions instanceof User ? true : false;
        $faculteId   = $userOptions instanceof User && $userOptions->getFaculte() ? $userOptions->getFaculte()->getId() : null;

        // if(!$mappedEmploye){
        //     $builder
        //         ->add(
        //             'email',
        //             EmailType::class,
        //             [
        //                 'required' => false
        //             ]
        //         )
        //         ->add(
        //             'firstName',
        //             TextType::class
        //         )
        //         ->add(
        //             'lastName',
        //             TextType::class
        //         );
        // } else {
        //     $builder->add(
        //         'employe', 
        //         EmployeFormType::class
        //     );
        // }


        $builder
            ->add(
                'login',
                TextType::class,
                [
                    'attr' => ['readonly' => $readonly],
                    'required' => true
                ]
            )
            ->add(
                'bank_num',
                TextType::class,
                [
                    'required' => false
                ] 
            )
            ->add(
                'bank_name',
                TextType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'plainPassword',
                PasswordType::class,
                [
                    // instead of being set onto the object directly,
                    // this is read and encoded in the controller
                    'mapped'      => false,
                    'required'    => true,
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
                'profil',
                EntityType::class,
                [
                    'class'        => Profil::class,
                    'choice_label' => 'name',
                    'placeholder'  => '-- Profil --',
                    'data'         => $user->getProfil()
                ]
            )
            ->add(
                'status',
                ChoiceType::class,
                [
                    'label'      => 'Statut',
                    'choices'    => User::$statusList,
                    'required'   => true,
                    'expanded'   => false
                ]
            )
            ->add(
                'mention',
                EntityType::class,
                [
                    'class'        => Mention::class,
                    'choice_label' => 'nom',
                    'placeholder'  => '-- Mention --',
                    'data'         => ($em = $options['em']) && $mentionId ? $em->getReference(Mention::class, $mentionId) : null,
                    'required'   => false,
                ]
            )
            ->add(
                'faculte',
                EntityType::class,
                [
                    'class'        => Departement::class,
                    'choice_label' => 'nom',
                    'placeholder'  => '-- Facultés --',
                    'data'         => ($em = $options['em']) && $faculteId ? $em->getReference(Departement::class, $faculteId) : null,
                    'required'   => false,
                ]
            )
            ->add(
                'bankNum',
                TextType::class,
                [
                    'required'   => false
                ]
            )
            ->add(
                'tiersNum',
                TextType::class,
                [
                    'required'   => false
                ]
            );



        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function(FormEvent $event) use ($user) {
                $this->addExtraFields($event->getForm(), $user->getProfil());
            }
        );
        // $builder->addEventListener(
        //     FormEvents::POST_SUBMIT,
        //     function(FormEvent $event) use ($mappedEmploye) {
        //         $this->addExtraFields($event->getForm(), $user->getProfil());
        //     }
        // );
    }

    private function addExtraFields(FormInterface $form, $profil) {
        if(!$profil) {
            $form->add(
                    'employe',
                    EmployeFormType::class,
                    [
                        'required' => false
                    ]
                );
            return true;
        }
        switch($profil->getName()) {
            case Profil::ETUDIANT:
            case Profil::ENSEIGNANT:
            $form->add(
                    'email',
                    EmailType::class,
                    [
                        'required' => false
                    ]
                )
                ->add(
                    'firstName',
                    TextType::class,
                    [
                        'required' => false
                    ]
                )
                ->add(
                    'lastName',
                    TextType::class,
                    [
                        'required' => false
                    ]
                );
                break;
            case Profil::SURVEILLANT:
            default:
                $form->add(
                    'employe',
                    EmployeFormType::class,
                    [
                        'required' => false
                    ]
                );
                break;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => User::class,
                'user'       => [],
                'em'         => [],
            ]
        );
    }
}
