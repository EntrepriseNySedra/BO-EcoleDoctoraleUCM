<?php

namespace App\Controller\Frontend;

use App\Entity\CalendrierExamen;
use App\Entity\CalendrierSoutenance;
use App\Entity\CalendrierSoutenanceHistorique;
use App\Entity\Concours;
use App\Entity\EmploiDuTemps;
use App\Entity\Etudiant;
use App\Entity\ExtraNote;
use App\Entity\FichePresenceEnseignant;
use App\Entity\Mention;
use App\Entity\PaiementHistory;
use App\Entity\Prestation;
use App\Entity\Salles;
use App\Entity\User;
use App\Entity\Enseignant;
use App\Entity\EnseignantMatiere;
use App\Entity\DemandeDoc;
use App\Entity\Matiere;
use App\Entity\ConcoursCandidature;
use App\Form\ConcoursNotesFormType;
use App\Form\ConcoursType;
use App\Form\EnseignantParcoursType;
use App\Form\EnseignantType;
use App\Form\ExamenCalendarType;
use App\Form\ExtraNoteType;
use App\Form\FichePresenceEnseignantType;
use App\Form\MatiereType;
use App\Form\EnseignantMatiereType;
use App\Form\EmploiDuTempsType;
use App\Form\NoteType;
use App\Form\SallesType;
use App\Form\DemandeType;
use App\Form\ThesisCalendarType;
use App\Form\RegistrationFormType;
use App\Manager\AnneeUniversitaireManager;
use App\Manager\CalendrierExamenHistoriqueManager;
use App\Manager\CalendrierExamenManager;
use App\Manager\CalendrierPaiementManager;  
use App\Manager\CalendrierSoutenanceHistoriqueManager;
use App\Manager\CalendrierSoutenanceManager;
use App\Manager\ConcoursCandidatureManager;
use App\Manager\ConcoursManager;
use App\Manager\ConcoursMatiereManager;
use App\Manager\ConcoursNotesManager;
use App\Manager\DemandeDocHistoriqueManager;
use App\Manager\EmploiDuTempsManager;
use App\Manager\EnseignantManager;
use App\Manager\EnseignantMentionManager;
use App\Manager\EnseignantMatiereManager;
use App\Manager\ExtraNoteHistoriqueManager;
use App\Manager\ExtraNotesManager;
use App\Manager\FichePresenceEnseignantHistoriqueManager;
use App\Manager\FichePresenceEnseignantManager;
use App\Manager\MatiereManager;
use App\Manager\MentionManager;
use App\Manager\NiveauManager;
use App\Manager\NotesManager;
use App\Manager\PrestationManager;
use App\Manager\SallesManager;
use App\Manager\SemestreManager;
use App\Manager\UserManager;
use App\Manager\DepartementManager;
use App\Manager\ProfilManager;
use App\Manager\UniteEnseignementsManager;
use App\Manager\EtudiantManager;
use App\Manager\ParcoursManager;
use App\Manager\DemandeDocManager;
use App\Repository\CalendrierPaiementRepository;
use App\Repository\EmploiDuTempsRepository;
use App\Repository\EnseignantRepository;
use App\Repository\EtudiantRepository;
use App\Repository\MatiereRepository;

use App\Services\UtilFunctionService;

use App\Services\AnneeUniversitaireService;
use App\Services\NotesService;
use App\Services\WorkflowStatutService;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Container\ContainerInterface;
use Ramsey\Uuid\Uuid;
use Dompdf\Dompdf;
use Dompdf\Options;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Description of ChefMentionController.php.
 *
 * @package App\Controller\Frontend
 * @Route("/cm")
 */
class ChefMentionController extends AbstractController
{

    /**
     * @Route("/", name="front_chefmention_index")
     * @param \App\Manager\EnseignantManager $manager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @IsGranted("ROLE_CHEFMENTION")
     */
    public function index()
    {
        return $this->redirectToRoute('front_chefmention_presence_enseignant_index');
    }

    /**
     * @Route("/identification", name="front_chefmention_login")
     */
    public function login()
    {
        return $this->render(
            'frontend/common/login.html.twig',
            [
                'entity'     => 'chef-mention',
                'espacename' => 'Espace chef-mention'
            ]
        );
    }

    /**
     * @Route("/mon-compte", name="front_chefmention_me")
     * @IsGranted("ROLE_CHEFMENTION")
     */
    public function me(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder
    )
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user            = $this->getUser();
        $form = $this->createForm(
            RegistrationFormType::class, 
            $user,
            [
                'user' => $user,
                'em'   => $entityManager
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $login = $form->get('login')->getData();
            if(!empty($login))
                $user->setLogin($login);
            $clearPassword = $form->get('plainPassword')->getData();
            if (!empty($clearPassword)) {
                $password = $passwordEncoder->encodePassword(
                    $user,
                    $clearPassword
                );
                $user->setPassword($password);
            }
            $entityManager->persist($user);            
            $entityManager->flush();
            
            return $this->redirectToRoute('front_chefmention_presence_enseignant_index');
        }

        return $this->render(
            'frontend/chef-mention/mon-compte.html.twig',
            [
                'user'    => $user,
                'form'          => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/enseignant/add", name="front_chefmention_create_enseignant",  methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request                                 $request
     * @param \App\Manager\UserManager                                                  $userManager ,
     * @param \App\Manager\ProfilManager                                                $profilManager ,
     * @param \App\Manager\EnseignantManager                                            $manager
     * @param \App\Manager\EnseignantMentionManager                                     $enseignantmentionmanager
     * @param \App\Manager\MentionManager                                               $mentionmanager
     * @param \App\Manager\NiveauManager                                                $niveaumanager
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameter
     * @IsGranted("ROLE_CHEFMENTION")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function enseignantNew(
        Request $request,
        UserManager $userManager,
        ProfilManager $profilManager,
        EnseignantManager $manager,
        EnseignantMentionManager $enseignantmentionmanager,
        MentionManager $mentionmanager,
        NiveauManager $niveaumanager,
        UserPasswordEncoderInterface $passwordEncoder,
        ParameterBagInterface $parameter
    ) : Response {
        $enseignant = $manager->createObject();
        $form       = $this->createForm(
            EnseignantType::class,
            $enseignant
        );
        $form->handleRequest($request);
        $mentions = $mentionmanager->loadAll();
        $niveaux  = $niveaumanager->loadAll();
        $this->denyAccessUnlessGranted('ROLE_CHEFMENTION');
        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            $cvFile       = $form->get('pathCv')->getData();
            $diplomeFile  = $form->get('pathDiploma')->getData();
            $niveauPosted = ($_POST["niveau"]) ? $_POST["niveau"] : "";

            $directory     = $parameter->get('enseignants_directory');
            $uploader      = new \App\Services\FileUploader($directory);
            $today         = new \DateTime();
            $fileDirectory = $enseignant->getLastName() . "-" . $today->getTimestamp();

            // Upload file
            if ($cvFile) {
                $cvFileDisplay = $uploader->upload($cvFile, $directory, $fileDirectory);
                $enseignant->setPathCv($fileDirectory . "/" . $cvFileDisplay["filename"]);
            }

            if ($diplomeFile) {
                $diplomeFileDisplay = $uploader->upload($diplomeFile, $directory, $fileDirectory);
                $enseignant->setPathDiploma($fileDirectory . "/" . $diplomeFileDisplay["filename"]);
            }

            //insert line to user table
            $profilEnseignant = $profilManager->loadOneBy(['name' => 'Enseignant']);
            $newUser          = $userManager->createObject();
            $newUser->setProfil($profilEnseignant);
            $newUser->setEmail($enseignant->getEmail());
            $newUser->setFirstName($enseignant->getFirstName());
            $newUser->setLastName($enseignant->getLastName());

            $newUser->setLogin($enseignant->getEmail());
            $newUser->setPassword(
                $passwordEncoder->encodePassword(
                    $newUser,
                    "123456789"
                )
            );
            $newUser->setStatus(1);
            $userManager->save($newUser);
            $enseignant->setUser($newUser);
            $manager->save($enseignant);

            // Mention
            $resMention = $mentionmanager->loadOneBy(["id" => $user->getMention()->getId()]);

            // Niveau
            if (count($niveauPosted) != 0) {
                foreach ($niveauPosted as $key => $value) {
                    $enseignantmention = $enseignantmentionmanager->createObject();
                    $resNiveau         = $niveaumanager->loadOneBy(["id" => $value]);
                    $enseignantmention->setNiveau($resNiveau);
                    $enseignantmention->setEnseignant($enseignant);
                    $enseignantmention->setMention($resMention);
                    $enseignantmentionmanager->persist($enseignantmention);
                }
            }

            if (count($niveauPosted) != 0) {
                $enseignantmentionmanager->flush();
                // $enseignantmentionmanager->save($enseignantmention);
            }

            return $this->redirectToRoute('front_chefmention_edit_enseignant_2', ['id' => $enseignant->getId()]);
        }

        return $this->render(
            'frontend/chef-mention/addEnseignant.html.twig',
            [
                'enseignant' => $enseignant,
                'mentions'   => $mentions,
                'niveaux'    => $niveaux,
                'form'       => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}/edit", name="front_chefmention_edit_enseignant", methods={"GET","POST"})
     * @param \Symfony\Component\HttpFoundation\Request                                 $request
     * @param \App\Entity\Enseignant                                                    $enseignant
     * @param \App\Manager\EnseignantManager                                            $manager
     * @param \App\Manager\EnseignantMentionManager                                     $enseignantmentionmanager
     * @param \App\Manager\MentionManager                                               $mentionmanager
     * @param \App\Manager\NiveauManager                                                $niveaumanager
     * @param \App\Manager\ProfilManager                                                $profilManager ,
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameter
     * @param \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface     $passwordEncoder
     * @IsGranted("ROLE_CHEFMENTION")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function enseignantEdit(
        Request $request,
        Enseignant $enseignant,
        UserManager $userManager,
        EnseignantManager $manager,
        EnseignantMentionManager $enseignantmentionmanager,
        MentionManager $mentionmanager,
        NiveauManager $niveaumanager,
        ProfilManager $profilManager,
        UserPasswordEncoderInterface $passwordEncoder,
        ParameterBagInterface $parameter
    ) : Response {
        $form = $this->createForm(EnseignantType::class, $enseignant);
        $form->handleRequest($request);
        $mentions = $mentionmanager->loadAll();
        $niveaux  = $niveaumanager->loadAll();
        $this->denyAccessUnlessGranted('ROLE_CHEFMENTION');
        $user = $this->getUser();

        $currentNiveau       = $enseignantmentionmanager->loadby(['enseignant' => $enseignant->getId()]);
        $tCollectEnsNiveauId = [];
        foreach ($currentNiveau as $niv) {
            if (!in_array($curNivId = $niv->getNiveau()->getId(), $tCollectEnsNiveauId)) {
                $tCollectEnsNiveauId[] = $curNivId;
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $cvFile       = $form->get('pathCv')->getData();
            $diplomeFile  = $form->get('pathDiploma')->getData();
            $niveauPosted = ($_POST["niveau"]) ? $_POST["niveau"] : "";

            $directory     = $parameter->get('enseignants_directory');
            $uploader      = new \App\Services\FileUploader($directory);
            $today         = new \DateTime();
            $fileDirectory = $enseignant->getLastName() . "-" . $today->getTimestamp();

            // Upload file
            if ($cvFile) {
                $cvFileDisplay = $uploader->upload($cvFile, $directory, $fileDirectory);
                $enseignant->setPathCv($fileDirectory . "/" . $cvFileDisplay["filename"]);
            }

            if ($diplomeFile) {
                $diplomeFileDisplay = $uploader->upload($diplomeFile, $directory, $fileDirectory);
                $enseignant->setPathDiploma($fileDirectory . "/" . $diplomeFileDisplay["filename"]);
            }

            //insert line to user table
            if ($enseignant->getUser()) {
                $newUser = $userManager->load($enseignant->getUser());
            } else {
                $newUser          = $userManager->createObject();
                $profilEnseignant = $profilManager->loadOneBy(['name' => 'Enseignant']);
                $newUser->setProfil($profilEnseignant);
                $newUser->setLogin($enseignant->getEmail());
                $newUser->setPassword(
                    $passwordEncoder->encodePassword(
                        $newUser,
                        "123456789"
                    )
                );
                $newUser->setStatus(1);
            }
            $newUser->setEmail($enseignant->getEmail());
            $newUser->setFirstName($enseignant->getFirstName());
            $newUser->setLastName($enseignant->getLastName());
            $userManager->save($newUser);

            $enseignant->setUser($newUser);
            $manager->save($enseignant);

            $resMention = $mentionmanager->loadOneBy(["id" => $user->getMention()->getId()]);

            //remove all enseignant mention before save
            if (count($niveauPosted) != 0) {
                $currentEnsMentionNiveau = $enseignantmentionmanager->loadBy(
                    [
                        'enseignant' => $enseignant->getId(),
                        'mention'    => $resMention->getId()
                    ]
                );
                foreach ($currentEnsMentionNiveau as $ensMentionNiveau) {
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->remove($ensMentionNiveau);
                    $entityManager->flush();
                }
            }

            // Niveau
            if (count($niveauPosted) != 0) {
                foreach ($niveauPosted as $key => $value) {
                    $enseignantmention = $enseignantmentionmanager->createObject();
                    $resNiveau         = $niveaumanager->loadOneBy(["id" => $value]);
                    $enseignantmention->setNiveau($resNiveau);
                    $enseignantmention->setEnseignant($enseignant);
                    $enseignantmention->setMention($resMention);
                    $enseignantmentionmanager->persist($enseignantmention);
                }
            }

            if (count($niveauPosted) != 0) {
                $enseignantmentionmanager->flush();
            }

            return $this->redirectToRoute('front_chefmention_edit_enseignant_2', ['id' => $enseignant->getId()]);

        }

        return $this->render(
            'frontend/chef-mention/editEnseignant.html.twig',
            [
                'enseignant'          => $enseignant,
                'mentions'            => $mentions,
                'niveaux'             => $niveaux,
                'tCollectEnsNiveauId' => $tCollectEnsNiveauId,
                'form'                => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}/edit-2", name="front_chefmention_edit_enseignant_2", methods={"GET","POST"})
     * @param \Symfony\Component\HttpFoundation\Request                                 $request
     * @param \App\Entity\Enseignant                                                    $enseignant
     * @param \App\Manager\EnseignantManager                                            $manager
     * @param \App\Manager\EnseignantMentionManager                                     $enseignantmentionmanager
     * @param \App\Manager\MentionManager                                               $mentionmanager
     * @param \App\Manager\NiveauManager                                                $niveaumanager
     * @param \App\Manager\ProfilManager                                                $profilManager ,
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameter
     * @param \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface     $passwordEncoder
     * @IsGranted("ROLE_CHEFMENTION")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function enseignantEdit2(
        Request $request,
        Enseignant $enseignant,
        UserManager $userManager,
        EnseignantManager $manager,
        EnseignantMentionManager $enseignantmentionmanager,
        MentionManager $mentionmanager,
        NiveauManager $niveaumanager,
        ProfilManager $profilManager,
        UserPasswordEncoderInterface $passwordEncoder,
        ParcoursManager $parcoursManager,
        ParameterBagInterface $parameter
    ) : Response {
        $form = $this->createForm(EnseignantParcoursType::class, $enseignant);
        $form->handleRequest($request);
        $this->denyAccessUnlessGranted('ROLE_CHEFMENTION');
        $user = $this->getUser();

        $currentNiveau         = $enseignantmentionmanager->loadby(['enseignant' => $enseignant->getId()]);
        $tCollectEnsNiveauId   = [];
        $tCollectEnsParcoursId = [];
        foreach ($currentNiveau as $niv) {
            if (!in_array($curNivId = $niv->getNiveau()->getId(), $tCollectEnsNiveauId)) {
                $tCollectEnsNiveauId[] = $curNivId;
            }
            if ($niv->getParcours() && !in_array($curPId = $niv->getParcours()->getId(), $tCollectEnsParcoursId)) {
                $tCollectEnsParcoursId[] = $curPId;
            }
        }

        /** @var \App\Entity\Mention $mention */
        $mention = $user->getMention();

        $parcours = $parcoursManager->loadBy(['niveau' => $tCollectEnsNiveauId, 'mention' => $mention]);
        if (!$parcours) {
            return $this->redirectToRoute('front_chefmention_index');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $request->request->all();

            //remove all enseignant mention before save
            $currentEnsMentionNiveau = $enseignantmentionmanager->loadBy(
                [
                    'enseignant' => $enseignant->getId(),
                    'mention'    => $mention->getId()
                ]
            );
            foreach ($currentEnsMentionNiveau as $ensMentionNiveau) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($ensMentionNiveau);
                $entityManager->flush($ensMentionNiveau);
            }

            if (count($formData) != 0) {
                foreach ($formData['parcours'] as $parcoursId) {
                    /** @var \App\Entity\Parcours $parcours */
                    $parcours = $parcoursManager->load($parcoursId);

                    /** @var \App\Entity\EnseignantMention $enseignantmention */
                    $enseignantmention = $enseignantmentionmanager->createObject();
                    $enseignantmention->setEnseignant($enseignant);
                    $enseignantmention->setMention($mention);
                    $enseignantmention->setNiveau($parcours->getNiveau());
                    $enseignantmention->setParcours($parcours);

                    $enseignantmentionmanager->persist($enseignantmention);
                }
                $enseignantmentionmanager->flush();
            }

            return $this->redirectToRoute('front_chefmention_index');

        }

        return $this->render(
            'frontend/chef-mention/editEnseignant2.html.twig',
            [
                'parcours'              => $parcours,
                'tCollectEnsParcoursId' => $tCollectEnsParcoursId,
                'tCollectEnsNiveauId'   => $tCollectEnsNiveauId,
                'form'                  => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="front_chefmention_delete_enseignant", methods={"DELETE"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Enseignant                    $enseignant
     * @IsGranted("ROLE_CHEFMENTION")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function enseignantDelete(Request $request, Enseignant $enseignant) : Response
    {
        if ($this->isCsrfTokenValid('delete' . $enseignant->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($enseignant);
            $entityManager->flush();
        }

        return $this->redirectToRoute('front_chefmention_index');
    }

    /**
     * @Route("/matiere/", name="frontend_chefmention_matiere_index")
     * @param \App\Manager\MentionManager $mentionmanager
     * @param \App\Manager\MatiereManager $matManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @IsGranted("ROLE_CHEFMENTION")
     */
    public function enseignantMatiereList(MentionManager $mentionmanager, MatiereManager $matManager)
    {
        $user     = $this->getUser();
        $matieres = $matManager->getByMention($user->getMention()->getId());

        return $this->render(
            'frontend/chef-mention/index.html.twig',
            [
                'enseignantsEtmatieres' => $matieres
            ]
        );
    }

    /**
     * @Route("/enseignant/matiere/add", name="front_chefmention_create_enseignant_matiere",  methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request                                 $request
     * @param \App\Entity\EnseignantMatiere                                             $enseignantMatiere
     * @param \App\Manager\EnseignantManager                                            $manager
     * @param \App\Manager\EnseignantMatiereManager                                     $enseignantMatieremanager
     * @param \App\Manager\EnseignantMentionManager                                     $enseignantMentionmanager
     * @param \App\Manager\MentionManager                                               $mentionmanager
     * @param \App\Manager\NiveauManager                                                $niveaumanager
     * @param \App\Manager\ParcoursManager                                              $parcoursManager
     * @param \App\Repository\UniteEnseignementsManager                                 $ueManager ,
     * @param \App\Repository\MatiereRepository                                         $matRepository ,
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameter
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function assignMatter(
        Request $request,
        NiveauManager $niveauManager,
        ParcoursManager $parcoursManager,
        EnseignantManager $manager,
        MentionManager $mentionManager,
        EnseignantMentionManager $enseMentionManager,
        MatiereManager $matManager,
        UniteEnseignementsManager $ueManager,
        MatiereRepository $matRepository,
        ParameterBagInterface $parameter
    ) : Response {

        $user       = $this->getUser();
        $resMention = $mentionManager->loadOneBy(["id" => $user->getMention()->getId()]);
        $niveaux    = $niveauManager->loadAll();
        // $matieres = $matManager->getAllByMention($user->getMention()->getId());
        $matieres = [];
        // $uniteEnseignements = $ueManager->getAllByMention($resMention->getId());
        $uniteEnseignements = [];
        // $enseignants = $enseMentionManager->getAllByMention($user->getMention()->getId());    
        $enseignants = [];
        $parcours    = [];


        $enseignantMatiere = $matManager->createObject();
        $form              = $this->createForm(
            MatiereType::class,
            $enseignantMatiere
        );
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $matiereParams = $request->get('matiere');


            if (isset($matiereParams['matiere'])) {

                $enseignant = $manager->loadOneBy(['id' => $matiereParams['enseignantId']]);
                $ue         = $ueManager->loadOneBy(['id' => $matiereParams['ue']]);

                //remove all enseignant matiere before save
                if (count($matiereParams['matiere']) > 0) {
                    $currentEnsMatieres = $matManager->loadBy(
                        [
                            'enseignant'         => $enseignant->getId(),
                            'uniteEnseignements' => $ue->getId()
                        ]
                    );
                    foreach ($currentEnsMatieres as $matiere) {
                        $matiere->setEnseignant(null);
                        $matManager->persist($matiere);
                    }
                    $matManager->flush();
                }

                foreach ($matiereParams['matiere'] as $matId) {
                    $enseignantMatiere = $matManager->loadOneBy(['id' => $matId]);
                    $enseignantMatiere->setEnseignant($enseignant);
                    $matManager->persist($enseignantMatiere);
                }
                if (count($matiereParams['matiere']) > 0) {
                    $matManager->flush();
                }
            }

            return $this->redirectToRoute('frontend_matiere_index');
        }

        return $this->render(
            'frontend/chef-mention/addMatiere.html.twig',
            [
                'niveaux'            => $niveaux,
                'parcours'           => $parcours,
                'enseignants'        => $enseignants,
                'uniteEnseignements' => $uniteEnseignements,
                'matieres'           => $matieres,
                'form'               => $form->createView(),
            ]
        );
    }


    /**
     * @Route("/enseignant/matiere/parent-child/options", name="front_chefmention_enseignant_matiere_ajax_options",  methods={"GET", "POST"})
     * @param Request                               $request
     * @param NiveauManager                         $niveaumanager
     * @param ParcoursManager                       $parcoursManager
     * @param \App\Manager\EnseignantMentionManager $enseMentionManager
     * @param SemestreManager                       $semManager
     * @param UniteEnseignementsManager             $ueManager
     * @param MatiereManager                        $matiereManager
     *
     * @return Response
     */
    public function assignMaterAjaxOptions(
        Request $request,
        NiveauManager $niveaumanager,
        ParcoursManager $parcoursManager,
        EnseignantMentionManager $enseMentionManager,
        SemestreManager $semManager,
        UniteEnseignementsManager $ueManager,
        MatiereManager $matiereManager
    ) {
        $user    = $this->getUser();
        $mention = $user->getMention();

        $parentName  = $request->get('parent_name');
        $parentValue = $request->get('parent_id');
        $childTarget = $request->get('child_target');

        $parcours           = [];
        $uniteEnseignements = [];
        $matieres           = [];
        $enseignants        = [];

        switch ($parentName) {
            case 'niveau':
                if ($childTarget === 'parcours') {
                    $parcours = $parcoursManager->loadBy(['niveau' => $parentValue]);
                }
                if ($childTarget === 'enseignant') {
                    $enseignants = $enseMentionManager->getAllByParams(
                        [
                            'mention' => $mention->getid(),
                            'niveau'  => $parentValue
                        ]
                    );
                }
                break;
            case 'parcours':
                $filterOptions              = [];
                $filterOptions['mention']   = $mention->getId();
                $filterOptions['niveau']    = $request->get('niveau_id');
                $filterOptions[$parentName] = $parentValue;
                $enseignants                = $enseMentionManager->getAllByParams($filterOptions);
                break;
            case 'uniteEnseignements':
                $matieres = $matiereManager->loadBy([$parentName => $parentValue]);
                break;
            default:
                break;
        }

        return $this->render(
            'frontend/chef-mention/matiere/_ajax_options.html.twig',
            [
                'parcours'           => $parcours,
                'enseignants'        => $enseignants,
                'uniteEnseignements' => $uniteEnseignements,
                'matieres'           => $matieres
            ]
        );
    }


    /**
     * @Route("/enseignant/matiere/{id}/edit", name="front_chefmention_edit_enseignant_matiere",  methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request                                 $request
     * @param \App\Entity\EnseignantMatiere                                             $enseignantMatiere
     * @param \App\Manager\EnseignantManager                                            $manager
     * @param \App\Manager\EnseignantMatiereManager                                     $enseignantMatieremanager
     * @param \App\Manager\EnseignantMentionManager                                     $enseignantmentionmanager
     * @param \App\Manager\MentionManager                                               $mentionmanager
     * @param \App\Manager\NiveauManager                                                $niveaumanager
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameter
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function editassignMatter(
        Request $request,
        EnseignantMatiere $enseignantMatiere,
        EnseignantManager $manager,
        EnseignantMentionManager $enseignantmentionmanager,
        MentionManager $mentionmanager,
        NiveauManager $niveaumanager,
        ParameterBagInterface $parameter
    ) : Response {
        //$enseignantMatiere = $enseignantMatieremanager->createObject();
        $form = $this->createForm(EnseignantMatiereType::class, $enseignantMatiere);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->save($enseignantMatiere);

            return $this->redirectToRoute('frontend_matiere_index');
        }


        return $this->render(
            'frontend/chef-mention/editMatiere.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/enseignant/ue", name="front_chefmention_enseignant_matiere",  methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request                                 $request
     * @param \App\Manager\EnseignantManager                                            $ensManager
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameter
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function getUeOptionsByEnseignant(
        Request $request,
        MentionManager $mentionManager,
        EnseignantManager $ensManager,
        UniteEnseignementsManager $ueManager,
        ParameterBagInterface $parameter
    ) : Response {
        $user       = $this->getUser();
        $resMention = $mentionManager->loadOneBy(["id" => $user->getMention()->getId()]);
        $enseignant = $ensManager->loadOneBy(["id" => $request->query->getInt('id')]);
        $ues        = $ueManager->getAllByMentionAndEnseignant($resMention->getId(), $enseignant->getId());

        return $this->render(
            'frontend/chef-mention/_enseignant_ue_options.html.twig',
            [
                'uniteEnseignements' => $ues
            ]
        );
    }

    /**
     * @Route("/enseignant/ue/matiere", name="front_chefmention_enseignant_ue_matiere",  methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request                                 $request
     * @param \App\Manager\EnseignantManager                                            $ensManager
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameter
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function getMatiereOptionsByUniteEns(
        Request $request,
        UniteEnseignementsManager $ueManager,
        MatiereManager $matManager,
        ParameterBagInterface $parameter
    ) : Response {
        $ue       = $ueManager->loadOneBy(['id' => $request->query->getInt('id')]);
        $matieres = $matManager->loadBy(['uniteEnseignements' => $ue]);

        return $this->render(
            'frontend/chef-mention/_ue_matiere_options.html.twig',
            [
                'matieres' => $matieres
            ]
        );
    }

    /**
     * @Route("/gestion-emploi-temps", name="front_chefmention_gestion_emploi_du_temps")
     * @IsGranted("ROLE_CHEFMENTION")
     * @param \App\Manager\EmploiDuTempsManager       $emploiDuTempsManager
     * @param \App\Services\AnneeUniversitaireService $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edtList(
        Request $request,
        EmploiDuTempsManager $emploiDuTempsManager,
        CalendrierPaiementManager   $calPaiementManager,
        CalendrierPaiementRepository $calPaiementRepo,
        AnneeUniversitaireService $anneeUniversitaireService,
        NiveauManager $niveauManager,
        //AnneeUniversitaireService $anneeUniversitaireService,
        PaginatorInterface $paginator
    ){
        /** @var User $user */

        $user          = $this->getUser();

        $c = $request->get('c', '');
        $n = $request->get('n', 0);
        $w = $request->get('w', '');
        $d = $request->get('d', '');
        $month = date('m');
        //$year = date('Y');
        $selectedCalPaiement = $c? $calPaiementManager->load($c) : $calPaiementRepo->findDefault();
        $currentMonth = $selectedCalPaiement->getDateDebut()->format('m');
        $currentYear = $selectedCalPaiement->getDateDebut()->format('Y');
        $monthWeeks = UtilFunctionService::getWeekOfMonth($currentMonth, $currentYear);
        $niveaux = $niveauManager->loadAll();

        $emploiDuTemps = $emploiDuTempsManager->getByMentionAndOrCollegeYear(
            $anneeUniversitaireService->getCurrent(), $user->getMention()
        );
        $calPaiements = $calPaiementManager->loadAll();
        $anneeUniv = $anneeUniversitaireService->getCurrent();
        $emploiDuTemps = $emploiDuTempsManager->getListActive($user->getMention()->getId(), null, $selectedCalPaiement, $n, $w, $d);
        


        // Paginer les données
        $page = $request->query->getInt('page', 1);
        $pagination = $paginator->paginate(
            $emploiDuTemps, // Vos données à paginer
            $page, // Numéro de la page
            $itemsPerPage=20 // Nombre d'éléments par page
        );

        return $this->render(
            'frontend/chef-mention/edtList.html.twig',
            [
                'c'              => $selectedCalPaiement->getId(),
                'workspaceTitle' => 'Chef de mention',
                'emploiDuTemps'  => $emploiDuTemps,
                'calPaiements'   => $calPaiements,
                'pagination'    => $pagination,
                'workspacePath'  => 'front_chefmention_presence_enseignant_index',
                'edtPath'        => 'front_chefmention_gestion_emploi_du_temps',
                'niveaux'        => $niveaux,
                'monthWeeks'     => $monthWeeks,
                'n'              => $n,
                'w'              => $w,
                'd'              => $d
            ]
        );
    }

    /**
     * @Route("/gestion-emploi-temps/add", name="front_chefmention_gestion_emploi_du_temps_add",  methods={"GET", "POST"})
     * @IsGranted("ROLE_CHEFMENTION")
     * @param \Symfony\Component\HttpFoundation\Request                                 $request
     * @param \App\Entity\EnseignantMatiere                                             $enseignantMatiere
     * @param \App\Manager\EnseignantManager                                            $manager
     * @param \App\Manager\EnseignantMatiereManager                                     $enseignantMatieremanager
     * @param \App\Manager\EnseignantMentionManager                                     $enseignantMentionmanager
     * @param \App\Manager\MentionManager                                               $mentionmanager
     * @param \App\Manager\NiveauManager                                                $niveaumanager
     * @param \App\Repository\UniteEnseignementsManager                                 $ueManager ,
     * @param \App\Repository\MatiereRepository                                         $matRepository ,
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameter
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function edtNew(
        Request $request,
        EnseignantManager $manager,
        MentionManager $mentionManager,
        ParcoursManager $parcoursManager,
        EnseignantMentionManager $enseMentionManager,
        MatiereManager $matManager,
        EmploiDuTempsManager $emploiDuTempsManager,
        NiveauManager $niveaumanager,
        UniteEnseignementsManager $ueManager,
        MatiereRepository $matRepository,
        SallesManager $sallesManager,
        ParameterBagInterface $parameter,
        AnneeUniversitaireService $anneeUniversitaireService
    ) : Response {
        $user     = $this->getUser();
        $mention  = $mentionManager->loadOneBy(["id" => $user->getMention()->getId()]);
        $parcours = $parcoursManager->loadBy(['mention' => $mention]);
        $niveaux  = $niveaumanager->loadAll();

        /** @var EmploiDuTemps $emploiDuTemps */
        $emploiDuTemps = $emploiDuTempsManager->createObject();
        $emploiDuTemps->setMention($mention);
        $form = $this->createForm(
            EmploiDuTempsType::class,
            $emploiDuTemps
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $emploiDuTemps->setCreatedAt(new \DateTime());
            $emploiDuTemps->setAnneeUniversitaire($anneeUniversitaireService->getCurrent());
            $emploiDuTempsManager->save($emploiDuTemps);

            return $this->redirectToRoute('front_chefmention_gestion_emploi_du_temps');
        }

        return $this->render(
            'frontend/chef-mention/edtAdd.html.twig',
            [
                'parcours'           => $parcours,
                'niveaux'            => $niveaux,
                'uniteEnseignements' => [],
                'matieres'           => [],
                'semestres'          => [],
                'form'               => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/gestion-emploi-temps/{id}/edit", name="front_chefmention_gestion_emploi_du_temps_edit",  methods={"GET", "POST"})
     * @IsGranted("ROLE_CHEFMENTION")
     * @param \Symfony\Component\HttpFoundation\Request                                 $request
     * @param \App\Manager\MentionManager                                               $mentionmanager
     * @param \App\Manager\NiveauManager                                                $niveaumanager
     * @param \App\Repository\UniteEnseignementsManager                                 $ueManager ,
     * @param \App\Repository\MatiereRepository                                         $matRepository ,
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameter
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function edtEdit(
        Request $request,
        MentionManager $mentionManager,
        NiveauManager $niveaumanager,
        SemestreManager $semestreManager,
        ParcoursManager $parcoursManager,
        MatiereManager $matManager,
        UniteEnseignementsManager $ueManager,
        MatiereRepository $matRepository,
        SallesManager $sallesManager,
        ParameterBagInterface $parameter,
        EmploiDuTemps $emploiDuTemps
    ) : Response {
        $user    = $this->getUser();
        $mention = $mentionManager->loadOneBy(["id" => $user->getMention()->getId()]);

        $niveaux               = $niveaumanager->loadAll();
        $semestres             = $semestreManager->loadBy(['niveau' => $emploiDuTemps->getNiveau()]);
        $parcours              = $parcoursManager->loadBy(['mention' => $mention]);
        $ueOptions             = [];
        $ueOptions['mention']  = $mention;
        $ueOptions['niveau']   = $emploiDuTemps->getNiveau();
        $ueOptions['semestre'] = $emploiDuTemps->getSemestre();
        if ($parcours = $emploiDuTemps->getParcours()) {
            $ueOptions['parcours'] = $parcours;
        }
        $uniteEnseignements = $ueManager->loadBy($ueOptions);
        $matieres           = $matManager->loadBy(['uniteEnseignements' => $emploiDuTemps->getUe()]);
        $dateSchedule       = date_format($emploiDuTemps->getDateSchedule(), "Y-m-d");
        $startTime          = date_format($emploiDuTemps->getStartTime(), "H:i");
        $endTime            = date_format($emploiDuTemps->getEndTime(), "H:i");
        // $salles             = $sallesManager->getAllByMixedFilters(
        //     [
        //         'date'            => $dateSchedule,
        //         'startTime'       => $startTime,
        //         'endTime'         => $endTime,
        //         'capacite'        => $emploiDuTemps->getSalles()->getCapacite(),
        //         'connexion'       => $emploiDuTemps->getSalles()->getInternetConnexionOn(),
        //         'videoProjecteur' => $emploiDuTemps->getSalles()->getVideoProjecteurOn()
        //     ]
        // );

        $salles = $sallesManager->getEdtSalle($emploiDuTemps->getId());

        
        $form               = $this->createForm(
            EmploiDuTempsType::class,
            $emploiDuTemps,
            [
                'emploiDuTemps' => $emploiDuTemps,
                'em'            => $this->getDoctrine()->getManager()
            ]
        );

        // dump($form);die;

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $emploiDuTemps->setUpdatedAt(new \DateTime());
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('front_chefmention_gestion_emploi_du_temps');
        } else {
            dump($form);
        }

        return $this->render(
            'frontend/chef-mention/edtEdit.html.twig',
            [
                'niveaux'            => $niveaux,
                'semestres'          => $semestres,
                'parcours'           => $parcours,
                'uniteEnseignements' => $uniteEnseignements,
                'matieres'           => $matieres,
                'salles'             => $salles,
                'currentSalle'       => $emploiDuTemps->getSalles()->getId(),
                'currentMatiere'     => $emploiDuTemps->getMatiere()->getId(),
                'form'               => $form->createView(),
                'emploiDuTemps'      => $emploiDuTemps,
            ]
        );
    }

    /**
     * @Route("/gestion-emploi-temps/{id}/delete", name="front_chefmention_gestion_emploi_du_temps_delete",  methods={"GET", "POST"})
     * @IsGranted("ROLE_CHEFMENTION")
     * @param \Symfony\Component\HttpFoundation\Request                                 $request
     * @param \App\Entity\EnseignantMatiere                                             $enseignantMatiere
     * @param \App\Manager\EnseignantManager                                            $manager
     * @param \App\Manager\EnseignantMatiereManager                                     $enseignantMatieremanager
     * @param \App\Manager\EnseignantMentionManager                                     $enseignantMentionmanager
     * @param \App\Manager\MentionManager                                               $mentionmanager
     * @param \App\Manager\NiveauManager                                                $niveaumanager
     * @param \App\Repository\UniteEnseignementsManager                                 $ueManager ,
     * @param \App\Repository\MatiereRepository                                         $matRepository ,
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameter
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function edtDelete(
        Request $request,
        EnseignantManager $manager,
        ParameterBagInterface $parameter,
        EmploiDuTemps $emploiDuTemps
    ) : Response {
        if ($this->isCsrfTokenValid('delete' . $emploiDuTemps->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($emploiDuTemps);
            $entityManager->flush();
            $this->addFlash('success', 'Succès suppression');
        }

        return $this->redirectToRoute('front_chefmention_gestion_emploi_du_temps');
    }

    /**
     * @Route("/niveaux/effectif", name="front_chefmention_edt_niveaux",  methods={"GET", "POST"})
     * @IsGranted("ROLE_CHEFMENTION")
     * @param Request                   $request
     * @param MentionManager            $mentionManager
     * @param EnseignantManager         $ensManager
     * @param UniteEnseignementsManager $ueManager
     * @param SemestreManager           $semestreManager
     * @param EtudiantManager           $etudiantManager
     * @param SallesManager             $sallesManager
     * @param EmploiDuTempsManager      $emploiDuTempsManager
     * @param ParameterBagInterface     $parameter
     *
     * @return Response
     */
    public function edtEffectifNiveaux(
        Request $request,
        MentionManager $mentionManager,
        EnseignantManager $ensManager,
        UniteEnseignementsManager $ueManager,
        SemestreManager $semestreManager,
        EtudiantManager $etudiantManager,
        SallesManager $sallesManager,
        EmploiDuTempsManager $emploiDuTempsManager,
        ParameterBagInterface $parameter
    ) : Response {
        $user       = $this->getUser();
        $mention    = $mentionManager->loadBy(["id" => $request->query->getInt('mentionId')]);
        $mentionId  = $request->query->getInt('mentionId');
        $niveauId   = $request->query->getInt('niveauId');
        $salles     = $sallesManager->loadAll();
        $sallesList = $emploiDuTempsManager->loadBy(
            [
                "id" => $mentionId
            ]
        );
        //Chercher l'effectif des étudiants par mention et par niveaux...
        $effectifs = $etudiantManager->countAllByMentionAndNiveau($mentionId, $niveauId);

        return $this->render(
            'frontend/chef-mention/edtEffectif.html.twig',
            [
                'salles'     => $salles,
                'sallesList' => $sallesList,
                'mention'    => $mention,
                'effectifs'  => $effectifs,
                'niveauId'   => $request->get('niveauId')
            ]
        );
    }

    /**
     * @Route("/salles/ue", name="front_chefmention_edt_salles",  methods={"GET", "POST"})
     * @IsGranted("ROLE_CHEFMENTION")
     * @param Request                   $request
     * @param MentionManager            $mentionManager
     * @param EnseignantManager         $ensManager
     * @param UniteEnseignementsManager $ueManager
     * @param SemestreManager           $semestreManager
     * @param SallesManager             $sallesManager
     * @param EmploiDuTempsManager      $emploiDuTempsManager
     * @param EtudiantManager           $etudiantManager
     * @param ParameterBagInterface     $parameter
     *
     * @return Response
     */
    public function edtGetUeOptionsBySalles(
        Request $request,
        MentionManager $mentionManager,
        EnseignantManager $ensManager,
        UniteEnseignementsManager $ueManager,
        SemestreManager $semestreManager,
        SallesManager $sallesManager,
        EmploiDuTempsManager $emploiDuTempsManager,
        EtudiantManager $etudiantManager,
        ParameterBagInterface $parameter
    ) : Response {
        //Post datas
        $dateSchedule = $request->get('dateSchedule');
        $startTime    = $request->get('startTime');
        $endTime      = $request->get('endTime');
        $mentionId    = $request->query->getInt('mentionId');
        $niveauId     = $request->query->getInt('niveauId');

        $connexion       = $request->query->getInt('connexion');
        $videoProjecteur = $request->query->getInt('videoProjecteur');

        // dump($dateSchedule);die;

        $effectifs = $etudiantManager->countAllByMentionAndNiveau($mentionId, $niveauId);
        $params    = [
            "dateSchedule" => $dateSchedule,
            "startTime"    => $startTime,
            "endTime"      => $endTime,
            "capacite"     => $effectifs
        ];
        // $sallesList   = $emploiDuTempsManager->getEdtByParams($params);

        // $reservedClass = array();
        // foreach ($sallesList as $key => $value) {
        //     $reservedClass[] = $value["salles_id"];
        // }
        // $salles       = $sallesManager->getSallesByParams(implode(',', $reservedClass));

        $salles = $sallesManager->getAllByMixedFilters(
            [
                'date'            => $dateSchedule,
                'startTime'       => $startTime,
                'endTime'         => $endTime,
                'capacite'        => $effectifs,
                'connexion'       => $connexion,
                'videoProjecteur' => $videoProjecteur
            ]
        );

        return $this->render(
            'frontend/chef-mention/_salle_ue_options.html.twig',
            [
                'salles'    => $salles,
                'effectifs' => $effectifs
                // 'sallesList'   => $sallesList,
                // 'reservedClass' => $reservedClass
            ]
        );
    }

    /**
     * @Route("/edt/parent-child/options", name="front_chefmention_edt_ajax_options",  methods={"GET", "POST"})
     * @param Request                   $request
     * @param NiveauManager             $niveaumanager
     * @param SemestreManager           $semManager
     * @param UniteEnseignementsManager $ueManager
     * @param MatiereManager            $matiereManager
     *
     * @return Response
     */
    public function edtAjaxOptions(
        Request $request,
        NiveauManager $niveaumanager,
        SemestreManager $semManager,
        UniteEnseignementsManager $ueManager,
        MatiereManager $matiereManager
    ) {
        $user    = $this->getUser();
        $mention = $user->getMention();

        $parentName  = $request->get('parent_name');
        $parentValue = $request->get('parent_id');
        $parcoursId  = $request->get('parcours_id');

        $mentions           = [];
        $parcours           = [];
        $uniteEnseignements = [];
        $matieres           = [];
        $niveauId           = $request->get('niveau_id');

        $semestres = [];
        switch ($parentName) {
            case 'niveau':
                $semestres = $semManager->loadBy(['niveau' => $parentValue]);
                break;
            case 'semestre':
                $filterOptions              = [];
                $filterOptions['mention']   = $mention->getId();
                $filterOptions['niveau']    = $niveauId;
                $filterOptions[$parentName] = $parentValue;
                if (!empty($parcoursId)) {
                    $filterOptions['parcours'] = $parcoursId;
                }
                $uniteEnseignements = $ueManager->loadBy($filterOptions);
                break;
            case 'uniteEnseignements':
                $matieres = $matiereManager->loadBy([$parentName => $parentValue]);
                break;
            default:
                break;
        }

        return $this->render(
            'frontend/chef-mention/edt/_ajax_options.html.twig',
            [
                'mentions'           => $mentions,
                'parcours'           => $parcours,
                'semestres'          => $semestres,
                'uniteEnseignements' => $uniteEnseignements,
                'matieres'           => $matieres
            ]
        );
    }

    /**
     * @Route("/classroom", name="front_chefmention_classroom")
     * @IsGranted("ROLE_CHEFMENTION")
     * @param EnseignantManager        $manager
     * @param EnseignantMentionManager $enseMentionManager
     * @param MentionManager           $mentionmanager
     */
    public function salleList(
        EnseignantManager $manager,
        EnseignantMentionManager $enseMentionManager,
        MentionManager $mentionmanager,
        EmploiDuTempsManager $emploiDuTempsManager,
        SallesManager $sallesManager,
        EmploiDuTempsRepository $emploiDuTempsRepository

    ) {
        $salles = $sallesManager->loadAll();

        return $this->render(
            'frontend/chef-mention/salleList.html.twig',
            [
                'salles' => $salles
            ]
        );
    }

    /**
     * @Route("/classroom/add", name="front_chefmention_classroom_add",  methods={"GET", "POST"})
     * @param Request               $request
     * @param SallesManager         $sallesManager
     * @param ParameterBagInterface $parameter
     *
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */

    public function salleNew(
        Request $request,
        SallesManager $sallesManager,
        ParameterBagInterface $parameter
    ) : Response {

        $user   = $this->getUser();
        $salles = $sallesManager->loadAll();
        $salle  = $sallesManager->createObject();
        $form   = $this->createForm(
            SallesType::class,
            $salle
        );

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $salle->setUuid(Uuid::uuid4());
            $salle->setCreatedAt(new \DateTime());
            $sallesManager->save($salle);

            return $this->redirectToRoute('front_classroom');
        }

        return $this->render(
            'frontend/chef-mention/salleAdd.html.twig',
            [
                'salles' => $salles,
                'form'   => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/classroom/{id}/edit", name="front_chefmention_edit_salle",  methods={"GET", "POST"})
     * @param Request               $request
     * @param SallesManager         $sallesManager
     * @param ParameterBagInterface $parameter
     *
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */

    public function salleEdit(
        Request $request,
        SallesManager $sallesManager,
        Salles $salles,
        ParameterBagInterface $parameter
    ) : Response {

        $form = $this->createForm(SallesType::class, $salles);
        $form->handleRequest($request);

        $user = $this->getUser();
        $form = $this->createForm(
            SallesType::class,
            $salles
        );

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('front_classroom');
        }

        return $this->render(
            'frontend/chef-mention/salleEdit.html.twig',
            [
                'salles' => $salles,
                'form'   => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/vacations/list", name="front_chefmention_presence_enseignant_index", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request   $request
     * @param \App\Manager\FichePresenceEnseignantManager $ficheEnseignantManager
     * @param \App\Services\AnneeUniversitaireService     $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @IsGranted("ROLE_CHEFMENTION")
     */
    public function fichePresenceEnseignantList(
        Request                         $request, 
        EmploiDuTempsManager            $edtManager,
        CalendrierPaiementManager       $calPaiementManager,
        CalendrierPaiementRepository    $calPaiementRepo,
        AnneeUniversitaireService       $anneeUniversitaireService,
        WorkflowStatutService           $workflowStatService,
        MentionManager                  $menManager)
    {
        $c = $request->get('c', '');
        $m = $request->get('m', '');
        $selectedCalPaiement = $c? $calPaiementManager->load($c) : $calPaiementRepo->findDefault();
        $user               = $this->getUser();
        $calPaiements = $calPaiementManager->loadAll();
        $mentions = $menManager->loadBy(
            [
                'diminutif' => $user->getMention()->getDiminutif()
            ],[]);
        $currentAnneUniv    = $anneeUniversitaireService->getCurrent();
        $profilListStatut = $workflowStatService->getStatutForListByProfil($user->getRoles());
        $profilNextStatut = $workflowStatService->getResourceNextStatut($profilListStatut);
        $profilPrevStatut = $workflowStatService->getEdtPreviousStatut($profilListStatut);
        $fPresenceEnseignants = $edtManager->getCurrentVacation(
            $currentAnneUniv->getId(),
            $user->getMention()->getId(),
            $selectedCalPaiement
        );

        return $this->render(
            'frontend/chef-mention/presence-enseignant-list.html.twig',
            [
                'c'                     => $selectedCalPaiement ? $selectedCalPaiement->getId() : 0,
                'm'                     => $m,
                'calPaiements'          => $calPaiements,
                'mentions'              => $mentions,
                'fPresenceEnseignants'  => $fPresenceEnseignants,
                'profilListStatut'      => $profilListStatut,
                'profilNextStatut'      => $profilNextStatut,
                'profilPrevStatut'      => $profilPrevStatut
            ]
        );
    }

    /**
     * @Route("/vacations/enseignant/{id}", name="front_cm_vacation_enseignant_index", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Enseignant $enseignant
     * @param \App\Manager\EmploiDuTempsManager $edtManager
     * @param \App\Service\AnneeUniversitaireService $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function vacationEnseignantList(
        Request                         $request, 
        Enseignant                      $enseignant,
        EmploiDuTempsManager            $edtManager,
        CalendrierPaiementManager       $calPaiementManager,
        AnneeUniversitaireService       $anneeUniversitaireService,
        WorkflowStatutService           $workflowStatService)
    {
        $user               = $this->getUser();
        $c = $request->get('c', '');
        $m = $user->getMention()->getId();
        $selectedCalPaiement = $c? $calPaiementManager->load($c) : "";
        $calPaiements = $calPaiementManager->loadAll();
        $currentAnneUniv    = $anneeUniversitaireService->getCurrent();
        $profilListStatut = $workflowStatService->getStatutForListByProfil($user->getRoles());
        $profilNextStatut = $workflowStatService->getResourceNextStatut($profilListStatut);
        $profilPrevStatut = $workflowStatService->getEdtPreviousStatut($profilListStatut);
        $fPresenceEnseignants = $edtManager->getEnseignantVacation(
            $enseignant->getId(),
            $currentAnneUniv->getId(),
            $user->getMention()->getId(),
            $selectedCalPaiement
        );

        return $this->render(
            'frontend/chef-mention/vacation/enseignant-index.html.twig',
            [
                'c'                     => $c,
                'm'                     => $m,
                'enseignant'            => $enseignant,
                'calPaiement'           => $selectedCalPaiement,
                'fPresenceEnseignants'  => $fPresenceEnseignants,
                'profilListStatut'  => $profilListStatut,
                'profilNextStatut'  => $profilNextStatut,
                'profilPrevStatut'  => $profilPrevStatut
            ]
        );
    }  

    /**
     * @Route("/vacation/enseignant/{id}/edit", name="front_cm_vacation_enseignant_edit", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Enseignant $enseignant
     * @param \App\Manager\EmploiDuTempsManager $edtManager
     * @param \App\Service\AnneeUniversitaireService $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function vacationEnseignantEdit (
        Request                         $request, 
        EmploiDuTemps                   $edt,
        EmploiDuTempsManager            $edtManager,
        MatiereManager                  $matManager, 
        CalendrierPaiementManager       $calPaiementManager,
        ParameterBagInterface           $parameter,
        AnneeUniversitaireService       $anneeUniversitaireService,
        WorkflowStatutService           $workflowStatService) : Response
    {
        $c = $request->get('c', '');
        $user             = $this->getUser();
        $currentUserRoles = $user->getRoles();
        $edtNextStatut = $workflowStatService->getResourceNextStatut($edt->getStatut());
        $edtPreviousStatut = $workflowStatService->getEdtPreviousStatut($edt->getStatut());

        if($request->isMethod('POST')) {
            $edtFormData = $request->get('emploi_du_temps', array());
            $edtTroncCommunSiblings = $edtManager->loadby(
                [
                    'dateSchedule'  => $edt->getDateSchedule(),
                    'startTime'     => $edt->getStartTime(),
                    'endTime'       => $edt->getEndTime(),
                    'salles'        => $edt->getSalles()
                ]
            );
            foreach ($edtTroncCommunSiblings as $edt) {
                $edt->setStatut($edtFormData['statut']);
                $edt->setCommentaire($edtFormData['commentaire']);
                $edtManager->persist($edt);
            }
            $edtManager->flush();

            return $this->redirectToRoute('front_chefmention_presence_enseignant_index');
        }
        
        return $this->render(
            'frontend/chef-mention/vacation/enseignant-edit.html.twig',
            [
                'c'             => $c,
                'edt'           => $edt,
                'edtNextStatut' => $edtNextStatut,
                'edtPreviousStatut' => $edtPreviousStatut
            ]
        );
    }    

    /**
     * IsGranted("ROLE_CHEFMENTION")
     * @Route("/validate/vacations/", name="front_cm_vacation_validation", methods={"POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\PrestationManager            $prestationManager
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     * @param \App\Manager\MentionManager               $mentionManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function validateVacation(
        Request                     $request,
        MentionManager              $mentionmanager,
        EmploiDuTempsManager        $edtManager,
        CalendrierPaiementManager   $calPaiementManager,
        WorkflowStatutService       $workflowStatService,
        AnneeUniversitaireService   $anneeUniversitaireService
    ) {
        $mention = $this->getUser()->getMention();
        $vacationIds = $request->get('presence', array());
        $profilListStatut = $workflowStatService->getStatutForListByProfil($this->getUser()->getRoles());
        $entityManager = $this->getDoctrine()->getManager();
        foreach($vacationIds as $id) {
            $vacation = $edtManager->load($id);
            $edtTroncCommunSiblings = $edtManager->loadby(
                [
                    'dateSchedule'  => $vacation->getDateSchedule(),
                    'startTime'     => $vacation->getStartTime(),
                    'endTime'       => $vacation->getEndTime(),
                    'salles'        => $vacation->getSalles()
                ]
            );
            foreach ($edtTroncCommunSiblings as $currVacation) {
                $currVacNextStatut = $currVacation->getStatut() == $profilListStatut ? 
                $workflowStatService->getResourceNextStatut($profilListStatut) :
                $workflowStatService->getEdtPreviousStatut($currVacation->getStatut());
                $currVacation->setStatut($currVacNextStatut);
                $entityManager->persist($currVacation);

                $vacationHistory = new PaiementHistory();
                $vacationHistory->setResourceName(PaiementHistory::VACATION_RESOURCE);
                $vacationHistory->setStatut($currVacNextStatut);
                $vacationHistory->setResourceId($currVacation->getId());
                $vacationHistory->setValidator($this->getUser());
                $vacationHistory->setCreatedAt(new \DateTime());
                $vacationHistory->setMontant(0);
                $vacationHistory->setUpdatedAt(new \DateTime());
                $entityManager->persist($vacationHistory);
            }
        };
        $entityManager->flush();
        
        return new JsonResponse(array('statut' => '200'));
    }

    /**
     * @Route("/presence-enseignant/matiere/{id}/fiche", name="front_chefmention_presence_enseignant_matiere_fiche", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request   $request
     * @param \App\Entity\Matiere                         $matiere
     * @param \App\Manager\FichePresenceEnseignantManager $ficheEnseignantManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @IsGranted("ROLE_CHEFMENTION")
     */
    public function fichePresenceEnseignantFiche(
        Request $request,
        Matiere $matiere,
        FichePresenceEnseignantManager $ficheEnseignantManager
    ) {
        $fPresenceEnseignants = $ficheEnseignantManager->getTeacherMatiereDetailHour($matiere->getId());

        return $this->render(
            'frontend/chef-mention/presence-enseignant-fiche.html.twig',
            [
                'matiere'              => $matiere,
                'fPresenceEnseignants' => $fPresenceEnseignants
            ]
        );
    }

    /**
     * @IsGranted("ROLE_CHEFMENTION")
     * @Route("/presence-enseignant/edit/{id}", name="front_chefmention_presence_enseignant_edit", methods={"GET", "POST"})
     * @param Request                                               $request
     * @param FichePresenceEnseignant                               $fichePresenceEnseignant
     * @param FichePresenceEnseignantManager                        $ficheEnseignantManager
     * @param EnseignantManager                                     $enseignantManager
     * @param MentionManager                                        $mentionManager
     * @param MatiereManager                                        $matiereManager
     * @param DepartementManager                                    $departementManager
     * @param UniteEnseignementsManager                             $ueManager
     * @param NiveauManager                                         $niveauManager
     * @param \App\Manager\ParcoursManager                          $parcoursManager
     * @param \App\Manager\FichePresenceEnseignantHistoriqueManager $historiqueManager
     *
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function fichePresenceEnseignantEdit(
        Request $request,
        FichePresenceEnseignant $fichePresenceEnseignant,
        FichePresenceEnseignantManager $ficheEnseignantManager,
        EnseignantManager $enseignantManager,
        MentionManager $mentionManager,
        MatiereManager $matiereManager,
        DepartementManager $departementManager,
        UniteEnseignementsManager $ueManager,
        NiveauManager $niveauManager,
        ParcoursManager $parcoursManager,
        FichePresenceEnseignantHistoriqueManager $historiqueManager

    ) : Response {
        $mention     = $mentionManager->loadOneBy(["id" => $fichePresenceEnseignant->getMention()->getId()]);
        $enseignant  = $enseignantManager->loadOneBy(["id" => $fichePresenceEnseignant->getEnseignant()->getId()]);
        $matiere     = $matiereManager->loadOneBy(["id" => $fichePresenceEnseignant->getMatiere()->getId()]);
        $departement = $departementManager->loadOneBy(["id" => $fichePresenceEnseignant->getDomaine()->getId()]);
        $ue          = $ueManager->loadOneBy(["id" => $fichePresenceEnseignant->getUe()->getId()]);
        $niveau      = $niveauManager->loadOneBy(["id" => $fichePresenceEnseignant->getNiveau()->getId()]);

        $form = $this->createForm(
            FichePresenceEnseignantType::class,
            $fichePresenceEnseignant,
            [
                'fichePresenceEnseignant' => $fichePresenceEnseignant,
                'em'                      => $this->getDoctrine()->getManager()
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $fichePresenceEnseignant->setEnseignant($enseignant);
            $fichePresenceEnseignant->setMention($mention);
            $fichePresenceEnseignant->setMatiere($matiere);
            $fichePresenceEnseignant->setDomaine($departement);
            $fichePresenceEnseignant->setUe($ue);
            $fichePresenceEnseignant->setNiveau($niveau);

            $fichePresenceEnseignant->setUpdatedAt(new \DateTime());
            $fichePresenceEnseignant->setStatut('CM_VALIDATED');
            $this->getDoctrine()->getManager()->flush();

            /** @var \App\Entity\FichePresenceEnseignantHistorique $historical */
            $historical = $historiqueManager->createObject();
            $historical->setUser($this->getUser());
            $historical->setFichePresenceEnseignant($fichePresenceEnseignant);
            $historical->setStatus('CM_VALIDATED');
            $historiqueManager->save($historical);

            return $this->redirectToRoute('front_chefmention_presence_enseignant_index');
        } else {
            dump($form);
        }

        return $this->render(
            'frontend/chef-mention/presence-enseignant-edit.html.twig',
            [
                'fpEnseignant' => $fichePresenceEnseignant,
                'form'         => $form->createView()
            ]
        );
    }

    /**
     * @Route("/list-demande-doc", name="front_chefmention_demande_doc_list")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\DemandeDocManager            $demandeDocManager
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @IsGranted("ROLE_CHEFMENTION")
     */
    public function demandeDocList(
        Request $request,
        DemandeDocManager $demandeDocManager,
        EtudiantManager $etudiantManager
    ) {
        $currentUser  = $this->getUser();
        $etudiants    = $etudiantManager->loadBy(
            [
                'mention' => $currentUser->getMention()->getId(),
            ]
        );
        $etudiantList = [];
        foreach ($etudiants as $key => $value) {
            $etudiantList[] = $value->getId();
        }
        $demandeDoc = $demandeDocManager->getDemandeDocByParams(
            implode(',', $etudiantList), DemandeDoc::TYPE_LETTRE_INTRO
        );

        return $this->render(
            'frontend/chef-mention/demande-doc-list.html.twig',
            [
                'demandesDoc' => $demandeDoc
            ]
        );
    }

    /**
     * @Route("/edit-demande-doc/{id}/edit", name="front_chefmention_demande_doc_edit", methods="GET|POST")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\DemandeDocManager            $demandeDocManager
     * @param \App\Entity\DemandeDoc                    $demandeDoc
     * @param \App\Manager\DemandeDocHistoriqueManager  $historiqueManager
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @IsGranted("ROLE_CHEFMENTION")
     */
    public function demandeDocEdit(
        Request $request,
        DemandeDocManager $demandeDocManager,
        DemandeDoc $demandeDoc,
        DemandeDocHistoriqueManager $historiqueManager
    ) {
        $form = $this->createForm(
            DemandeType::class,
            $demandeDoc,
            [
                'demande' => $demandeDoc,
                'em'      => $this->getDoctrine()->getManager()
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $extraData = $form->getExtraData();
            $demandeDoc->setStatut($extraData['status']);
            $this->getDoctrine()->getManager()->flush();

            /** @var \App\Entity\DemandeDocHistorique $historical */
            $historical = $historiqueManager->createObject();
            $historical->setUser($this->getUser());
            $historical->setStatus($extraData['status']);
            $historical->setDemandeDoc($demandeDoc);
            $historiqueManager->save($historical);

            if ($extraData['status'] == "VALIDATED") {
                $this->demandeDocToPdf($request, $demandeDocManager, $demandeDoc);
            }

            return $this->redirectToRoute('front_chefmention_demande_doc_list');
        }

        $template = ($demandeDoc->getType()->getCode(
            ) == "LETTRE_INTRODUCTION") ? "frontend/chef-mention/demande-doc.html.twig" : "frontend/chef-mention/demande-doc-status.html.twig";

        return $this->render(
            $template,
            [
                'demandeDoc' => $demandeDoc,
                'form'       => $form->createView()
            ]
        );
    }

    /**
     * @Route("/edit-demande-doc/status", name="front_chefmention_demande_doc_status", methods="GET|POST")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\DemandeDocManager            $demandeDocManager
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function demandeDocStatus(
        Request $request,
        DemandeDocManager $demandeDocManager
    ) {
        $user   = $this->getUser();
        $docId  = $request->get('docId');
        $status = $request->get('status');

        $demandeDoc = $demandeDocManager->loadOneBy(["id" => $docId]);
        $demandeDoc->setStatut($status);
        $demandeDocManager->persist($demandeDoc);
        $demandeDocManager->flush();

        if ($status == "VALIDATED") {
            $this->demandeDocToPdf($request, $demandeDocManager, $demandeDoc);
        }

        return $this->render(
            'frontend/chef-mention/demande-doc-list.html.twig',
            [
                'demandesDoc' => $demandeDoc,
            ]
        );
    }

    /**
     * @param Request           $request
     * @param DemandeDocManager $demandeDocManager
     * @param DemandeDoc        $demandeDoc
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function demandeDocToPdf(
        Request $request,
        DemandeDocManager $demandeDocManager,
        DemandeDoc $demandeDoc
    ) {

        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView(
            'frontend/demandedoc/lettre_introdution.html.twig',
            [
                'site'       => $this->getParameter('site'),
                'demandeDoc' => $demandeDoc,
            ]
        );

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Store PDF Binary Data
        $output = $dompdf->output();

        // In this case, we want to write the file in the public directory
        $uploadDir = $this->getParameter('demande_directory');

        $filename    = 'demande-' . $demandeDoc->getId() . '-' . $demandeDoc->getMatricule() . '.pdf';
        $pdfFilepath = $uploadDir . '/' . $filename;


        // Write file to the desired path
        file_put_contents($pdfFilepath, $output);
    }

    /**
     * @Route("/calendrier-soutenances/{id}/edit", name="front_cm_thesis_calendar_edit", methods={"GET", "POST"})
     * @IsGranted("ROLE_CHEFMENTION")
     * @param \Symfony\Component\HttpFoundation\Request          $request
     * @param \App\Entity\CalendrierSoutenance                   $calendrierSoutenance
     * @param \App\Manager\CalendrierSoutenanceManager           $manager
     * @param \App\Manager\CalendrierSoutenanceHistoriqueManager $historicalManager
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function thesisCalendarEdit(
        Request $request,
        CalendrierSoutenance $calendrierSoutenance,
        CalendrierSoutenanceManager $manager,
        CalendrierSoutenanceHistoriqueManager $historicalManager
    ) {
        $user = $this->getUser();
        $form = $this->createForm(
            ThesisCalendarType::class,
            $calendrierSoutenance,
            [
                'calendar' => $calendrierSoutenance,
                'user'     => $user,
                'em'       => $this->getDoctrine()->getManager()
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->save($calendrierSoutenance);

            $user = $this->getUser();

            /** @var \App\Entity\CalendrierSoutenanceHistorique $historical */
            $historical = $historicalManager->getInstance(
                $calendrierSoutenance, $user, $calendrierSoutenance->getStatus()
            );
            $historical->setUser($user);
            $historical->setStatus($calendrierSoutenance->getStatus());
            $historical->setCalendrierSoutenance($calendrierSoutenance);
            $historical->setCreatedAt(new \DateTime());
            $historicalManager->save($historical);

            return $this->redirectToRoute('front_cm_thesis_calendar_list');
        }

        return $this->render(
            'frontend/chef-mention/thesis/new.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/calendrier-soutenances", name="front_cm_thesis_calendar_list", methods={"GET"})
     * @IsGranted("ROLE_CHEFMENTION")
     * @param \App\Manager\CalendrierSoutenanceManager $calendrierSoutenanceManager
     * @param \App\Services\AnneeUniversitaireService  $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function thesisCalendarList(
        CalendrierSoutenanceManager $calendrierSoutenanceManager,
        AnneeUniversitaireService $anneeUniversitaireService
    ) {
        $calendarList = $calendrierSoutenanceManager->loadBy(
            [
                'anneeUniversitaire' => $anneeUniversitaireService->getCurrent()
            ],
            [
                'dateSchedule' => 'ASC'
            ]
        );

        return $this->render(
            'frontend/chef-mention/thesis/index.html.twig',
            [
                'items' => $calendarList
            ]
        );
    }

    /**
     * @Route("/calendrier-examen/ajout", name="front_cm_examen_calendar_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_CHEFMENTION")
     * @param \Symfony\Component\HttpFoundation\Request      $request
     * @param \App\Manager\CalendrierExamenManager           $manager
     * @param \App\Services\AnneeUniversitaireService        $anneeUniversitaireService
     * @param \App\Manager\CalendrierExamenHistoriqueManager $historicalManager
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function examenCalendarNew(
        Request $request,
        CalendrierExamenManager $manager,
        AnneeUniversitaireService $anneeUniversitaireService,
        CalendrierExamenHistoriqueManager $historicalManager
    ) {
        $user = $this->getUser();
        /** @var \App\Entity\CalendrierExamen $examenCalendar */
        $examenCalendar = $manager->createObject();
        $form           = $this->createForm(
            ExamenCalendarType::class,
            $examenCalendar,
            [
                'user' => $user
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $examenCalendar->setAnneeUniversitaire($anneeUniversitaireService->getCurrent());
            $manager->save($examenCalendar);

            /** @var \App\Entity\CalendrierExamenHistorique $historical */
            $historical = $historicalManager->getInstance($examenCalendar, $user, $examenCalendar->getStatus());
            $historical->setUser($this->getUser());
            $historical->setStatus(CalendrierExamen::STATUS_CREATED);
            $historical->setCalendrierExamen($examenCalendar);
            $historicalManager->save($historical);

            return $this->redirectToRoute('front_cm_examen_calendar_list');
        }

        return $this->render(
            'frontend/chef-mention/examen/new.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/calendrier-Examens/{id}/edit", name="front_cm_examen_calendar_edit", methods={"GET", "POST"})
     * @IsGranted("ROLE_CHEFMENTION")
     * @param \Symfony\Component\HttpFoundation\Request      $request
     * @param \App\Entity\CalendrierExamen                   $calendrierExamen
     * @param \App\Manager\CalendrierExamenManager           $manager
     * @param \App\Manager\CalendrierExamenHistoriqueManager $historiqueManager
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function examenCalendarEdit(
        Request $request,
        CalendrierExamen $calendrierExamen,
        CalendrierExamenManager $manager,
        CalendrierExamenHistoriqueManager $historiqueManager
    ) {
        $user = $this->getUser();
        $form = $this->createForm(
            ExamenCalendarType::class,
            $calendrierExamen,
            [
                'calendar' => $calendrierExamen,
                'user'     => $user,
                'em'       => $this->getDoctrine()->getManager()
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->save($calendrierExamen);

            /** @var \App\Entity\CalendrierExamenHistorique $historical */
            $historical = $historiqueManager->createObject();
            $historical->setUser($this->getUser());
            $historical->setStatus($calendrierExamen->getStatus());
            $historical->setCalendrierExamen($calendrierExamen);
            $historiqueManager->save($historical);

            return $this->redirectToRoute('front_cm_examen_calendar_list');
        }

        return $this->render(
            'frontend/chef-mention/examen/new.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/calendrier-examen", name="front_cm_examen_calendar_list", methods={"GET"})
     * @IsGranted("ROLE_CHEFMENTION")
     * @param \App\Manager\CalendrierExamenManager    $calendrierExamenManager
     * @param \App\Services\AnneeUniversitaireService $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function examenCalendarList(
        CalendrierExamenManager $calendrierExamenManager,
        AnneeUniversitaireService $anneeUniversitaireService
    ) {
        $calendarList = $calendrierExamenManager->loadBy(
            [
                'anneeUniversitaire' => $anneeUniversitaireService->getCurrent()
            ],
            [
                'dateSchedule' => 'ASC'
            ]
        );

        return $this->render(
            'frontend/chef-mention/examen/index.html.twig',
            [
                'items' => $calendarList
            ]
        );
    }

    /**
     * @Route("/notes/{id}", name="front_cm_notes")
     * @IsGranted("ROLE_CHEFMENTION")
     * @param \App\Manager\EnseignantManager $enseignantManager
     * @param \App\Entity\Enseignant         $enseignant
     * @param \App\Manager\MatiereManager    $matiereManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function notes(EnseignantManager $enseignantManager, Enseignant $enseignant, MatiereManager $matiereManager)
    {
        $user    = $this->getUser();
        $mention = $user->getMention();

        $teachersMention = [];

        if ($mention) {
            $teachersMention = $enseignantManager->getByMention($mention->getId());
        }

        $matieres = $matiereManager->getByEnseignant($enseignant->getId());

        return $this->render(
            'frontend/chef-mention/notes/notes.html.twig',
            [
                'teachers'  => $teachersMention,
                'matieres'  => $matieres,
                'teacherId' => $enseignant->getId(),
                'noteIndex' => 'front_cm_enseignant'
            ]
        );
    }

    /**
     * @Route("/notes", name="front_cm_enseignant")
     * @IsGranted("ROLE_CHEFMENTION")
     * @param \App\Manager\EnseignantManager $enseignantManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function enseignants(EnseignantManager $enseignantManager)
    {
        $user    = $this->getUser();
        $mention = $user->getMention();

        $teachersMention = [];

        if ($mention) {
            $teachersMention = $enseignantManager->getByMention($mention->getId());
        }

        return $this->render(
            'frontend/chef-mention/notes/notes.html.twig',
            [
                'teachers'  => $teachersMention,
                'noteIndex' => 'front_cm_enseignant'
            ]
        );
    }

    /**
     * Add new notes function
     * @IsGranted("ROLE_CHEFMENTION")
     * @Route("/notes/{teacher_id}/{matiere_id}/add", name="front_cm_manage_notes",  methods={"GET", "POST"})
     * @ParamConverter("enseignant", options={"mapping": {"teacher_id": "id"}})
     * @ParamConverter("matiere", options={"mapping": {"matiere_id": "id"}})
     *
     * @param \App\Entity\Enseignant                    $enseignant
     * @param \App\Entity\Matiere                       $matiere
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\NotesManager                 $notesManager
     * @param \App\Repository\EtudiantRepository        $etudiantRepo
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function newNnotes(
        Enseignant $enseignant,
        Matiere $matiere,
        Request $request,
        NotesManager $notesManager,
        EtudiantRepository $etudiantRepo,
        AnneeUniversitaireService $anneeUniversitaireService
    ) : Response {
        $matiereUE          = $matiere->getUniteEnseignements();
        $anneeUniversitaire = $anneeUniversitaireService->getCurrent();
        $etudiants          = $notesManager->getByMatiereEnseignant(
            $enseignant->getId(), $matiere->getId(), $anneeUniversitaire->getId()
        );
        $form               = $this->createForm(NoteType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $notes = $request->get('note');
            foreach ($notes['etudiant'] as $etudiantId => $noteVal) {

                //check if etudiant note exist
                $etudiant = $etudiantRepo->find($etudiantId);

                /** @var \App\Entity\Notes $note */
                $note = $notesManager->loadOneBy(
                    [
                        'etudiant'           => $etudiant,
                        'matiere'            => $matiere,
                        'anneeUniversitaire' => $anneeUniversitaire
                    ]
                );
                if (!$note) {
                    $note = $notesManager->createObject();
                    $note->setAnneeUniversitaire($anneeUniversitaire);
                }

                if ($noteVal['note'] !== "") {
                    if (
                    (intval($noteVal['note']) === 0 || $noteVal['note'] > 0)
                    ) {
                        $note->setNote($noteVal['note']);
                        $note->setUser($this->getUser());
                    }
                    if (
                        isset($noteVal['rattrapage']) &&
                        $noteVal['rattrapage'] !== "" &&
                        (intval($noteVal['rattrapage']) === 0 || $noteVal['rattrapage'] > 0)
                    ) {
                        $note->setRattrapage($noteVal['rattrapage']);
                    }

                    $note->setEtudiant($etudiant);
                    $note->setMatiere($matiere);
                    $note->setSemestre($matiereUE->getSemestre());
                    $note->setCreatedAt(new \DateTime());
                    $note->setUpdatedAt(new \DateTime());
                    $notesManager->persist($note);
                }
            }

            $notesManager->flush();

            return $this->redirectToRoute('front_cm_notes', ['id' => $enseignant->getId()]);
        }

        return $this->render(
            'frontend/chef-mention/notes/addNote.html.twig',
            [
                'teacherId' => $enseignant->getId(),
                'etudiants' => $etudiants,
                'matiere'   => $matiere,
                'form'      => $form->createView()
            ]
        );
    }

    /**
     * @IsGranted("ROLE_CHEFMENTION")
     * @Route("/extra-note", name="front_cm_extranote_list", methods={"GET"})
     * @param \App\Manager\NiveauManager $niveaumanager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function extraNoteList(NiveauManager $niveaumanager) {
        $mentionId = 0;
        if ($mention = $this->getUser()->getMention()) {
            $mentionId = $mention->getId();
        }
        $niveaux     = $niveaumanager->loadBy([], ['libelle' => 'ASC']);

        return $this->render(
            'frontend/chef-mention/notes/extranote-list.html.twig',
            [
                'm'        => (int) $mentionId,
                'niveaux'  => $niveaux,
            ]
        );
    }

    /**
     * @IsGranted("ROLE_CHEFMENTION")
     * @Route("/extra-note/{mention_id}/{note_id}/edit", name="front_cm_extranote_edit", methods={"GET", "POST"})
     * @ParamConverter("mention", options={"mapping": {"mention_id": "id"}})
     * @ParamConverter("note", options={"mapping": {"note_id": "id"}})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\ExtraNote                     $note
     * @param \App\Entity\Mention                       $mention
     * @param \App\Manager\ExtraNotesManager            $notesManager
     * @param \App\Manager\ExtraNoteHistoriqueManager   $historicalManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function extraNoteEdit(
        Request $request,
        ExtraNote $note,
        Mention $mention,
        ExtraNotesManager $notesManager,
        ExtraNoteHistoriqueManager $historicalManager
    ) {
        $form = $this->createForm(
            ExtraNoteType::class,
            $note,
            [
                'mention' => $mention,
                'note'    => $note,
                'status'  => ExtraNote::$cmStatusList
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $notesManager->save($note);

            /** @var \App\Entity\ExtraNoteHistorique $historical */
            $historical = $historicalManager->createObject();
            $historical->setStatus($note->getStatus());
            $historical->setExtraNote($note);
            $historical->setUser($this->getUser());
            $historicalManager->save($historical);

            return $this->redirectToRoute('front_cm_extranote_list', ['m' => $mention->getId()]);
        }

        return $this->render(
            'frontend/chef-mention/notes/extranote-edit.html.twig',
            [
                'form'  => $form->createView(),
                'notes' => $note
            ]
        );
    }

    /**
     * @IsGranted("ROLE_CHEFMENTION")
     * @Route("/concours/result/index", name="front_cm_concours_result", methods={"GET", "POST"})
     * @param \App\Manager\ConcoursManager                  $concoursManager
     * @param \App\Manager\ConcoursNotesManager             $notesManager
     * @param \App\Serive\AnneeUniversitaireService         $anneeUniversitaireService
     * @param \App\Manager\ParcoursManager                  $parcoursManager
     * @param \Symfony\Component\HttpFoundation\Request     $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function ShowConcoursResult(
        Request                 $request,
        ConcoursManager         $concoursManager,
        ParcoursManager         $parcoursManager,
        ConcoursNotesManager    $notesManager,
        AnneeUniversitaireService $anneeUniversitaireService
    ) {
        $user = $this->getUser();
        $mention    = $user->getMention();
        
        $selectedC = $request->get('c');
        $selectedP = $request->get('p');
        $currentAnneUniv = $anneeUniversitaireService->getCurrent();
        $concours = $concoursManager->loadBy(['mention' => $mention], ['libelle' => 'ASC']);
        $parcours = $parcoursManager->loadBy(['mention'  => $mention], ['nom' => 'ASC']);

        $resultats        = [];
        $selectedConcours = '';
        $form = '';
        /** @var \App\Entity\Concours $selectedConcours */
        if ($selectedC && ($selectedConcours = $concoursManager->load($selectedC))) {
            $resultats = $notesManager->getLevelNotesByMixedParams(
                $selectedConcours, 
                $mention, 
                $currentAnneUniv->getId(), 
                $selectedP,
                Concours::STATUS_VALIDATED_RECTEUR,
                NULL,
                NULL,
                ConcoursCandidature::RESULT_ADMITTED
            );
        }

        //dump($selectedConcours->getDeliberation());die;

        return $this->render(
            'frontend/chef-mention/concours/result-list.html.twig',
            [
                'c'        => $selectedC,
                'p'        => $selectedP,
                'concours' => $concours,
                'parcours' => $parcours,
                'selectedConcours' => $selectedConcours,
                'form'     => $form,
                'resultats'=> $resultats
            ]
        );
    }

    /**
     * @IsGranted("ROLE_CHEFMENTION")
     * @Route("/concours/result/validate/{id}", name="front_cm_validate_concours_result", methods={"POST"})
     * @param \App\Entity\Concours                          $concours
     * @param \App\Manager\ConcoursManager                  $concoursManager
     * @param \Symfony\Component\HttpFoundation\Request     $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function validateConcoursResult(Concours $concours, ConcoursManager $concoursManager, Request $request) {
        $concours->setResultStatut(Concours::STATUS_VALIDATED_CM);
        $concoursManager->save($concours);
    
        return $this->redirectToRoute('front_cm_concours_result');
    }

    /**
     * @Route("/concours/notes/{id}", name="front_cm_concours_candidate_notes", methods={"GET", "POST"})
     * @IsGranted("ROLE_CHEFMENTION")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\ConcoursCandidature           $candidate
     * @param \App\Manager\ConcoursCandidatureManager   $concoursCandidatureManager
     * @param \App\Manager\ConcoursMatiereManager       $concoursMatiereManager
     * @param \App\Manager\ConcoursNotesManager         $concoursNotesManager
     * @param \App\Manager\ConcoursManager              $concoursManager
     * @param \App\Manager\MentionManager               $mentionManager
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function candidatNotes(
        Request $request,
        ConcoursCandidature $candidate,
        ConcoursCandidatureManager $concoursCandidatureManager,
        ConcoursMatiereManager $concoursMatiereManager,
        ConcoursNotesManager $concoursNotesManager,
        ConcoursManager $concoursManager,
        MentionManager $mentionManager
    ) {
        $user = $this->getUser();
        $mention    = $user->getMention();

        $selectedC = $request->get('c');
        $selectedConcours = $concoursManager->load($selectedC);
        $selectedP = $request->get('p');

        $concoursNotes = $concoursNotesManager->createObject();
        $form          = $this->createForm(ConcoursNotesFormType::class, $concoursNotes);
        $form->handleRequest($request);

        $matieres = $concoursMatiereManager->findByCriteria(
            [
                'mention'  => $mention,
                'concours' => $selectedC,
                'parcours' => $selectedP
            ],
            [
                'libelle' => 'ASC'
            ]
        );
        $notes = $concoursNotesManager->getByCandidateIdAndConcoursId($candidate->getId(), $selectedC);
        $matieresNotes = [];
        /** @var \App\Entity\ConcoursMatiere $matiere */
        foreach ($matieres as $matiere) {
            $candidateNotes                          = new \stdClass;
            $candidateNotes->concours_candidature_id = $candidate->getId();
            $candidateNotes->concours_matiere_id     = $matiere->getId();
            $candidateNotes->concours_id             = $selectedC;
            $candidateNotes->matiere_libelle         = $matiere->getLibelle();
            $candidateNotes->note                    = null;
            foreach ($notes as $note) {
                if ($note['id'] == $matiere->getId()) {
                    $candidateNotes->note = $note['note'];
                }
            }
            $matieresNotes[] = $candidateNotes;
        }

        return $this->render(
            'frontend/chef-mention/concours/notes.html.twig',
            [
                'c'             => $selectedC,
                'p'             => $selectedP,
                'candidate'     => $candidate,
                'matieresNotes' => $matieresNotes,
                'form'          => $form->createView(),
                'concours'      => $selectedConcours
            ]
        );
    }

    /**
     * @IsGranted("ROLE_CHEFMENTION")
     * @Route("/examen/note/index", name="front_cm_examen_note", methods={"GET", "POST"})
     * @param \App\Manager\NiveauManager                    $niveauManager
     * @param \App\Manager\ConcoursNotesManager             $notesManager
     * @param \App\Serive\AnneeUniversitaireService         $anneeUniversitaireService
     * @param \App\Manager\ParcoursManager                  $parcoursManager
     * @param \Symfony\Component\HttpFoundation\Request     $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function ShowExamenNote(
        Request                     $request,
        NiveauManager               $niveauManager,
        ParcoursManager             $parcoursManager,
        NotesManager                $notesManager,
        AnneeUniversitaireService   $anneeUniversitaireService,
        NotesService                $noteService
    ) {
        $user = $this->getUser();
        $mention    = $user->getMention();
        $selectedN = $request->get('n');
        $selectedP = $request->get('p');
        $currentAnneUniv = $anneeUniversitaireService->getCurrent();

        $niveaux    = $niveauManager->loadBy([], ['libelle' => 'ASC']);
        $parcours   = $parcoursManager->loadBy(
            [
                'mention'   => $mention,
                'niveau'    => $selectedN
            ], 
            ['nom' => 'ASC']
        );

        $resultats        = [];
        $selectedConcours = '';
        $form = '';
        
        $notes = $notesManager->getClassNotes(
            $mention->getId(), 
            $selectedN, 
            $selectedP,
            $currentAnneUniv->getId()
        );
        $resultats = $noteService->getClassExamenNote($notes);

        // dump($resultats);die;
        return $this->render(
            'frontend/chef-mention/examen/result-list.html.twig',
            [
                'n'         => $selectedN,
                'p'         => $selectedP,
                'niveaux'   => $niveaux,
                'parcours'  => $parcours,
                'resultats' => $resultats
            ]
        );
    }

    /**
     * IsGranted("ROLE_CHEFMENTION")
     * @Route("/chef-mention/etudiant/{id}/notes", name="front_cm_etudiant_releve", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Etudiant                                                  $etudiant
     * @param \App\Manager\NotesManager                                             $noteManager
     * @param \App\Repository\SemestreManager                                       $semestreManager
     * @param \App\Services\NotesService                                            $noteService   
     */

    public function etudiantNote(
        Etudiant $etudiant,
        NotesManager $noteManager,
        AnneeUniversitaireService $anneeUniversitaireService,
        NotesService $noteService
    ) {
        $niveauSemestre1 = null;
        $resultUeSem1 = null;
        $moyenneSem1 = 0;
        $niveauSemestre2 = null;
        $resultUeSem2 = null;
        $moyenneSem2 = 0;
        $moyenneGenerale = null;


        return $this->render(
            'frontend/chef-mention/examen/note.html.twig',
            [
                'etudiant'          => $etudiant,
                'niveauSemestre1'   => $niveauSemestre1,
                'resultUeSem1'      => $resultUeSem1,
                'moyenneSem1'       => $moyenneSem1,

                'niveauSemestre2'   => $niveauSemestre2,
                'resultUeSem2'      => $resultUeSem2,
                'moyenneSem2'       => $moyenneSem2,

                'moyenneGenerale'   => $moyenneGenerale
            ]
        );
        
    }

    /**
     * IsGranted("ROLE_CHEFMENTION")
     * @Route("/prestations", name="front_cm_prestation_index", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\PrestationManager            $prestationManager
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     * @param \App\Manager\MentionManager               $mentionManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function prestationIndex(
        Request                     $request,
        MentionManager              $mentionmanager,
        PrestationManager           $prestationManager,
        CalendrierPaiementManager   $calPaiementManager,
        CalendrierPaiementRepository   $calPaiementRepo,
        AnneeUniversitaireService   $anneeUniversitaireService,
        WorkflowStatutService       $workflowStatService
    ) {
        $mention = $this->getUser()->getMention();
        $currentUserRoles = $this->getUser()->getRoles();
        $userDisplayStatut = $workflowStatService->getStatutForListByProfil($currentUserRoles);
        $c = $request->get('c', '');
        $selectedCalPaiement = $c? $calPaiementManager->load($c) : $calPaiementRepo->findDefault();
        $calPaiements = $calPaiementManager->loadAll();
        $collegeYear = $anneeUniversitaireService->getCurrent();
        $prestations = $prestationManager->getListGroupByAthor(
            $collegeYear->getId(), 
            $mention->getId(), 
            $selectedCalPaiement,
            null
        );

        // dump($prestations);die;

        return $this->render(
            'frontend/chef-mention/prestation/index.html.twig',
            [
                'c'            => $selectedCalPaiement ? $selectedCalPaiement->getId() : 0,
                'calPaiements' => $calPaiements,
                'list'         => $prestations
            ]
        );
    }

    /**
     * IsGranted("ROLE_CHEFMENTION")
     * @Route("/prestations/details/{id}", name="front_cm_prestation_details_index", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\PrestationManager            $prestationManager
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     * @param \App\Manager\MentionManager               $mentionManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function PrestationDetailsIndex(
        Request                     $request,
        User                        $user,
        MentionManager              $mentionmanager,
        PrestationManager           $prestationManager,
        CalendrierPaiementManager   $calPaiementManager,
        CalendrierPaiementRepository   $calPaiementRepo,
        AnneeUniversitaireService   $anneeUniversitaireService,
        WorkflowStatutService       $workflowStatService
    ) {
        $mention = $this->getUser()->getMention();
        $currentUserRoles = $this->getUser()->getRoles();
        $c = $request->get('c', '');
        $selectedCalPaiement = $c? $calPaiementManager->load($c) : $calPaiementRepo->findDefault();
        $profilListStatut = $workflowStatService->getStatutForListByProfil($this->getUser()->getRoles());
        $profilNextStatut = $workflowStatService->getResourceNextStatut($profilListStatut);
        $profilPrevStatut = $workflowStatService->getEdtPreviousStatut($profilListStatut);
        $calPaiements = $calPaiementManager->loadAll();
        $collegeYear = $anneeUniversitaireService->getCurrent();
        $prestations = $prestationManager->getListResumeByUser(
            $user->getId(),
            $collegeYear->getId(), 
            $mention->getId(), 
            $selectedCalPaiement,
            null
        );

        // dump($prestations);die;

        return $this->render(
            'frontend/chef-mention/prestation/resume-index.html.twig',
            [
                'c'            => $selectedCalPaiement ? $selectedCalPaiement->getId() : 0,
                'author'       => $user, 
                'calPaiements' => $calPaiements,
                'list'         => $prestations,
                'profilListStatut'  => $profilListStatut, 
                'profilNextStatut'  => $profilNextStatut,
                'profilPrevStatut'  => $profilPrevStatut
            ]
        );
    }

    /**
     * IsGranted("ROLE_CHEFMENTION")
     * @Route("/prestations/{id}/validatable/", name="front_cm_valid_prestation_index", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\PrestationManager            $prestationManager
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     * @param \App\Manager\MentionManager               $mentionManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function validatablePrestationIndex(
        Request                     $request,
        User                        $user,   
        MentionManager              $mentionmanager,
        PrestationManager           $prestationManager,
        CalendrierPaiementManager   $calPaiementManager,
        CalendrierPaiementRepository   $calPaiementRepo,
        AnneeUniversitaireService   $anneeUniversitaireService,
        ParameterBagInterface       $parameter,
        WorkflowStatutService       $workflowStatService
    ) {
        // dump($parameter->get('version'));die;
        $mention = $this->getUser()->getMention();
        $c = $request->get('c', 0);
        $selectedCalPaiement = $c? $calPaiementManager->load($c) :$calPaiementRepo->findDefault();
        $calPaiements = $calPaiementManager->loadAll();
        $collegeYear = $anneeUniversitaireService->getCurrent();
        $profilListStatut = $workflowStatService->getStatutForListByProfil($this->getUser()->getRoles());
        $profilNextStatut = $workflowStatService->getResourceNextStatut($profilListStatut);
        $profilPrevStatut = $workflowStatService->getEdtPreviousStatut($profilListStatut);
        $prestations = $prestationManager->getUserValidatablePrestation(
            $user->getId(),
            $collegeYear->getId(), 
            $mention->getId(), 
            $selectedCalPaiement,
            null
        );

        return $this->render(
            'frontend/chef-mention/prestation/validatable-index.html.twig',
            [
                'c'            => $selectedCalPaiement ? $selectedCalPaiement->getId() : 0,
                'author'       => $this->getUser(), 
                'user'         => $user, 
                'calPaiements' => $calPaiements,
                'list'         => $prestations,
                'profilListStatut'  => $profilListStatut,
                'profilNextStatut'  => $profilNextStatut,
                'profilPrevStatut'  => $profilPrevStatut
            ]
        );
    }

    /**
     * IsGranted("ROLE_CHEFMENTION")
     * @Route("/validate/prestations/", name="front_cm_prestation_validation", methods={"POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\PrestationManager            $prestationManager
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     * @param \App\Manager\MentionManager               $mentionManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function validatePrestation(
        Request                     $request,
        MentionManager              $mentionmanager,
        PrestationManager           $prestationManager,
        CalendrierPaiementManager   $calPaiementManager,
        WorkflowStatutService       $workflowStatService,
        AnneeUniversitaireService   $anneeUniversitaireService
    ) {
        $mention = $this->getUser()->getMention();
        $prestationIds = $request->get('prestation', array());
        $profilListStatut = $workflowStatService->getStatutForListByProfil($this->getUser()->getRoles());
        $entityManager = $this->getDoctrine()->getManager();
        foreach($prestationIds as $id) {
            $currPrest = $prestationManager->load($id);
            $currPrestNextStatut = $currPrest->getStatut() == $profilListStatut ? 
                $workflowStatService->getResourceNextStatut($profilListStatut) :
                $workflowStatService->getEdtPreviousStatut($currPrest->getStatut());
            $currPrest->setStatut($currPrestNextStatut);
            $entityManager->persist($currPrest);

            $prestHistory = new PaiementHistory();
            $prestHistory->setResourceName(PaiementHistory::PRESTATION_RESOURCE);
            $prestHistory->setStatut($currPrestNextStatut);
            $prestHistory->setResourceId($currPrest->getId());
            $prestHistory->setValidator($this->getUser());
            $prestHistory->setCreatedAt(new \DateTime());
            $prestHistory->setUpdatedAt(new \DateTime());
            $prestHistory->setMontant(0);
            $entityManager->persist($prestHistory);
        };
        $entityManager->flush();
        
        return new JsonResponse(array('statut' => '200'));
    }

    /**
     * IsGranted("ROLE_CHEFMENTION")
     * @Route("/surveillances", name="front_cm_surveillance_index", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\CalendrierPaiementManager    $calPaiementManager
     * @param \App\Manager\CalendrierExamenManager      $calExamenManager
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     * @param \App\Manager\MentionManager               $mentionManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function surveillanceIndex(
        Request                     $request,
        MentionManager              $mentionmanager,
        CalendrierExamenManager     $calExamenManager,
        CalendrierPaiementManager   $calPaiementManager,
        CalendrierPaiementRepository   $calPaiementRepo,
        AnneeUniversitaireService   $anneeUniversitaireService,
        WorkflowStatutService       $workflowStatService
    ) {
        $c = $request->get('c', '');
        $selectedCalPaiement = $c? $calPaiementManager->load($c) : $calPaiementRepo->findDefault();
        $user               = $this->getUser();
        $m = $user->getMention()->getId();
        $calPaiements = $calPaiementManager->loadAll();
        $currentAnneUniv    = $anneeUniversitaireService->getCurrent();
        $mentions = $mentionmanager->loadBy([ 'id'=> $m], []);

        $profilListStatut = $workflowStatService->getStatutForListByProfil($user->getRoles());
        $profilNextStatut = $workflowStatService->getResourceNextStatut($profilListStatut);
        $profilPrevStatut = $workflowStatService->getEdtPreviousStatut($profilListStatut);

        $surveillants = $calExamenManager->getCurrentVacation(
            $currentAnneUniv->getId(),
            $user->getMention()->getId(),
            $selectedCalPaiement
        );

        return $this->render(
            'frontend/chef-mention/surveillance/index.html.twig',
            [
                'c'            => $selectedCalPaiement ? $selectedCalPaiement->getId() : 0,
                'm'            => $m,
                'calPaiements' => $calPaiements,
                'list'         => $surveillants,
                'mentions'         => $mentions,
                'profilListStatut'      => $profilListStatut,
                'profilNextStatut'      => $profilNextStatut,
                'profilPrevStatut'      => $profilPrevStatut
            ]
        );
    }

    /**
     * IsGranted("ROLE_CHEFMENTION")
     * @Route("/surveillance/{id}/details", name="front_cm_surveillance_details", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\User $user
     * @param \App\Manager\EmploiDuTempsManager $edtManager
     * @param \App\Service\AnneeUniversitaireService $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function surveillanceDetails(
        Request                         $request, 
        User                            $surveillant,
        CalendrierExamenManager         $calExamenManager,
        CalendrierPaiementManager       $calPaiementManager,
        AnneeUniversitaireService       $anneeUniversitaireService,
        WorkflowStatutService           $workflowStatService)
    {
        $user               = $this->getUser();
        $c = $request->get('c', '');
        $m = $user->getMention()->getId();
        $selectedCalPaiement = $c? $calPaiementManager->load($c) : "";
        $calPaiements = $calPaiementManager->loadAll();
        $currentAnneUniv    = $anneeUniversitaireService->getCurrent();

        $profilListStatut = $workflowStatService->getStatutForListByProfil($user->getRoles());
        $profilNextStatut = $workflowStatService->getResourceNextStatut($profilListStatut);
        // $profilPrevStatut = $workflowStatService->getEdtPreviousStatut($profilListStatut);


        $surveillantVacations = $calExamenManager->getSurveillantVacation(
            $surveillant->getId(),
            $currentAnneUniv->getId(),
            $user->getMention()->getId(),
            $selectedCalPaiement,
            null
        );

        return $this->render(
            'frontend/chef-mention/surveillance/details-index.html.twig',
            [
                'c'                     => $c,
                'm'                     => $m,
                'surveillant'           => $surveillant,
                'calPaiement'           => $selectedCalPaiement,
                'list'                  => $surveillantVacations,
                'profilListStatut'      => $profilListStatut,
                'profilNextStatut'      => $profilNextStatut
            ]
        );
    }  

    /**
     * IsGranted("ROLE_CHEFMENTION")
     * @Route("/validate/surveillance/", name="front_cm_surveillance_validate", methods={"POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\PrestationManager            $prestationManager
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     * @param \App\Manager\MentionManager               $mentionManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function validateSurveillance(
        Request                     $request,
        MentionManager              $mentionmanager,
        CalendrierExamenManager     $calExamManager,
        CalendrierPaiementManager   $calPaiementManager,
        WorkflowStatutService       $workflowStatService,
        AnneeUniversitaireService   $anneeUniversitaireService
    ) {
        $user = $this->getUser();
        $mention = $user->getMention();
        $surveillantIds = $request->get('surveillant', array());
        $profilListStatut = $workflowStatService->getStatutForListByProfil($user->getRoles());
        $entityManager = $this->getDoctrine()->getManager();
        foreach($surveillantIds as $id) {
            $currSurvey = $calExamManager->load($id);
            $currSurvNextStatut = $currSurvey->getStatut() == $profilListStatut ? 
                $workflowStatService->getResourceNextStatut($profilListStatut) :
                $workflowStatService->getEdtPreviousStatut($currSurvey->getStatut());
            $currSurvey->setStatut($currSurvNextStatut);
            $entityManager->persist($currSurvey);

            $surveyHistory = new PaiementHistory();
            $surveyHistory->setResourceName(PaiementHistory::SURVEILLANCE_RESOURCE);
            $surveyHistory->setStatut($currSurvNextStatut);
            $surveyHistory->setResourceId($currSurvey->getId());
            $surveyHistory->setValidator($this->getUser());
            $surveyHistory->setCreatedAt(new \DateTime());
            $surveyHistory->setUpdatedAt(new \DateTime());
            $surveyHistory->setMontant(0);
            $entityManager->persist($surveyHistory);
        };
        $entityManager->flush();
        
        return new JsonResponse(array('statut' => '200'));
    }

}