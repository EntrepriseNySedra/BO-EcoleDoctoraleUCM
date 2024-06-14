<?php
/**
 * Description of ConcoursType.php.
 *
 * @package App\Form
 * @author Joelio
 */

namespace App\Form;

use App\Entity\AnneeUniversitaire;
use App\Entity\ConcoursConfig;
use App\Entity\Roles;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConcoursConfigsType extends AbstractType
{

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $concoursOptions = $options['concours_config'];
        $anneeUnivId    = $concoursOptions instanceof ConcoursConfig ? ($concoursOptions->getAnneeUniversitaire() ? $concoursOptions->getAnneeUniversitaire()->getId() : null) : null;
        $builder
            ->add('libelle',
                TextType::class,
                [
                    'required'     => true
                ]
            )
            ->add(
                'start_date',
                TextType::class,
                [
                    'required'     => true
                ]
            )
            ->add(
                'end_date',
                TextType::class,
                [
                    'required'     => true
                ]
            )
            ->add(
                'annee_universitaire',
                EntityType::class,
                [
                    'class'        => AnneeUniversitaire::class,
                    'choice_label' => 'libelle',
                    'placeholder'  => '-- Année universitaire --',
                    'required'     => true
                ]
            )
        ;

        $builder
            ->get('start_date')
            ->addModelTransformer(
                new CallbackTransformer(
                    function ($startDateAsDateTime)
                    {
                        return $startDateAsDateTime;
                    },
                    function ($startDateAsString)
                    {
                        $startDate = \DateTime::createFromFormat('d/m/Y', $startDateAsString);

                        return $startDate;
                    }
                )
            )
        ;
        $builder
            ->get('end_date')
            ->addModelTransformer(
                new CallbackTransformer(
                    function ($endDateAsDateTime)
                    {
                        return $endDateAsDateTime;
                    },
                    function ($endDateAsString)
                    {
                        $endDate = \DateTime::createFromFormat('d/m/Y', $endDateAsString);

                        return $endDate;
                    }
                )
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
                'data_class' => ConcoursConfig::class,
                'concours_config'   => [],
                'em'         => [],
            ]
        );
    }
}