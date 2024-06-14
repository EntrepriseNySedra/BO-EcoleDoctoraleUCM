<?php

namespace App\Form;

use App\Entity\Absences;
use App\Entity\Departement;
use App\Entity\EmploiDuTemps;
use App\Entity\Mention;
use App\Entity\Niveau;
use App\Entity\Parcours;
use App\Entity\UniteEnseignements;
use App\Entity\Matiere;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class AbsenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $absence = $options['data'];
        $builder
            ->add(
                'domaine',
                EntityType::class,
                [
                    'class' => Departement::class,
                    'choice_label'     => 'nom',
                    'placeholder'  => '-- Séléctionnez --',
                    'expanded'  => false
                ]
            )
            ->add(
                'mention',
                EntityType::class,
                [
                    'class' => Mention::class,
                    'choice_label'     => 'nom',
                    'placeholder'  => '-- Séléctionnez --',
                    'expanded'  => false
                    // 'query_builder' => function (EntityRepository $er) use ($absence)
                    // {
                    //     $qb = $er->createQueryBuilder('m')
                    //             ->andWhere('m.departement = :pDepartement')
                    //             ->setParameter('pDepartement', $absence->getDomaine());
                    //     return $qb;
                    // },
                    // 'data' => $absence ? $absence->getMention() : null
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
                    // 'query_builder' => function (EntityRepository $er) use ($absence)
                    // {
                    //     $qb = $er->createQueryBuilder('p')
                    //             ->andWhere('p.mention = :pMention')
                    //             ->setParameter('pMention', $absence->getMention());
                    //     return $qb;
                    // }
                ]
            )
            ->add(
                'ue',
                EntityType::class,
                [
                    'class' => UniteEnseignements::class,
                    'choice_label'     => 'libelle',
                    'placeholder'  => '-- Séléctionnez --',
                    'expanded'  => false,
                    'required' => false,
                    'data' => null
                    // 'query_builder' => function (EntityRepository $er) use ($absence)
                    // {
                    //     $qb = $er->createQueryBuilder('ue')
                    //             ->andWhere('ue.mention = :pMention')
                    //             ->setParameter('pMention', $absence->getMention())
                    //             ->andWhere('ue.niveau = :pNiveau')
                    //             ->setParameter('pNiveau', $absence->getNiveau())
                    //             ->andWhere('ue.parcours = :pParcours')
                    //             ->setParameter('pParcours', $absence->getParcours());
                    //     return $qb;
                    // }
                ]
            )
            ->add(
                'matiere',
                EntityType::class,
                [
                    'class' => Matiere::class,
                    'choice_label'     => 'nom',
                    'placeholder'  => '-- Séléctionnez --',
                    'expanded'  => false,
                    'required' => false
                    // 'query_builder' => function (EntityRepository $er) use ($absence)
                    // {
                    //     $qb = $er->createQueryBuilder('mat')
                    //             ->andWhere('mat.uniteEnseignements = :pUe')
                    //             ->setParameter('pUe', $absence->getMention());
                    //     return $qb;
                    // }
                ]
            )


            // ->add(
            //     'emploi_du_temps',
            //     EntityType::class,
            //     [
            //         'class' => EmploiDuTemps::class,
            //         'placeholder'  => '-- Séléctionnez --',
            //         'expanded'  => false,
            //         'choice_label' => function(EmploiDuTemps $edt){
            //             // !!$edt->getMatiere() ? dd($edt->getMatiere()) : dd($edt);
            //          return $edt->getStartTime()->format('H:i') . '-' . $edt->getEndTime()->format('H:i') ;
            //         },




                    
                    // 'query_builder' => function (EntityRepository $er)
                    // {
                    //     $survProfileName = Profil::SURVEILLANT;
                    //     $qb = $er->createQueryBuilder('u')
                    //             ->innerJoin('u.profil', 'p')
                    //             ->innerJoin('p.roles', 'r')
                    //             ->addSelect('p')
                    //             ->addSelect('r');
                    //         $qb->where('p.name = :pProfil')
                    //         ->setParameter('pProfil', $survProfileName);
                    //     return $qb;  
                    // },
                    // 'query_builder' => function (EntityRepository $er) use ($absence)
                    // {
                    //     $qb = $er->createQueryBuilder('epl')
                    //             ->andWhere('epl.mention = :pMention')
                    //             ->setParameter('pMention', $absence->getMention());
                    //     return $qb;
                    // },
                    // 'choice_label'  => function (EmploiDuTemps $e)
                    // {
                    //     return $e->getId() . ' ' . ($e->getMatiere() ? $e->getMatiere()->getNom() : '');
                    // },
                    // 'choice_value' => 'id',
                    // 'group_by' => function($e) {
                    //     return $e->getProfil()->getName();
                    // },
                    // 'placeholder'   => '-- Séléctionnez --',
                    // 'expanded'      => false,
                    // 'mapped'        => false,
                    // 'required'      => false,
                    // 'multiple'      => true,
                    // 'attr' => [
                    //     'class' => 'js-example-basic-multiple'
                    // ]



                    //'choice_value' => 'id',
                    
                    //'choices' => [],
                    // 'multiple'      => true,
                    // 'mapped' => false
            //     ]
            // )


            ->add(
                'date',
                DateType::class,
                [
                    'widget' => 'single_text'
                ]
            )
            // ->add(
            //     'startTime',
            //     TimeType::class,
            //     [
            //         'widget' => 'single_text',
            //         'required' => false
            //     ]
            // )
            // ->add(
            //     'endTime',
            //     TimeType::class,
            //     [
            //         'widget' => 'single_text',
            //         'required' => false
            //     ]
            // )
            ->add(
                'description',
                TextareaType::class,
                [
                    'required' => 'false'
                ]
            )
            ->add(
                'justification',
                FileType::class,
                [
                    'required' => 'false',
                    'data_class' => null
                ]
            )
        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
            'data_class' => Absences::class,
            'allow_extra_fields' => true,
        ]);
    }
}
