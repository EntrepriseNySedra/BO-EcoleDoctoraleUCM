<?php

namespace App\Form;

use App\Entity\Matiere;
use App\Entity\UniteEnseignements;
use App\Entity\Enseignant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Type;

use Doctrine\ORM\EntityRepository;

class MatiereType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $matiere = $options['data'];
        $builder
            ->add('nom')
            ->add(
                'code',
                TextType::class,
                [
                    'required' => false
                ]
            )
            ->add('uniteEnseignements', EntityType::class, [
                'class' => UniteEnseignements::class,
                'choice_label' => 'libelle',
                'placeholder'  => '-- Selectionnez --',
                'expanded'     => false,
                'query_builder' => function (EntityRepository $er) use ($matiere)
                {
                    $qb = $er->createQueryBuilder('ue');

                    if($matiere->getId() && ($mention = $matiere->getUniteEnseignements()->getMention())){
                        $qb = $qb->andWhere('ue.mention = :pMention')
                                ->setParameter('pMention', $mention);
                    }
                    if($matiere->getId() && ($niveau = $matiere->getUniteEnseignements()->getNiveau())){
                        $qb = $qb->andWhere('ue.niveau = :pNiveau')
                                ->setParameter('pNiveau', $niveau);
                    }
                    return $qb;
                }
            ])
            ->add('enseignant', EntityType::class, [
                'class' => Enseignant::class,
                'choice_label' => 'firstName',
                'placeholder'  => '-- Selectionnez --',
                'expanded' => false,
                'required' => false
            ])
            ->add('credit')
            ->add('volumeHoraireTotal',
                  IntegerType::class,
                  [
                      'required'    => false,
                      'constraints' => [
                          new Type(
                              [
                                  'type'    => 'integer',
                                  'message' => 'Merci d\'entrer un nombre !'
                              ]
                          )
                      ]
                  ]
            )
            ->add('etudeTheorique',
                  IntegerType::class,
                  [
                      'required'    => false,
                      'constraints' => [
                          new Type(
                              [
                                  'type'    => 'integer',
                                  'message' => 'Merci d\'entrer un nombre !'
                              ]
                          )
                      ]
                  ]
            )
            ->add('travauDirige',
                  IntegerType::class,
                  [
                      'required'    => false,
                      'constraints' => [
                          new Type(
                              [
                                  'type'    => 'integer',
                                  'message' => 'Merci d\'entrer un nombre !'
                              ]
                          )
                      ]
                  ]
            )
            ->add(
                'tauxHoraire', 
                MoneyType::class,
                [
                    'currency' => 'MGA'
                ]
            )
            ->add(
                'syllabus',
                CKEditorType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'active',
                CheckboxType::class,
                [
                    'required'     => false
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Matiere::class
        ]);
    }
}
