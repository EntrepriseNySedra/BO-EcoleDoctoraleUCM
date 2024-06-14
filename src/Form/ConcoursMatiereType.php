<?php
/**
 * Description of ConcoursMatiereType.php.
 *
 * @package App\Form
 * @author Joelio
 */

namespace App\Form;

use App\Entity\Concours;
use App\Entity\ConcoursMatiere;
use App\Entity\Mention;
use App\Entity\Parcours;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Doctrine\ORM\EntityRepository;

class ConcoursMatiereType extends AbstractType
{

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builderOptions = $options['matieres'];
        $mentionId      = $builderOptions instanceof ConcoursMatiere ? $builderOptions->getMention()->getId() : null;
        $concoursId     = $builderOptions instanceof ConcoursMatiere ? $builderOptions->getConcours()->getId() : null;
        $parcoursId     = ($builderOptions instanceof ConcoursMatiere && $builderOptions->getParcours()) ? $builderOptions->getParcours()->getId() : null;

    
        $builder
            ->add('libelle')
            ->add(
                'concours',
                EntityType::class,
                [
                    'class'        => Concours::class,
                    'query_builder' => function (EntityRepository $er)
                    {
                        $qb = $er->createQueryBuilder('c')
                                ->Where('c.deletedAt IS NULL');                                    
                        return $qb;
                    },
                    'choice_label' => 'libelle',
                    'placeholder'  => '-- Concours --',
                    'data'         => ($em = $options['em']) ? ($concoursId ? $em->getReference(Concours::class, $concoursId) : null) : null,
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
                'mention',
                EntityType::class,
                [
                    'class'        => Mention::class,
                    'choice_label' => 'nom',
                    'placeholder'  => '-- Mention --',
                    'data'         => ($em = $options['em']) ? $em->getReference(Mention::class, $mentionId) : null
                ]
            )
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => ConcoursMatiere::class,
                'matieres'   => [],
                'em'         => [],
            ]
        );
    }
}