<?php

namespace App\Form;

use App\Entity\Prestation;
use App\Entity\PrestationUser;
use App\Entity\Profil;
use App\Entity\TypePrestation;
use App\Entity\TypePrestationMention;
use App\Entity\Roles;
use App\Entity\User;

use Doctrine\ORM\EntityRepository;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PrestationType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {   
        $prestation = $options['data'];
        // $prestationUsers = $prestation->getPrestationUsers();
        $profilRepo = isset($options['profil_repo']) ? $options['profil_repo'] : null ;
        $mention = $options['mention'];
        $profilEtudiant = Profil::ETUDIANT;
        
        $builder
            ->add('date', DateType::class, [
                'widget' => 'single_text'
            ])
            ->add('quantite', NumberType::class, [
            ])
            ->add('commentaire', TextareaType::class, [
                'required' => false
            ])
            // ->add(
            //     'prestationUsers',
            //     EntityType::class,
            //     [
            //         'class'         => User::class,
            //         'query_builder' => function (EntityRepository $er) use ($profilEtudiant)
            //         {
            //             $qb = $er->createQueryBuilder('u')
            //                      ->innerJoin(Profil::class, 'p');
            //             $qb->Where('p.name = :test')
            //             ->setParameter('test', 'Enseignan');
            //             $qb->AndWhere('u.id = :pProfilEtudiant')
            //             ->setParameter('pProfilEtudiant', 1);
                            
            //             return $qb;
            //         },
            //         'choice_label'  => function ($e)
            //         {
            //             return $e->getFirstName() . ' ' . $e->getLastName();
            //         },
            //         'choice_value' => 'id',
            //         'group_by' => function($e) {
            //             return $e->getProfil()->getName();
            //         },
            //         'placeholder'   => '-- Séléctionnez --',
            //         'expanded'      => false,
            //         'required'      => false,
            //         'multiple'      => true,
            //         'attr' => [
            //             'class' => 'js-example-basic-multiple'
            //         ]
            //         ,
            //         'data' => array_map(
            //                         function($item){
            //                             return $item->getUser();
            //                         }, $prestationUsers->toArray()
            //                     )
            //     ]
            // )
             ->add(
                'user',
                EntityType::class,
                [
                    'class'         => User::class,
                    'query_builder' => function (EntityRepository $er)
                    {
                        $excludeRoles = [Roles::ROLE_ETUDIANT, Roles::ROLE_ADMIN];
                        $qb = $er->createQueryBuilder('u')
                                 ->innerJoin('u.profil', 'p')
                                 ->innerJoin('p.roles', 'r')
                                 ->addSelect('p')
                                 ->addSelect('r');
                        // $qb->Where($qb->expr()->neq('r.code', ":pRoleEtudiant"))
                        //     ->setParameter('pRoleEtudiant', $roleEtudiant);                            

                        $qb->andWhere('r.code NOT IN (:pExcludeRole)')
                            ->setParameter('pExcludeRole', $excludeRoles);                            
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
                    'data' => [$prestation->getUser()]
                ]
            )
            ->add(
                'type_prestation',
                EntityType::class,
                [
                    'class'         => TypePrestation::class,
                    'query_builder' => function (EntityRepository $er) use ($mention)
                    {
                        $qb = $er->createQueryBuilder('tp')
                                 ->innerJoin('tp.typePrestationMentions', 'tpm');
                        if ($mention) {
                            return $qb->addSelect('tpm')
                                    ->andWhere('tpm.mention = :mention')
                               ->setParameter('mention', $mention);
                        }
                        return $qb;
                    },
                    'choice_label'  => function (TypePrestation $e)
                    {
                        return $e->getDesignation();
                    },
                    'placeholder'   => '-- Séléctionnez --',
                    'expanded'      => false,
                    'required'      => true
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
            'data_class'    => Prestation::class,
            'profil_repo'   => [],
            'mention'       => null
        ]);
    }
}
