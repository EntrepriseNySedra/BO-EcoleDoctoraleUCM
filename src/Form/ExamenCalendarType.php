<?php

namespace App\Form;

use App\Entity\CalendrierExamen;
use App\Entity\Departement;
use App\Entity\Etudiant;
use App\Entity\Matiere;
use App\Entity\Mention;
use App\Entity\Niveau;
use App\Entity\Parcours;
use App\Entity\Profil;
use App\Entity\Semestre;
use App\Entity\UniteEnseignements;
use App\Entity\User;

use App\Form\CalExamenSurvType;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of ExamenCalendarType.php.
 *
 * @package App\Form
 */
class ExamenCalendarType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \App\Entity\User $user */
        $user   = $options['user'];
        $em   = $options['em'];
        $surveillants = $options['data']->getCalendrierExamenSurveillances()->toArray();
        $mention = $user->getMention();
        $userDepart = $mention->getDepartement();
        $departMentions = $userDepart->getMentions();
        $status = CalendrierExamen::$onlyCreatedStatus;
        if ($user && in_array('ROLE_CHEFMENTION', $user->getRoles())) {
            $status = CalendrierExamen::$cmStatusList;
        }
        //dump($status);die;
        $builder
            ->add(
                'libelle',
                TextType::class,
                [
                    'required' => true
                ]
            )
            ->add(
                'dateSchedule',
                DateType::class,
                [
                    'widget' => 'single_text',
                    'required' => true,
                    'by_reference' => true
                ]
            )
            ->add(
                'startTime',
                TimeType::class,
                [
                    'widget' => 'single_text',
                    'required' => true,
                    'by_reference' => true
                ]
            )
            ->add(
                'endTime',
                TimeType::class,
                [
                    'widget' => 'single_text',
                    'required' => true,
                    'by_reference' => true
                ]
            )
            ->add(
                'departement',
                EntityType::class,
                [
                    'required' => true,
                    'class' => Departement::class,
                    'choices' => [$userDepart],
                    'choice_label' => 'nom'
                ]
            )
            ->add(
                'statut',
                TextType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'mention',
                EntityType::class,
                [
                    'required' => true,
                    'class' => Mention::class,
                    'choices' => [$mention],
                    'choice_label' => 'nom'
                ]
            )
            ->add(
                'niveau',
                EntityType::class,
                [
                    'required' => true,
                    'class' => Niveau::class,
                    'choice_label' => 'libelle',
                    'expanded' => false,
                    'placeholder' => '--- Séléctionner ---'
                ]
            )
            ->add(
                'surveillant',
                // EntityType::class,
                // [
                //     'required' => false,
                //     'class' => User::class,
                //     'expanded' => false,
                //     'placeholder' => '--- Séléctionner ---',
                //     'auto_initialize' => false,
                //     'query_builder' => function (EntityRepository $er)
                //     {
                //         $survProfileName = Profil::SURVEILLANT;
                //         $qb = $er->createQueryBuilder('u')
                //         ->select('u')
                //         ->join('u.profil', 'p')
                //         ->addSelect('p')
                //         ->where('p.name = :pProfil')
                //         ->setParameter('pProfil', $survProfileName);
                //         return $qb;
                //     },
                //     'choice_label' => function(User $user) {
                //         return $user->getFirstName(). ' '. $user->getLastName();
                //     },
                //     'multiple'      => true,
                //     'attr' => [
                //         'class' => 'js-example-basic-multiple'
                //     ]
                // ]
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

                        // $qb->andWhere('r.code NOT IN (:pExcludeRole)')
                        //     ->setParameter('pExcludeRole', $excludeRoles);                            
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
                    'mapped'        => false,
                    'required'      => true,
                    'multiple'      => true,
                    'attr' => [
                        'class' => 'js-example-basic-multiple'
                    ],
                    'data' => array_map(function($item){
                                    return $item->getSurveillant();
                                }, $surveillants)
                ]
            )
            // ->add(
            //     'calendrierExamenSurveillances',
            //     CollectionType::class,
            //     [
            //         'entry_type'   => CalExamenSurvType::class,
            //         'prototype'    => true,
            //         'allow_add'    => true,
            //         'allow_delete' => true,
            //         'by_reference' => false,
            //         'required'     => false,
            //         'label'        => false
            //     ]
            // )
        ;
        $semestreModifier = function(FormInterface $form, Niveau $niveau = null) {
            $semestres = null === $niveau ? [] : $niveau->getSemestres();
            $builder = $form->getConfig()->getFormFactory()->createNamedBuilder(
                'semestre',
                EntityType::class,
                null,
                [
                    'class'           => Semestre::class,
                    'placeholder'     => '--- Sélectionner ---',
                    'mapped'          => true,
                    'required'        => true,
                    'auto_initialize' => false,
                    'choices'         => $niveau ? $niveau->getSemestres() : [],
                ]
            );
            $builder->addEventListener(
                FormEvents::POST_SUBMIT,
                function (FormEvent $event)
                {
                    $form = $event->getForm();
                    $parentForm = $form->getParent();
                    $mention = $parentForm->get('mention')->getData();
                    $niveau = $parentForm->get('niveau')->getData();
                    $parcours = $parentForm->get('parcours')->getData();
                    $this->addUEField($form->getParent(), $form->getData(), $mention, $niveau, $parcours);
                }
            );
            $form->add($builder->getForm());
        };
        $parcoursModifier = function(FormInterface $form, Mention $mention = null, Niveau $niveau = null) {
            $builder = $form->getConfig()->getFormFactory()->createNamedBuilder(
                'parcours',
                EntityType::class,
                null,
                [
                    'class'           => Parcours::class,
                    'placeholder'     => '--- Sélectionner ---',
                    'mapped'          => true,
                    'required'        => false,
                    'auto_initialize' => false,
                    'query_builder' => function (EntityRepository $er) use ($mention, $niveau)
                    {
                        $qb = $er->createQueryBuilder('p')
                        ->where('p.mention = :pMention')
                        ->setParameter('pMention', $mention)
                        ->andWhere('p.niveau = :pNiveau')
                        ->setParameter('pNiveau', $niveau);
                        return $qb;
                    },
                    'choice_label' => function(Parcours $parcours) {
                        return $parcours->getNom();
                    }
                ]
            );
            $builder->addEventListener(
                FormEvents::POST_SUBMIT,
                function (FormEvent $event)
                {
                    $form = $event->getForm();
                    $parentForm = $form->getParent();
                    $parcours = $form->getData();
                    $mention = $parentForm->get('mention')->getData();
                    $niveau = $parentForm->get('niveau')->getData();
                    $semestre = $parentForm->get('semestre')->getData();
                    $this->addUEField($form->getParent(), $semestre, $mention, $niveau, $form->getData());
                }
            );
            $form->add($builder->getForm());
            // $form->add(
            //     'parcours',
            //     EntityType::class,
            //     [
            //         'class'           => Parcours::class,
            //         'placeholder'     => '--- Sélectionner ---',
            //         'mapped'          => true,
            //         'required'        => false,
            //         'auto_initialize' => false,
            //         'query_builder' => function (EntityRepository $er) use ($mention, $niveau)
            //         {
            //             $qb = $er->createQueryBuilder('p')
            //             ->where('p.mention = :pMention')
            //             //->setParameter('pMention', $mention);
            //             ->setParameter('pMention', 3)
            //             ->andWhere('p.niveau = :pNiveau')
            //             ->setParameter('pNiveau', $niveau);
            //             return $qb;
            //         },
            //         'choice_label' => function(Parcours $parcours) {
            //             return $parcours->getNom();
            //         }
            //     ]
            // );
        };
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($semestreModifier, $parcoursModifier) {
                $data = $event->getData();
                $semestreModifier($event->getForm(), $data->getNiveau());
                $parcoursModifier($event->getForm(), $data->getMention(), $data->getNiveau());
                $this->addUEField($event->getForm(), $data->getSemestre(), $data->getMention(), $data->getNiveau(), $data->getParcours());
                $this->addMatiereField($event->getForm(), $data->getUniteEnseignements());
            }
        );
        $builder->get('niveau')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($semestreModifier, $parcoursModifier) {
                $currentForm = $event->getForm();
                $parentForm = $currentForm->getParent();
                $niveau = $currentForm->getData();
                $mention = $parentForm->get('mention')->getData();
                $semestreModifier($parentForm, $niveau);
                $parcoursModifier($parentForm, $mention, $niveau);
                //$ueModifier($event->getForm(), $parentForm->getData()->getMention(), $data->getNiveau(), $parentForm->getData()->getParcours());
            }
        );
        // $builder->addEventListener(
        //     FormEvents::POST_SET_DATA,
        //     function (FormEvent $event)
        //     {
        //         $form   = $event->getForm();
        //         /** @var CalendrierExamen $data */
        //         $data = $form->getData();
        //         $this->addSemestreField($form, $data->getNiveau());
        //         $this->addUEField($form, $data->getSemestre());
        //         $this->addMatiereField($form, $data->getUniteEnseignements());
        //     }
        // );

        // $builder->get('niveau')->addEventListener(
        //     FormEvents::POST_SUBMIT,
        //     function (FormEvent $event)
        //     {
        //         /** @var \Symfony\Component\Form\FormInterface $form */
        //         $form = $event->getForm();
        //         $this->addSemestreField($form->getParent(), $form->getData());
        //     }
        // );
    }

    /**
     * Add department Field in the form
     *
     * @param \Symfony\Component\Form\FormInterface $form
     * @param \App\Entity\Niveau|null               $niveau
     */
    private function addSemestreField(FormInterface $form, ?Niveau $niveau)
    {
        dump('//////////////////////////////////////////');
        dump($niveau);
        $builder = $form->getConfig()->getFormFactory()->createNamedBuilder(
            'semestre',
            EntityType::class,
            null,
            [
                'class'           => Semestre::class,
                'placeholder'     => '--- Sélectionner ---',
                'mapped'          => true,
                'required'        => false,
                'auto_initialize' => false,
                'choices'         => $niveau ? $niveau->getSemestres() : [],
            ]
        );
        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event)
            {
                $form = $event->getForm();
                $this->addUEField($form->getParent(), $form->getData());
            }
        );
        $form->add($builder->getForm());
    }

    /**
     * Add city field in tne form
     *
     * @param \Symfony\Component\Form\FormInterface $form
     * @param \App\Entity\Semestre|null             $semestre
     */
    private function addUEField(FormInterface $form, ?Semestre $semestre, ?Mention $mention, ?Niveau $niveau, ?Parcours $parcours)
    {
        $builder = $form->getConfig()->getFormFactory()->createNamedBuilder(
            'uniteEnseignements',
            EntityType::class,
            null,
            [
                'class'           => UniteEnseignements::class,
                'placeholder'     => '--- Sélectionner ---',
                'mapped'          => true,
                'required'        => true,
                'auto_initialize' => false,
                'query_builder' => function (EntityRepository $er) use ($mention, $niveau, $parcours, $semestre)
                {
                    $qb = $er->createQueryBuilder('ue')
                    ->where('ue.mention = :pMention')
                    ->setParameter('pMention', $mention)
                    // ->setParameter('pMention', 3)
                    ->andWhere('ue.niveau = :pNiveau')
                    ->setParameter('pNiveau', $niveau)
                    ->andWhere('ue.semestre = :pSemestre')
                    ->setParameter('pSemestre', $semestre);
                    if($parcours){
                        $qb->andWhere('ue.parcours = :pParcours')
                        ->setParameter('pParcours', $parcours);
                    }
                    return $qb;
                },
                'choice_label' => function(UniteEnseignements $ue) {
                    return $ue->getLibelle();
                }
            ]
        );
        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event)
            {
                $form = $event->getForm();
                $this->addMatiereField($form->getParent(), $form->getData());
            }
        );
        $form->add($builder->getForm());
    }

    /**
     * @param \Symfony\Component\Form\FormInterface $form
     * @param \App\Entity\UniteEnseignements|null   $ue
     */
    private function addMatiereField(FormInterface $form, ?UniteEnseignements $ue)
    {
        $form->add(
            'matiere',
            EntityType::class,
            [
                'class'       => Matiere::class,
                'placeholder' => '--- Sélectionner ---',
                'choices'     => $ue ? $ue->getMatieres() : [],
                'required'    => true
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => CalendrierExamen::class,
                'calendar'   => null,
                'user'       => null,
                'em'         => null
            ]
        );
    }

}