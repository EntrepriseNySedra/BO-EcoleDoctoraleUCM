<?php
/**
 * Description of ConcoursType.php.
 *
 * @package App\Form
 * @author Joelio
 */

namespace App\Form;

use App\Entity\AnneeUniversitaire;
use App\Entity\Concours;
use App\Entity\Mention;
use App\Entity\Niveau;
use App\Entity\Parcours;
use App\Entity\Roles;
use App\Entity\User;

use Doctrine\ORM\EntityRepository;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;

class ConcoursType extends AbstractType
{

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $concoursOptions = $options['concours'];
        $niveauId        = $concoursOptions instanceof Concours ? $concoursOptions->getNiveau()->getId() : null;
        $mentionId       = $concoursOptions instanceof Concours ? ($concoursOptions->getMention() ? $concoursOptions->getMention()->getId() : null) : null;
        $parcoursId      = $concoursOptions instanceof Concours ? ($concoursOptions->getParcours() ? $concoursOptions->getParcours()->getId() : null) : null;
        $signataireId    = $concoursOptions instanceof Concours ? ($concoursOptions->getSignataire() ? $concoursOptions->getSignataire()->getId() : null) : null;
        $anneeUnivId    = $concoursOptions instanceof Concours ? ($concoursOptions->getAnneeUniversitaire() ? $concoursOptions->getAnneeUniversitaire()->getId() : null) : null;

        $builder
            ->add('libelle')
            ->add(
                'startDate',
                DateType::class,
                [
                    'widget' => 'single_text',
                    'required'     => false
                ]
            )
            ->add(
                'endDate',
                DateType::class,
                [
                    'widget' => 'single_text',
                    'constraints' => [
                        new GreaterThan(
                            [
                                'propertyPath' => 'parent.all[startDate].data',
                                'message' => 'Date de fin devrait être plus récente que date de début !'
                            ]
                        )
                    ],
                    'required'     => false
                ]
            )
            ->add('deliberation')
            ->add(
                'niveau',
                EntityType::class,
                [
                    'class'        => Niveau::class,
                    'choice_label' => 'libelle',
                    'placeholder'  => '-- Niveau --',
                    'data'         => ($em = $options['em']) ? $em->getReference(Niveau::class, $niveauId) : null
                ]
            )
            ->add(
                'mention',
                EntityType::class,
                [
                    'class'        => Mention::class,
                    'choice_label' => 'nom',
                    'placeholder'  => '-- Mention --',
                    'data'         => ($em = $options['em']) ? ($mentionId ? $em->getReference(Mention::class, $mentionId) : null) : null,
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
                    'data'         => ($em = $options['em']) ? ($parcoursId ? $em->getReference(Parcours::class, $parcoursId) : null) : null,
                    'required'     => false
                ]
            )
            ->add(
                'signataire',
                EntityType::class,
                [
                    'class'        => User::class,
                    'query_builder' => function (EntityRepository $er) use ($mentionId)
                    {
                        $roles = [Roles::ROLE_CHEFMENTION, Roles::ROLE_DOYEN, Roles::ROLE_SG, Roles::ROLE_RECTEUR];
                        $qb = $er->createQueryBuilder('u')
                                ->innerJoin('u.profil', 'p')
                                ->innerJoin('p.roles', 'r')
                                ->addSelect('p')
                                ->addSelect('r')
                                ->andWhere('r.code IN (:roles)')
                                ->setParameter('roles', $roles);
                        return $qb;
                    },
                    'group_by' => function($u) {
                        return $u->getProfil()->getName();
                    },
                    'choice_label' => function (User $item) {
                        return $item->getLastName() . ' ' . $item->getFirstName();
                    },
                    'placeholder'  => '-- Signataire --',
                    'expanded'     => false,
                    'data'         => ($em = $options['em']) ? ($signataireId ? $em->getReference(User::class, $signataireId) : null) : null,
                    'required'     => false
                ]
            )
            ->add(
                'annee_universitaire',
                EntityType::class,
                [
                    'class'        => AnneeUniversitaire::class,
                    'choice_label' => 'libelle',
                    'placeholder'  => '-- Année universitaire --',
                    'data'         => ($em = $options['em']) ? ($anneeUnivId ? $em->getReference(AnneeUniversitaire::class, $anneeUnivId) : null) : null,
                    'required'     => false
                ]
            )
        ;

        // $builder
        //     ->get('startDate')
        //     ->addModelTransformer(
        //         new CallbackTransformer(
        //             function ($startDateAsDateTime)
        //             {
        //                 return $startDateAsDateTime ;
        //             },
        //             function ($startDateAsString) use($concoursOptions)
        //             {
        //                 $startDate = $startDateAsString ? \DateTime::createFromFormat('d/m/Y', $startDateAsString) : 
        //                 $concoursOptions ? $concoursOptions->getStartDate() :NULL;
        //                 return $startDateAsString;
        //             }
        //         )
        //     )
        // ;
        // $builder
        //     ->get('endDate')
        //     ->addModelTransformer(
        //         new CallbackTransformer(
        //             function ($endDateAsDateTime)
        //             {
        //                 return $endDateAsDateTime;
        //             },
        //             function ($endDateAsString)
        //             {
        //                 $endDate = $endDateAsString ? \DateTime::createFromFormat('d/m/Y', $endDateAsString) : NULL;

        //                 return $endDate;
        //             }
        //         )
        //     )
        // ;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Concours::class,
                'concours'   => [],
                'em'         => [],
            ]
        );
    }
}