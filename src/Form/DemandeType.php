<?php

namespace App\Form;

use App\Entity\Etudiant;
use App\Entity\Mention;
use App\Entity\Niveau;
use App\Entity\Parcours;
use App\Entity\ConcoursCandidature;
use App\Entity\DemandeDoc;
use App\Entity\DemandeDocType;
use App\Entity\Profil;
use App\Entity\Roles;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ProfilType
 *
 * @package App\Form
 */
class DemandeType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array                                        $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builderOptions = $options['demande'];
        $niveauId       = $builderOptions instanceof DemandeDoc ? $builderOptions->getNiveau()->getId()   : null;
        $mentionId      = $builderOptions instanceof DemandeDoc ? $builderOptions->getMention()->getId()  : null;
//        $parcoursId     = $builderOptions instanceof DemandeDoc ? $builderOptions->getParcours()->getId() : null;
        
        $builder
        ->add(
            'etudiant',
                EntityType::class,
                [
                    'class' => Etudiant::class,
                    'choice_label'     => 'first_name',
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
                        'data'         => ($em = $options['em']) ? $em->getReference(Mention::class, $mentionId) : null,
                        'expanded'  => false
                    ]
            )

            ->add(
                'niveau',
                EntityType::class,
                [
                    'class' => Niveau::class,
                    'choice_label'     => 'libelle',
                    'placeholder'  => '-- Séléctionnez --',
                    'data'         => ($em = $options['em']) ? $em->getReference(Niveau::class, $niveauId) : null,
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
//                    'data'         => ($em = $options['em']) ? $em->getReference(Parcours::class, $parcoursId) : null,
                    'expanded'  => false,
                    'required'  => false
                ]
            )
            ->add(
                'type',
                EntityType::class,
                [
                    'placeholder'  => '-- Séléctionnez --',
                    'class'        => DemandeDocType::class,
                    'choice_label' => 'libelle',
                    'multiple'     => false,
                    'expanded'     => false
                ]
            )
            ->add('quantity')
            ->add('matricule')
            ->add(
                'sexe',
                ChoiceType::class,
                [
                    'label'    => 'Sexe',
                    'choices'  => ConcoursCandidature::$genderList,
                    'required' => true,
                    'expanded' => false
                ]
            )
            ->add(
                'civility',
                ChoiceType::class,
                [
                    'label' => 'Civilité',
                    'choices' => DemandeDoc::$civilitiesList,
                    'required' => true,
                    'expanded' => false
                ]
            )
            ->add('lastName')
            ->add('firstName')
            ->add(
                'birthDate',
                TextType::class
            )
            ->add('birthPlace')
            ->add(
                'description',
                TextType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'address',
                TextType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'postalCode',
                TextType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'city',
                TextType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'country',
                TextType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'phone',
                TextType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'portable',
                TextType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'email',
                TextType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'addressPro',
                TextType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'job',
                TextType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'diplomeName',
                TextType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'diplomeYear',
                TextType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'diplomeLibelle',
                TextType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'diplomeMention',
                TextType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'identityPiece',
                TextType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'depotAttestation',
                TextType::class,
                [
                    'required' => false
                ]
            )
            ->add(
                'identityPiece',
                FileType::class,
                [
                    'required'  => false,
                    'label'     => false
                ]
            )
            ->add(
                'depotAttestation',
                FileType::class,
                [
                    'required'  => false,
                    'label'     => false
                ]
            )
        ;

        $builder
            ->get('birthDate')
            ->addModelTransformer(
                new CallbackTransformer(
                    function ($birthDateAsDateTime)
                    {
                        return $birthDateAsDateTime;
                    },
                    function ($birthDateAsString)
                    {
                        $birthDate = \DateTime::createFromFormat('d/m/Y', $birthDateAsString);
                        return !$birthDate ? null : $birthDate ;
                    }
                )
            )
        ;
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => DemandeDoc::class,
                'em'         => [],
                'demande'    => [],
            ]
        );
    }
}
