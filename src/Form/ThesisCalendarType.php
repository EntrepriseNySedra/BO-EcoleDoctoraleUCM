<?php

namespace App\Form;

use App\Entity\CalendrierSoutenance;
use App\Entity\Etudiant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of ThesisCalendarType.php.
 *
 * @package App\Form
 */
class ThesisCalendarType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var CalendrierSoutenance $calendarOptions */
        $calendarOptions = $options['calendar'];
        $etudiantId      = $calendarOptions instanceof CalendrierSoutenance ? $calendarOptions->getEtudiant()->getId() : null;

        /** @var \App\Entity\User $user */
        $user   = $options['user'];
        $status = CalendrierSoutenance::$onlyCreatedStatus;
        if ($user && in_array('ROLE_CHEFMENTION', $user->getRoles())) {
            $status = CalendrierSoutenance::$cmStatusList;
        }
        $builder
            ->add(
                'libelle',
                TextType::class,
                [
                    'required' => true
                ]
            )
            ->add(
                'etudiant',
                EntityType::class,
                [
                    'class'        => Etudiant::class,
                    'choice_label' => 'first_name',
                    'placeholder'  => '-- Etudiant --',
                    'data'         => ($em = $options['em']) ? $em->getReference(
                        Etudiant::class, $etudiantId
                    ) : null,
                    'expanded'  => false
                ]
            )
            ->add(
                'dateSchedule',
                DateTimeType::class,
                [
                    'widget' => 'single_text',
                    'required' => true
                ]
            )
            ->add(
                'startTime',
                TimeType::class,
                [
                    'widget' => 'single_text',
                    'required' => true
                ]
            )
            ->add(
                'endTime',
                TimeType::class,
                [
                    'widget' => 'single_text',
                    'required' => true
                ]
            )
            ->add(
                'status',
                ChoiceType::class,
                [
                    'placeholder' => '--- Séléctionner ---',
                    'choices' => $status
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
                'data_class' => CalendrierSoutenance::class,
                'calendar'   => [],
                'user'       => [],
                'em'         => [],
            ]
        );
    }

}