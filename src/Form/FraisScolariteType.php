<?php

namespace App\Form;

use App\Entity\Etudiant;
use App\Entity\FraisScolarite;
use App\Entity\Inscription;
use App\Entity\Mention;
use App\Entity\Niveau;
use App\Entity\Parcours;
use App\Entity\Semestre;

use Doctrine\ORM\EntityRepository;

use App\Services\Form\EntityTypeTools;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FraisScolariteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fraisScolariteOptions = $options['data'];
        $semestre   = $fraisScolariteOptions->getSemestre();
        $mention    = $fraisScolariteOptions->getMention();
        $niveau     = $fraisScolariteOptions->getNiveau();
        $parcours   = $fraisScolariteOptions->getParcours();
        $etudiant   = $options['etudiant'];

        //dump($fraisScolariteOptions->getEtudiant());die;
        $builder
            ->add(
                'semestre',
                EntityType::class,
                [
                    'class'        => Semestre::class,
                    'choice_label' => function(Semestre $semestre) { 
                        return $semestre->getLibelle() . ' -- ' . $semestre->getNiveau()->getLibelle();
                    },
                    'placeholder'  => '-- Semestre --',
                    'data'         =>  null,
                    'expanded'     => false,
                    'required'     => true,
                    'data'         => $semestre,
                    'query_builder' => function (EntityRepository $er) use ($etudiant)
                    {
                        $qb = $er->createQueryBuilder('s');
                        if($etudiant){
                            $qb->andWhere('s.niveau = :niveau')
                               ->setParameter('niveau', $etudiant->getNiveau());                                    
                        }
                        return $qb;
                    },
                ]
            )
            ->add(
                'niveau',
                EntityType::class,
                [
                    'class'        => Niveau::class,
                    'choice_label' => 'libelle',
                    'placeholder'  => '-- Niveau --',
                    'data'         => null,
                    'required'     => true,
                    'data'         => $niveau
                ]
            )
            ->add(
                'mention',
                EntityType::class,
                [
                    'class'        => Mention::class,
                    'choice_label' => 'nom',
                    'placeholder'  => '-- Mention --',
                    'data'         => null,
                    'required'     => true,
                    'data'         => $mention
                ]
            )
            ->add(
                'parcours',
                EntityType::class,
                [
                    'class'        => Parcours::class,
                    'choice_label' => 'nom',
                    'placeholder'  => '-- Parcours --',
                    'data'         => null,
                    'required'     => false,
                    'data'         => $parcours
                ]
            )
            ->add(
                'etudiant',
                EntityType::class,
                [
                    'class'        => Etudiant::class,
                    // 'query_builder' => function (EntityRepository $er) use ($mention, $parcours, $niveau)
                    // {
                    //     $qb = $er->createQueryBuilder('i')
                    //                 ->innerJoin('i.etudiant', 'e', 'WITH', 'i.status = :status')
                    //                 ->addSelect('e')
                    //                 ->setParameter('status', Inscription::STATUS_VALIDATED)
                    //                 ->andWhere('e.mention = :mention')
                    //                 ->setParameter('mention', $mention)
                    //                 ->andWhere('e.niveau = :niveau')
                    //                 ->setParameter('niveau', $niveau);                                    
                    //     if($parcours) {
                    //         return $qb->andWhere('fs.parcours = :parcours')
                    //            ->setParameter('parcours', $parcours);
                    //     }
                    //     return $qb;
                    // },
                    // 'choice_label' => function (Inscription $inscription) {
                    //     return $inscription->getEtudiant()->getLastName() . ' ' . $inscription->getEtudiant()->getFirstName();
                    // },
                    'choice_label' => function(Etudiant $etudiant){
                        return $etudiant->getFirstName() . '  ' . $etudiant->getLastName();
                    },
                    'placeholder'  => '-- Etudiant --',
                    'expanded'     => false
                ]
            )
            ->add(
                'montant',
                MoneyType::class,
                [
                    'currency' => 'Ar',
                    'divisor'  => 1,
                    'required' => true
                ]
            )
            ->add('date_paiement', DateType::class, [
                'widget' => 'single_text'
            ])
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
                    'expanded' => true,
                    'required' => false
                ]
            )
            ->add(
                'remitter',
                TextType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'reference',
                TextType::class,
                [
                    'required' => true
                ]
            )
            ->add(
                'paymentRef',
                TextType::class,
                [
                    'required' => false,
                    'label'    => false,
                    'mapped'   => false
                ]
            )
            ->add(
                'paymentRefPath',
                FileType::class,
                [
                    'data_class' => null,
                    'required' => false,
                    'label'    => false
                ]
            )
            ->add(
                'comment',
                TextareaType::class,
                [
                    'required' => false,
                    'mapped' => false
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'        => FraisScolarite::class,
                'fraisScolarite'    => [],
                'em'                => [],
                'mention'           => [],
                'niveau'            => [],
                'etudiant'          => []
            ]
        );
    }
}
