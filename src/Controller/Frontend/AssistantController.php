<?php

namespace App\Controller\Frontend;

use App\Entity\CalendrierExamen;
use App\Entity\CalendrierExamenSurveillance;
use App\Entity\CalendrierSoutenance;
use App\Entity\EmploiDuTemps;
use App\Entity\FichePresenceEnseignant;
use App\Entity\Mention;
use App\Entity\Salles;
use App\Entity\User;
use App\Entity\Enseignant;
use App\Entity\EnseignantMatiere;
use App\Entity\DemandeDoc;
use App\Entity\Matiere;
use App\Entity\PaiementHistory;
use App\Entity\Prestation;
use App\Entity\PrestationUser;
use App\Entity\Roles;

use App\Form\DemandeType;
use App\Form\EmploiDuTempsType;
use App\Form\EnseignantMatiereType;
use App\Form\EnseignantParcoursType;
use App\Form\EnseignantType;
use App\Form\ExamenCalendarType;
use App\Form\ExtraNoteType;
use App\Form\FichePresenceEnseignantType;
use App\Form\MatiereType;
use App\Form\PrestationType;
use App\Form\RegistrationFormType;
use App\Form\SallesType;
use App\Form\ThesisCalendarType;

use App\Manager\CalendrierExamenHistoriqueManager;
use App\Manager\CalendrierExamenManager;
use App\Manager\CalendrierExamenSurveillanceManager;
use App\Manager\CalendrierPaiementManager;
use App\Manager\CalendrierSoutenanceHistoriqueManager;
use App\Manager\CalendrierSoutenanceManager;
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
use App\Manager\PaiementHistoryManager;
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
use App\Repository\MatiereRepository;
use App\Repository\PrestationUserRepository;
use App\Repository\ProfilRepository;
use App\Repository\TypePrestationRepository;
use App\Repository\UserRepository;

use App\Services\AnneeUniversitaireService;
use App\Services\UtilFunctionService;
use App\Services\WorkflowStatutService;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use Symfony\Component\Filesystem\Filesystem;
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
 * Description of AssistantController.php.
 *
 * @package App\Controller\Frontend
 * @Route("/assistant")
 */
class AssistantController extends AbstractController
{

    /**
     * @Route("/", name="frontend_assistant_index")
     * @param \App\Manager\EnseignantManager $manager
     * @return \Symfony\Component\HttpFoundation\Response
     * @IsGranted({"ROLE_ASSISTANT", "ROLE_ADMIN_ASS"})
     */
    public function index(EnseignantManager $manager, EnseignantMentionManager $enseMentionManager, MentionManager $mentionmanager)
    {
        $user = $this->getUser();
        $resMention = $mentionmanager->loadOneBy(
            array(
                "id" => $user->getMention()->getId()
            )
        );
        return $this->render(
            'frontend/assistant/index.html.twig',
            [
                'enseignants'     => $enseMentionManager->getAllByMention($user->getMention()->getId())
            ]
        );
    }

    /**
     * @Route("/identification", name="front_assistant_login")
     */
    public function login()
    {
        return $this->render(
            'frontend/common/login.html.twig',
            [
                'entity'     => 'assistant',
                'espacename' => 'Espace assistant'
            ]
        );
    }

    /**
     * @Route("/mon-compte", name="front_assistant_me")
     * @IsGranted("ROLE_ASSISTANT")
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
            
            return $this->redirectToRoute('frontend_assistant_index');
        }

        return $this->render(
            'frontend/assistant/mon-compte.html.twig',
            [
                'user'    => $user,
                'form'          => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/enseignant/add", name="front_assistant_create_enseignant",  methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\UserManager                      $userManager,
     * @param \App\Manager\ProfilManager                      $profilManager,
     * @param \App\Manager\EnseignantManager                $manager
     * @param \App\Manager\EnseignantMentionManager                $enseignantmentionmanager
     * @param \App\Manager\MentionManager                $mentionmanager
     * @param \App\Manager\NiveauManager                            $niveaumanager
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameter
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function enseignantNew(
        Request                     $request,
        UserManager                 $userManager,
        ProfilManager               $profilManager,
        EnseignantManager           $manager,
        EnseignantMentionManager    $enseignantmentionmanager,
        MentionManager              $mentionmanager,
        NiveauManager               $niveaumanager,
        UserPasswordEncoderInterface $passwordEncoder,
        ParameterBagInterface       $parameter
        ) : Response
    {
        $enseignant = $manager->createObject();
        $form   = $this->createForm(
            EnseignantType::class,
            $enseignant
        );
        $form->handleRequest($request);
        $mentions = $mentionmanager->loadAll();
        $niveaux = $niveaumanager->loadAll();
        $enseignants = $manager->loadBy(['status' => 1], ['lastName' => 'ASC', 'firstName' => 'ASC']);
        $this->denyAccessUnlessGranted('ROLE_ASSISTANT');
        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {

            //load selected enseignant

            //dump($request->request->get('selected_enseignant'));die;

            if($enseignantId = $request->request->get('selected_enseignant')) {
                $enseignant = $manager->load($enseignantId);
            } else {
                //insert line to user table
                $profilEnseignant = $profilManager->loadOneBy(array('name' => 'Enseignant'));
                $newUser = $userManager->createObject();
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
                //$userManager->save($newUser);
                $enseignant->setUser($newUser);
            }

            $cvFile        = $form->get('pathCv')->getData();
            $diplomeFile   = $form->get('pathDiploma')->getData();
            $niveauPosted  = ($_POST["niveau"]) ? $_POST["niveau"] : "";            

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
            //save enseignant
            $manager->save($enseignant);

            // Mention
            $resMention = $mentionmanager->loadOneBy(array("id" => $user->getMention()->getId()));
            // Niveau
            if(count($niveauPosted)!=0){
                foreach ($niveauPosted as $key => $value) {
                    $enseignantmention = $enseignantmentionmanager->createObject();
                    $resNiveau = $niveaumanager->loadOneBy(array("id" => $value));
                    $enseignantmention->setNiveau($resNiveau);
                    $enseignantmention->setEnseignant($enseignant);
                    $enseignantmention->setMention($resMention);
                    $enseignantmentionmanager->persist($enseignantmention);
                }
                $enseignantmentionmanager->flush();
            }            

            return $this->redirectToRoute('front_assistant_edit_enseignant_2', ['id' => $enseignant->getId()]);
        }

        return $this->render(
            'frontend/assistant/addEnseignant.html.twig',
            [
                'enseignant'    => $enseignant,
                'enseignants'    => $enseignants,
                'mentions'      => $mentions,
                'niveaux'       => $niveaux,
                'form'          => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/ens/autocomlete/", name="front_assistant_ens_autocomplete", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getEnsAutoCompleteResults(
        EnseignantManager           $manager,
        Request $request): Response {

        $srchText = $request->get('qry');
        $ensList = $manager->searchFromName($srchText);

        // dump($ensList);die;

        return $this->json($ensList);

    }

    /**
     * @Route("/enseignant/{id}/infos", name="front_assistant_enseignant_infos", methods={"GET","POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Enseignant                        $enseignant
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameter
     * @param \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface $passwordEncoder
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getEnseignantInfos(
        Request $request,
        Enseignant $enseignant): Response {

        return $this->render(
            'frontend/assistant/_enseignant_infos.html.twig',
            [
                'enseignant'    => $enseignant
            ]
        );

    }

    /**
     * @Route("/{id}/edit", name="front_assistant_edit_enseignant", methods={"GET","POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Enseignant                        $enseignant
     * @param \App\Manager\EnseignantManager                $manager
     * @param \App\Manager\EnseignantMentionManager                $enseignantmentionmanager
     * @param \App\Manager\MentionManager                $mentionmanager
     * @param \App\Manager\NiveauManager                            $niveaumanager
     * @param \App\Manager\ProfilManager                      $profilManager,
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameter
     * @param \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface $passwordEncoder
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function enseignantEdit(
        Request $request,
        Enseignant $enseignant,
        UserManager                 $userManager,
        EnseignantManager           $manager,
        EnseignantMentionManager    $enseignantmentionmanager,
        MentionManager              $mentionmanager,
        NiveauManager               $niveaumanager,
        ProfilManager                      $profilManager,
        UserPasswordEncoderInterface $passwordEncoder,
        ParameterBagInterface $parameter
        ) : Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $form = $this->createForm(EnseignantType::class, $enseignant);
        $form->handleRequest($request);
        $mentions = $mentionmanager->loadAll();
        $niveaux = $niveaumanager->loadAll();
        $this->denyAccessUnlessGranted('ROLE_ASSISTANT');
        $user = $this->getUser();
        $resMention = $mentionmanager->loadOneBy(array("id" => $user->getMention()->getId()));

        $currentNiveau = $enseignantmentionmanager->loadby(
            array(
                'enseignant'    => $enseignant->getId(),
                'mention'       => $resMention
            )
        );
        $tCollectEnsNiveauId = [];
        foreach($currentNiveau as $niv) {
            if(!in_array( $curNivId = $niv->getNiveau()->getId(), $tCollectEnsNiveauId)){
                $tCollectEnsNiveauId[] = $curNivId;
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $cvFile        = $form->get('pathCv')->getData();
            $diplomeFile   = $form->get('pathDiploma')->getData();
            $niveauPosted  = ($_POST["niveau"]) ? $_POST["niveau"] : "";

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
            if($enseignant->getUser()) {
                $newUser = $userManager->load($enseignant->getUser());
            } else {
                $newUser = $userManager->createObject();
                $profilEnseignant = $profilManager->loadOneBy(array('name' => 'Enseignant'));
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
            $entityManager->persist($newUser);
            $enseignant->setUser($newUser);
            $entityManager->persist($enseignant);
            //remove all enseignant mention before save
            if(count($niveauPosted)!=0){
                $currentEnsMentionNiveau = $enseignantmentionmanager->loadBy(
                    array(
                        'enseignant'    => $enseignant->getId(),
                        'mention'       => $resMention->getId()
                    )
                );
                foreach($currentEnsMentionNiveau as $ensMentionNiveau){
                    $entityManager->remove($ensMentionNiveau);
                    $entityManager->flush();
                }
            }
            // Niveau
            if(count($niveauPosted)!=0){
                foreach ($niveauPosted as $key => $value) {
                    $enseignantmention = $enseignantmentionmanager->createObject();
                    $resNiveau = $niveaumanager->loadOneBy(array("id" => $value));
                    $enseignantmention->setNiveau($resNiveau);
                    $enseignantmention->setEnseignant($enseignant);
                    $enseignantmention->setMention($resMention);
                    $entityManager->persist($enseignantmention);
                }
            }
            $entityManager->flush();
            
            return $this->redirectToRoute('front_assistant_edit_enseignant_2', ['id' => $enseignant->getId()]);
        }

        return $this->render(
            'frontend/assistant/editEnseignant.html.twig',
            [
                'enseignant'    => $enseignant,
                'mentions'      => $mentions,
                'niveaux'       => $niveaux,
                'tCollectEnsNiveauId' => $tCollectEnsNiveauId,
                'form'          => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}/edit-2", name="front_assistant_edit_enseignant_2", methods={"GET","POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Enseignant                        $enseignant
     * @param \App\Manager\EnseignantManager                $manager
     * @param \App\Manager\EnseignantMentionManager                $enseignantmentionmanager
     * @param \App\Manager\MentionManager                $mentionmanager
     * @param \App\Manager\NiveauManager                            $niveaumanager
     * @param \App\Manager\ProfilManager                      $profilManager,
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameter
     * @param \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface $passwordEncoder
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function enseignantEdit2(
        Request $request,
        Enseignant $enseignant,
        UserManager                 $userManager,
        EnseignantManager           $manager,
        EnseignantMentionManager    $enseignantmentionmanager,
        MentionManager              $mentionmanager,
        NiveauManager               $niveaumanager,
        ProfilManager                      $profilManager,
        UserPasswordEncoderInterface $passwordEncoder,
        ParcoursManager $parcoursManager,
        ParameterBagInterface $parameter
        ) : Response
    {
        $form = $this->createForm(EnseignantParcoursType::class, $enseignant);
        $form->handleRequest($request);
        $this->denyAccessUnlessGranted('ROLE_ASSISTANT');
        $user = $this->getUser();
        /** @var \App\Entity\Mention $mention */
        $mention = $user->getMention();
        $parcours = $enseignantmentionmanager->getParcourables($enseignant->getId(), $mention->getId());
        // dump($parcours);die;
        // $parcours = $parcoursManager->loadBy(['niveau' => $tCollectEnsNiveauId, 'mention' => $mention]);
        if (count($parcours) < 1 ) {
            return $this->redirectToRoute('frontend_assistant_index');
        }
        // $currentNiveau         = $enseignantmentionmanager->loadby(
        //     [
        //         'enseignant'    => $enseignant->getId(),
        //         'mention'       => $mention
        //     ]
        // );
        // $tCollectEnsNiveauId   = [];
        // $tCollectEnsParcoursId = [];
        // foreach ($currentNiveau as $niv) {
        //     if (!in_array($curNivId = $niv->getNiveau()->getId(), $tCollectEnsNiveauId)) {
        //         $tCollectEnsNiveauId[] = $curNivId;
        //     }
        //     if ($niv->getParcours() && !in_array($curPId = $niv->getParcours()->getId(), $tCollectEnsParcoursId)) {
        //         $tCollectEnsParcoursId[] = $curPId;
        //     }
        // }

        // $parcours = $parcoursManager->loadBy(['niveau' => $tCollectEnsNiveauId, 'mention' => $mention]);
        // if (!$parcours) {
        //     return $this->redirectToRoute('frontend_assistant_index');
        // }

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $request->request->all();

            //remove all enseignant mention before save
            // $currentEnsMentionNiveau = $enseignantmentionmanager->loadBy(
            //     [
            //         'enseignant' => $enseignant->getId(),
            //         'mention'    => $mention->getId()
            //     ]
            // );          
            // foreach ($currentEnsMentionNiveau as $ensMentionNiveau) {
            //     dump($ensMentionNiveau->getNiveau()->getParcours()->toArray());
            //     if ($ensMentionNiveau->getNiveau()->getParcours()->toArray()) {
            //         $entityManager = $this->getDoctrine()->getManager();
            //         $entityManager->remove($ensMentionNiveau);
            //         $entityManager->flush($ensMentionNiveau);
            //     }
            // } die;


            foreach ($parcours as $item) {
                if($item['parcoursId']) {
                    $ensMention = $enseignantmentionmanager->load($item['id']);
                    $entityManager->remove($ensMention);
                }
                // $entityManager->flush($ensMention);
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

            return $this->redirectToRoute('frontend_assistant_index');

        }

        return $this->render(
            'frontend/assistant/editEnseignant2.html.twig',
            [
                'parcours'              => $parcours,
                'form'                  => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="front_assistant_delete_enseignant", methods={"DELETE"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Enseignant                        $enseignant
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

        return $this->redirectToRoute('frontend_assistant_index');
    }

    /**
     * @Route("/matiere/", name="frontend_matiere_index")
     * @param \App\Manager\MentionManager $mentionmanager
     * @param \App\Manager\MatiereManager    $matManager
     * @return \Symfony\Component\HttpFoundation\Response
     * @IsGranted("ROLE_ASSISTANT")
     */
    public function enseignantMatiereList(MentionManager $mentionmanager, MatiereManager $matManager)
    {
        $user = $this->getUser();
        $matieres = $matManager->getByMention($user->getMention()->getId());

        // dump($matieres);die;

        return $this->render(
            'frontend/assistant/index.html.twig',
            [
                'enseignantsEtmatieres'     => $matieres
            ]
        );
    }

    /**
     * @Route("/enseignant/matiere/add", name="front_assistant_create_enseignant_matiere",  methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\EnseignantMatiere                 $enseignantMatiere
     * @param \App\Manager\EnseignantManager                $manager
     * @param \App\Manager\EnseignantMatiereManager         $enseignantMatieremanager
     * @param \App\Manager\EnseignantMentionManager         $enseignantMentionmanager
     * @param \App\Manager\MentionManager                   $mentionmanager
     * @param \App\Manager\NiveauManager                    $niveaumanager
     * @param \App\Manager\ParcoursManager                  $parcoursManager
     * @param \App\Repository\UniteEnseignementsManager     $ueManager,
     * @param \App\Repository\MatiereRepository             $matRepository,
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameter
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function assignMatter(
        Request                     $request,
        NiveauManager               $niveauManager,
        ParcoursManager             $parcoursManager,
        EnseignantManager           $manager,
        MentionManager              $mentionManager,
        EnseignantMentionManager    $enseMentionManager,
        MatiereManager              $matManager,
        UniteEnseignementsManager   $ueManager,
        MatiereRepository           $matRepository,
        ParameterBagInterface       $parameter
        ) : Response
    {
        $user = $this->getUser();
        $resMention = $mentionManager->loadOneBy(array("id" => $user->getMention()->getId()));
        $niveaux            = $niveauManager->loadAll();
        // $matieres = $matManager->getAllByMention($user->getMention()->getId());
        $matieres           = [];
        // $uniteEnseignements = $ueManager->getAllByMention($resMention->getId());
        $uniteEnseignements = [];
        // $enseignants = $enseMentionManager->getAllByMention($user->getMention()->getId());    
        $enseignants        = [];
        $parcours           = [];
        $enseignantMatiere = $matManager->createObject();
        $form   = $this->createForm(
            MatiereType::class,
            $enseignantMatiere
        );
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $matiereParams = $request->get('matiere');
            if(isset($matiereParams['matiere'])) {
                $enseignant = $manager->loadOneBy(array('id' => $matiereParams['enseignantId']));
                $ue = $ueManager->loadOneBy(array('id' => $matiereParams['ue']));
                //remove all enseignant matiere before save
                if(count($matiereParams['matiere']) > 0){
                    $currentEnsMatieres = $matManager->loadBy(
                        array(
                            'enseignant' => $enseignant->getId(),
                            'uniteEnseignements' => $ue->getId()
                        )
                    );
                    foreach($currentEnsMatieres as $matiere){
                        $matiere->setEnseignant(null);
                        $matManager->persist($matiere);
                    }
                    $matManager->flush();
                }
                foreach($matiereParams['matiere'] as $matId){
                    $enseignantMatiere = $matManager->loadOneBy(array('id' => $matId));
                    $enseignantMatiere->setEnseignant($enseignant);
                    $matManager->persist($enseignantMatiere);
                }
                if(count($matiereParams['matiere']) > 0 ){
                    $matManager->flush();
                }
            }

            return $this->redirectToRoute('frontend_matiere_index');
        }

        return $this->render(
            'frontend/assistant/addMatiere.html.twig',
            [
                'niveaux'               => $niveaux,
                'parcours'              => $parcours,
                'enseignants'           => $enseignants,
                'uniteEnseignements'    => $uniteEnseignements,
                'matieres'              => $matieres,
                'form'                  => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/enseignant/matiere/parent-child/options", name="front_enseignant_matiere_ajax_options",  methods={"GET", "POST"})
     * @param Request $request
     * @param NiveauManager $niveaumanager
     * @param ParcoursManager $parcoursManager
     * @param \App\Manager\EnseignantMentionManager         $enseMentionManager
     * @param SemestreManager $semManager
     * @param UniteEnseignementsManager $ueManager
     * @param MatiereManager $matiereManager
     * @return Response
     */
    public function assignMaterAjaxOptions(
        Request                     $request,
        NiveauManager               $niveaumanager,
        ParcoursManager             $parcoursManager,
        EnseignantMentionManager    $enseMentionManager,
        SemestreManager             $semManager,
        UniteEnseignementsManager   $ueManager,
        MatiereManager $matiereManager
    )
    {
        $user           = $this->getUser();
        $mention        = $user->getMention();
        $parentName     = $request->get('parent_name');
        $parentValue    = $request->get('parent_id');
        $childTarget    = $request->get('child_target');
        $parcours           = [];
        $uniteEnseignements = [];
        $matieres           = [];
        $enseignants        = [];

        switch($parentName) {
            case 'niveau':
                if($childTarget === 'parcours')
                    $parcours       = $parcoursManager->loadBy(
                        [
                            'mention' => $mention->getid(),
                            'niveau' => $parentValue
                        ]
                    );
                if($childTarget === 'enseignant') {
                    $enseignants    = $enseMentionManager->getAllByParams(
                        [
                            'mention' => $mention->getid(),
                            'niveau'  => $parentValue
                        ]
                    );
                }
                break;
            case 'parcours':
                $filterOptions = [];
                $filterOptions['mention']   = $mention->getId();
                $filterOptions['niveau']    = $request->get('niveau_id');
                $filterOptions[$parentName] = $parentValue;
                $enseignants   = $enseMentionManager->getAllByParams($filterOptions);
                break;
            case 'uniteEnseignements':
                $matieres = $matiereManager->loadBy([$parentName => $parentValue]);
                break;
            default:
                break;
        }

        return $this->render(
            'frontend/assistant/matiere/_ajax_options.html.twig',
            [
                'parcours'              => $parcours,
                'enseignants'           => $enseignants,
                'uniteEnseignements'    => $uniteEnseignements,
                'matieres'              => $matieres
            ]
        );
    }

    /**
     * @Route("/enseignant/matiere/{id}/edit", name="front_assistant_edit_enseignant_matiere",  methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\EnseignantMatiere                        $enseignantMatiere
     * @param \App\Manager\EnseignantManager                $manager
     * @param \App\Manager\EnseignantMatiereManager    $enseignantMatieremanager
     * @param \App\Manager\EnseignantMentionManager                $enseignantmentionmanager
     * @param \App\Manager\MentionManager                $mentionmanager
     * @param \App\Manager\NiveauManager                            $niveaumanager
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameter
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function editassignMatter(
        Request                     $request,
        EnseignantMatiere           $enseignantMatiere,
        EnseignantManager           $manager,
        EnseignantMentionManager    $enseignantmentionmanager,
        MentionManager              $mentionmanager,
        NiveauManager               $niveaumanager,
        ParameterBagInterface       $parameter
        ) : Response
    {
        //$enseignantMatiere = $enseignantMatieremanager->createObject();
        $form   = $this->createForm(EnseignantMatiereType::class,$enseignantMatiere);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->save($enseignantMatiere);
            return $this->redirectToRoute('frontend_matiere_index');
        }

        return $this->render(
            'frontend/assistant/editMatiere.html.twig',
            [
                'form'          => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/enseignant/ue", name="front_assistant_enseignant_matiere",  methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\EnseignantManager $ensManager
     *
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameter
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function getUeOptionsByEnseignant(
        Request                     $request,
        MentionManager              $mentionManager,
        EnseignantManager           $ensManager,
        UniteEnseignementsManager   $ueManager,
        ParameterBagInterface       $parameter
        ) : Response
    {
        $user = $this->getUser();
        $resMention = $mentionManager->loadOneBy(array("id" => $user->getMention()->getId()));
        $enseignant = $ensManager->loadOneBy(array("id" => $request->query->getInt('id')));
        $niveauId = $request->query->getInt('niveau_id');
        $parcoursId = $request->query->getInt('parcours_id');
        $ues = $ueManager->getAllByMentionAndEnseignant($resMention->getId(), $enseignant->getId(), $niveauId, $parcoursId);

        return $this->render(
            'frontend/assistant/_enseignant_ue_options.html.twig',
            [
                'uniteEnseignements'          => $ues
            ]
        );
    }

    /**
     * @Route("/enseignant/ue/matiere", name="front_assistant_enseignant_ue_matiere",  methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\EnseignantManager $ensManager
     *
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameter
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function getMatiereOptionsByUniteEns(
        Request                     $request,
        UniteEnseignementsManager   $ueManager,
        MatiereManager              $matManager,
        ParameterBagInterface       $parameter
        ) : Response
    {
        $ue = $ueManager->loadOneBy(array('id' => $request->query->getInt('id')));
        $matieres = $matManager->loadBy(array('uniteEnseignements' => $ue));

        return $this->render(
            'frontend/assistant/_ue_matiere_options.html.twig',
            [
                'matieres'          => $matieres
            ]
        );
    }

    /**
     * @Route("/gestion-emploi-temps", name="front_gestion_emploi_du_temps")
     * @IsGranted("ROLE_ASSISTANT")
     * @param EnseignantManager $manager
     * @param EnseignantMentionManager $enseMentionManager
     * @param MentionManager $mentionmanager
     */
    public function edtList(
        Request $request,
        EnseignantManager $manager,
        CalendrierPaiementManager   $calPaiementManager,
        CalendrierPaiementRepository $calPaiementRepo,
        EnseignantMentionManager $enseMentionManager,
        MentionManager $mentionmanager,
        EmploiDuTempsManager $emploiDuTempsManager,
        SallesManager $sallesManager,
        EmploiDuTempsRepository $emploiDuTempsRepository,
        AnneeUniversitaireService $anneeUniversitaireService,
        NiveauManager $niveauManager,
        PaginatorInterface $paginator
    ) {
        $user = $this->getUser();
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
        $calPaiements = $calPaiementManager->loadAll();
        $niveaux = $niveauManager->loadAll();
        // dump($user);die;
        $anneeUniv = $anneeUniversitaireService->getCurrent();
        $emploiDuTemps = $emploiDuTempsManager->getListActive($user->getMention()->getId(), null, $selectedCalPaiement, $n, $w, $d);
        $page = $request->query->getInt('page', 1);
        $pagination = $paginator->paginate(
            $emploiDuTemps, // Vos données à paginer
            $page, // Numéro de la page
            $itemsPerPage=20 // Nombre d'éléments par page
        );

        return $this->render(
            'frontend/assistant/edtList.html.twig',
            [
                'c'                         => $selectedCalPaiement->getId(),
                'calPaiements'              => $calPaiements,
                'pagination'                => $pagination,
                'monthWeeks'                => $monthWeeks,
                'niveaux'                   => $niveaux,
                'n'                         => $n,
                'w'                         => $w,
                'd'                         => $d
            ]
        );
    }

    /**
     * @Route("/gestion-emploi-temps/add", name="front_gestion_emploi_du_temps_add",  methods={"GET", "POST"})
     * @IsGranted("ROLE_ASSISTANT")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\EnseignantMatiere                 $enseignantMatiere
     * @param \App\Manager\EnseignantManager                $manager
     * @param \App\Manager\EnseignantMatiereManager         $enseignantMatieremanager
     * @param \App\Manager\EnseignantMentionManager         $enseignantMentionmanager
     * @param \App\Manager\MentionManager                   $mentionmanager
     * @param \App\Manager\NiveauManager                    $niveaumanager
     * @param \App\Repository\UniteEnseignementsManager     $ueManager,
     * @param \App\Repository\MatiereRepository             $matRepository,
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameter
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function edtNew(
        Request                     $request,
        EnseignantManager           $manager,
        MentionManager              $mentionManager,
        ParcoursManager             $parcoursManager,
        EnseignantMentionManager    $enseMentionManager,
        MatiereManager              $matManager,
        EmploiDuTempsManager        $emploiDuTempsManager,
        NiveauManager               $niveaumanager,
        UniteEnseignementsManager   $ueManager,
        MatiereRepository           $matRepository,
        SallesManager               $sallesManager,
        ParameterBagInterface       $parameter,
        AnneeUniversitaireService   $anneeUniversitaireService
    ) : Response
    {
        $user               = $this->getUser();
        $mention            = $mentionManager->loadOneBy(array("id" => $user->getMention()->getId()));
        $parcours           = $parcoursManager->loadBy(['mention' => $mention]);
        $niveaux            = $niveaumanager->loadAll();

        /** @var EmploiDuTemps $emploiDuTemps */
        $emploiDuTemps = $emploiDuTempsManager->createObject();
        $emploiDuTemps->setMention($mention);
        $form   = $this->createForm(
            EmploiDuTempsType::class,
            $emploiDuTemps
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $emploiDuTemps->setStatut(EmploiDuTemps::STATUS_CREATED);
            $emploiDuTemps->setCreatedAt(new \DateTime());
            $emploiDuTemps->setAnneeUniversitaire($anneeUniversitaireService->getCurrent());
            $emploiDuTempsManager->save($emploiDuTemps);
            return $this->redirectToRoute('front_gestion_emploi_du_temps');
        } else {
            //dump($form);
        }

        return $this->render(
            'frontend/assistant/edtAdd.html.twig',
            [
                'parcours'              => $parcours,
                'niveaux'               => $niveaux,
                'uniteEnseignements'    => [],
                'matieres'              => [],
                'semestres'             => [],
                'form'                  => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/gestion-emploi-temps/{id}/edit", name="front_gestion_emploi_du_temps_edit",  methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\MentionManager                   $mentionmanager
     * @param \App\Manager\NiveauManager                    $niveaumanager
     * @param \App\Repository\UniteEnseignementsManager     $ueManager,
     * @param \App\Repository\MatiereRepository             $matRepository,
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameter
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function edtEdit(
        Request                     $request,
        MentionManager              $mentionManager,
        NiveauManager               $niveaumanager,
        SemestreManager            $semestreManager,
        ParcoursManager             $parcoursManager,
        MatiereManager              $matManager,        
        UniteEnseignementsManager   $ueManager,
        MatiereRepository           $matRepository,
        SallesManager               $sallesManager,
        ParameterBagInterface       $parameter,
        EmploiDuTemps               $emploiDuTemps
    ) : Response
    {
        $user               = $this->getUser();
        $mention            = $mentionManager->loadOneBy(array("id" => $user->getMention()->getId()));
        $niveaux            = $niveaumanager->loadAll();
        $semestres          = $semestreManager->loadBy(['niveau' => $emploiDuTemps->getNiveau()]);
        $parcoursList       = $parcoursManager->loadBy(['mention' => $mention, 'niveau' => $emploiDuTemps->getNiveau()]);
        $ueOptions = [];
        $ueOptions['mention'] = $mention;
        $ueOptions['niveau'] = $emploiDuTemps->getNiveau();
        $ueOptions['semestre'] = $emploiDuTemps->getSemestre();
        if($parcours = $emploiDuTemps->getParcours())
            $ueOptions['parcours'] = $parcours;
        $uniteEnseignements = $ueManager->loadBy($ueOptions);
        $matieres           = $matManager->loadBy(['uniteEnseignements' => $emploiDuTemps->getUe()]);
        $dateSchedule = date_format($emploiDuTemps->getDateSchedule(), "Y-m-d");
        $startTime = date_format($emploiDuTemps->getStartTime(), "H:i");
        $endTime = date_format($emploiDuTemps->getEndTime(), "H:i");
        $salles = $sallesManager->getEdtSalle($emploiDuTemps->getId());

        $form   = $this->createForm(
            EmploiDuTempsType::class,
            $emploiDuTemps,
            [
                'emploiDuTemps' => $emploiDuTemps,
                'em'            => $this->getDoctrine()->getManager()
            ]
        );

        // dump($form);die;

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $emploiDuTemps->setUpdatedAt(new \DateTime());
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('front_gestion_emploi_du_temps');
        }
        else{
            dump($form);
        }

        return $this->render(
            'frontend/assistant/edtEdit.html.twig',
            [
                'niveaux'               => $niveaux,
                'semestres'             => $semestres,
                'parcours'              => $parcoursList,
                'uniteEnseignements'    => $uniteEnseignements,
                'matieres'              => $matieres,
                'salles'                => $salles,
                'currentSalle'          => $emploiDuTemps->getSalles()->getId(),
                'currentMatiere'        => $emploiDuTemps->getMatiere()->getId(),
                'form'                  => $form->createView(),
                'emploiDuTemps'         => $emploiDuTemps,
            ]
        );
    }

    /**
     * @Route("/gestion-emploi-temps/{id}/delete", name="front_gestion_emploi_du_temps_delete",  methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\EnseignantMatiere                 $enseignantMatiere
     * @param \App\Manager\EnseignantManager                $manager
     * @param \App\Manager\EnseignantMatiereManager         $enseignantMatieremanager
     * @param \App\Manager\EnseignantMentionManager         $enseignantMentionmanager
     * @param \App\Manager\MentionManager                   $mentionmanager
     * @param \App\Manager\NiveauManager                    $niveaumanager
     * @param \App\Repository\UniteEnseignementsManager     $ueManager,
     * @param \App\Repository\MatiereRepository             $matRepository,
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameter
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function edtDelete(
        Request                     $request,
        EnseignantManager           $manager,
        ParameterBagInterface       $parameter,
        EmploiDuTemps               $emploiDuTemps
    ) : Response
    {
        if ($this->isCsrfTokenValid('delete' . $emploiDuTemps->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($emploiDuTemps);
            $entityManager->flush();
            $this->addFlash('success', 'Succès suppression');
        }

        return $this->redirectToRoute('front_gestion_emploi_du_temps');
    }

    /**
     * @Route("/niveaux/effectif", name="front_assistant_edt_niveaux",  methods={"GET", "POST"})
     * @param Request $request
     * @param MentionManager $mentionManager
     * @param EnseignantManager $ensManager
     * @param UniteEnseignementsManager $ueManager
     * @param SemestreManager $semestreManager
     * @param EtudiantManager $etudiantManager
     * @param SallesManager $sallesManager
     * @param EmploiDuTempsManager $emploiDuTempsManager
     * @param ParameterBagInterface $parameter
     * @return Response
     */
    public function edtEffectifNiveaux(
        Request                     $request,
        MentionManager              $mentionManager,
        EnseignantManager           $ensManager,
        UniteEnseignementsManager   $ueManager,
        SemestreManager             $semestreManager,
        EtudiantManager             $etudiantManager,
        SallesManager               $sallesManager,
        EmploiDuTempsManager        $emploiDuTempsManager,
        ParameterBagInterface       $parameter
    ) : Response
    {
        $user           = $this->getUser();
        $mention        = $mentionManager->loadBy(array("id" => $request->query->getInt('mentionId')));
        $mentionId      = $request->query->getInt('mentionId');
        $niveauId       = $request->query->getInt('niveauId');
        $parcoursId       = $request->query->getInt('parcoursId');
        $salles       = $sallesManager->loadAll();
        $sallesList   = $emploiDuTempsManager->loadBy(
            array(
                "id" => $mentionId
            )
        );
        //Chercher l'effectif des étudiants par mention et par niveaux...
        $effectifs    = $etudiantManager->countAllByMentionAndNiveau($mentionId,$niveauId, $parcoursId);

        return $this->render(
            'frontend/assistant/edtEffectif.html.twig',
            [
                'salles'          => $salles,
                'sallesList'      => $sallesList,
                'mention'         => $mention,
                'effectifs'       => $effectifs,
                'niveauId'        => $request->get('niveauId')
            ]
        );
    }

    /**
     * @Route("/salles/ue", name="front_assistant_edt_salles",  methods={"GET", "POST"})
     * @param Request $request
     * @param MentionManager $mentionManager
     * @param EnseignantManager $ensManager
     * @param UniteEnseignementsManager $ueManager
     * @param SemestreManager $semestreManager
     * @param SallesManager $sallesManager
     * @param EmploiDuTempsManager $emploiDuTempsManager
     * @param EtudiantManager $etudiantManager
     * @param ParameterBagInterface $parameter
     * @return Response
     */
    public function edtGetUeOptionsBySalles(
        Request                     $request,
        MatiereManager              $matiereManager,
        MentionManager              $mentionManager,
        EnseignantManager           $ensManager,
        UniteEnseignementsManager   $ueManager,
        SemestreManager             $semestreManager,
        SallesManager               $sallesManager,
        EmploiDuTempsManager        $emploiDuTempsManager,
        EtudiantManager             $etudiantManager,
        ParameterBagInterface       $parameter
    ) : Response
    {        
        //Post datas
        $dateSchedule       = $request->get('dateSchedule');
        $startTime          = $request->get('startTime');
        $endTime            = $request->get('endTime');
        $mentionId          = $request->query->getInt('mentionId');
        $niveauId           = $request->query->getInt('niveauId');
        $parcoursId         = $request->query->getInt('parcoursId');
        $matiereId          = $request->query->getInt('matiereId');
        $connexion          = $request->query->getInt('connexion');
        $videoProjecteur    = $request->query->getInt('videoProjecteur');
        $TroncCommun        = $request->query->getInt('troncCommun');

        $formatedStarttime = UtilFunctionService::timeJsToMysqlTime($startTime);
        $formatedEndtime = UtilFunctionService::timeJsToMysqlTime($endTime);
        
        $matiere = $matiereManager->load($matiereId);
        $enseignant = $matiere->getEnseignant();
        $effectifs    = $etudiantManager->countAllByMentionAndNiveau($mentionId,$niveauId, $parcoursId);
        // $sallesList   = $emploiDuTempsManager->getEdtByParams($params);
        $salles = $sallesManager->getAllByMixedFilters(
            [
                'mention'   => $mentionId,
                'niveau'    => $niveauId,
                'parcours'  => $parcoursId,
                'date'      => $dateSchedule,
                'startTime' => $formatedStarttime,
                'endTime'   => $formatedEndtime,
                'capacite'  => $effectifs,
                'connexion' => $connexion,
                'matiereId' => $matiereId,
                'matiere'       => $matiere->getNom(),
                'videoProjecteur'   => $videoProjecteur,
                'enseignant'        => $enseignant ? $enseignant->getId() : 0,
                'troncCommun'       => $TroncCommun
            ]
        );
        // dump($salles);die;
        return $this->render(
            'frontend/assistant/_salle_ue_options.html.twig',
            [
                'salles'          => $salles,
                'effectifs'       => $effectifs
            ]
        );
    }

    /**
     * @Route("/edt/parent-child/options", name="front_edt_ajax_options",  methods={"GET", "POST"})
     * @param Request $request
     * @param NiveauManager $niveaumanager
     * @param SemestreManager $semManager
     * @param UniteEnseignementsManager $ueManager
     * @param MatiereManager $matiereManager
     * @return Response
     */
    public function edtAjaxOptions(
        Request                     $request,
        NiveauManager               $niveaumanager,
        ParcoursManager             $parcoursManager,
        SemestreManager             $semManager,
        UniteEnseignementsManager   $ueManager,
        MatiereManager $matiereManager
    )
    {
        $user           = $this->getUser();
        $mention        = $user->getMention();

        $parentName     = $request->get('parent_name');
        $parentValue    = $request->get('parent_id');
        $parcoursId     = $request->get('parcours_id');

        $mentions       = [];
        $parcours       = [];
        $uniteEnseignements = [];
        $matieres       = [];
        $niveauId       = $request->get('niveau_id');
        $childTarget    = $request->get('child_target');

        $semestres = [];
        switch($parentName) {
            case 'niveau':
                if($childTarget && $childTarget === "semestre")
                    $semestres   = $semManager->loadBy(['niveau' => $parentValue]);
                if($childTarget && $childTarget === "parcours")
                    $parcours    = $parcoursManager->loadBy(['mention' => $mention, 'niveau' => $parentValue]);
                break;
            case 'semestre':
                $filterOptions = [];
                $filterOptions['mention']   = $mention->getId();
                $filterOptions['niveau']    = $niveauId;
                $filterOptions[$parentName] = $parentValue;
                if(!empty($parcoursId)) {
                    $filterOptions['parcours'] = $parcoursId;
                }
                // dump($filterOptions);die;
                $uniteEnseignements   = $ueManager->loadBy($filterOptions);
                break;
            case 'uniteEnseignements':
                $matieres = $matiereManager->loadBy([
                    $parentName => $parentValue
                ]);
                break;
            default:
                break;
        }

        return $this->render(
            'frontend/assistant/edt/_ajax_options.html.twig',
            [
                'mentions'              => $mentions,
                'parcours'              => $parcours,
                'semestres'             => $semestres,
                'uniteEnseignements'    => $uniteEnseignements,
                'matieres'              => $matieres
            ]
        );
    }

    /**
     * @Route("/classroom", name="front_classroom")
     * @IsGranted("ROLE_ASSISTANT")
     * @param EnseignantManager $manager
     * @param EnseignantMentionManager $enseMentionManager
     * @param MentionManager $mentionmanager
     */
    public function salleList(
        EnseignantManager $manager,
        EnseignantMentionManager $enseMentionManager,
        MentionManager $mentionmanager,
        EmploiDuTempsManager $emploiDuTempsManager,
        SallesManager $sallesManager,
        EmploiDuTempsRepository $emploiDuTempsRepository

    ) {
        $salles       = $sallesManager->loadAll();
        return $this->render(
            'frontend/assistant/salleList.html.twig',
            [
                'salles'          => $salles
            ]
        );
    }

    /**
     * @Route("/classroom/add", name="front_classroom_add",  methods={"GET", "POST"})
     * @param Request $request
     * @param SallesManager $sallesManager
     * @param ParameterBagInterface $parameter
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */

    public function salleNew(
        Request                     $request,
        SallesManager               $sallesManager,
        ParameterBagInterface       $parameter
    ) : Response
    {

        $user           = $this->getUser();
        $salles         = $sallesManager->loadAll();
        $salle          = $sallesManager->createObject();
        $form           = $this->createForm(
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
            'frontend/assistant/salleAdd.html.twig',
            [
                'salles'          => $salles,
                'form'            => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/classroom/{id}/edit", name="front_assistant_edit_salle",  methods={"GET", "POST"})
     * @param Request $request
     * @param SallesManager $sallesManager
     * @param ParameterBagInterface $parameter
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */

    public function salleEdit(
        Request                     $request,
        SallesManager               $sallesManager,
        Salles                      $salles,
        ParameterBagInterface       $parameter
    ) : Response
    {
        $user           = $this->getUser();
        $form = $this->createForm(SallesType::class, $salles);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('front_classroom');
        }

        return $this->render(
            'frontend/assistant/salleEdit.html.twig',
            [
                'salles'          => $salles,
                'form'            => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/vacations/list", name="front_assistant_presence_enseignant_index", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\EmploiDuTempsManager $edtManager
     * @param \App\Manager\CalendrierPaiementManager $calPaiementManager
     * @param \App\Service\AnneeUniversitaireService $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function fichePresenceEnseignantList(
        Request                         $request, 
        EmploiDuTempsManager            $edtManager,
        CalendrierPaiementManager       $calPaiementManager,
        CalendrierPaiementRepository    $calPaiementRepo,
        AnneeUniversitaireService       $anneeUniversitaireService)
    {
        $c = $request->get('c', '');
        $selectedCalPaiement = $c? $calPaiementManager->load($c) : $calPaiementRepo->findDefault();
        $user               = $this->getUser();
        $calPaiements = $calPaiementManager->loadAll();
        $currentAnneUniv    = $anneeUniversitaireService->getCurrent();
        $fPresenceEnseignants = $edtManager->getCurrentVacation(
            $currentAnneUniv->getId(),
            $user->getMention()->getId(),
            $selectedCalPaiement
        );

        // dump($fPresenceEnseignants);die;

        return $this->render(
            'frontend/assistant/presence-enseignant-list.html.twig',
            [
                'c'                     => $selectedCalPaiement ? $selectedCalPaiement->getId() : 0,
                'calPaiements'          => $calPaiements,
                'fPresenceEnseignants'  => $fPresenceEnseignants
            ]
        );
    }

    /**
     * @Route("/vacations/enseignant/{id}", name="front_assistant_vacation_enseignant_index", methods={"GET", "POST"})
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
        AnneeUniversitaireService       $anneeUniversitaireService)
    {
        $c = $request->get('c', '');
        $selectedCalPaiement = $c? $calPaiementManager->load($c) : "";
        $user               = $this->getUser();
        $calPaiements = $calPaiementManager->loadAll();
        $currentAnneUniv    = $anneeUniversitaireService->getCurrent();
        $fPresenceEnseignants = $edtManager->getEnseignantVacation(
            $enseignant->getId(),
            $currentAnneUniv->getId(),
            $user->getMention()->getId(),
            $selectedCalPaiement
        );

        return $this->render(
            'frontend/assistant/vacation/enseignant-index.html.twig',
            [
                'c'                     => $c,
                'enseignant'            => $enseignant,
                'calPaiement'           => $selectedCalPaiement,
                'fPresenceEnseignants'  => $fPresenceEnseignants
            ]
        );
    }  

    /**
     * @Route("/vacation/enseignant/{id}/edit", name="front_assistant_vacation_enseignant_edit", methods={"GET", "POST"})
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
        CalendrierPaiementRepository    $calPaiementRepo,
        ParameterBagInterface           $parameter,
        AnneeUniversitaireService       $anneeUniversitaireService,
        WorkflowStatutService           $workflowStatService) : Response
    {
        $c = $request->get('c', '');
        $selectedCalPaiement = $c ? $calPaiementManager->load($c) : $calPaiementRepo->findDefault();
        $user               = $this->getUser();
        $profilListStatut = $workflowStatService->getStatutForListByProfil($this->getUser()->getRoles());
        $currPrestNextStatut = $workflowStatService->getResourceNextStatut($profilListStatut);
        if($request->isMethod('POST')) {
            $edtTroncCommunSiblings = $edtManager->loadby(
                [
                    'dateSchedule'  => $edt->getDateSchedule(),
                    'startTime'     => $edt->getStartTime(),
                    'endTime'       => $edt->getEndTime(),
                    'salles'        => $edt->getSalles()
                ]
            );

            // dump($edtTroncCommunSiblings);die;
            $edtFormData    = $request->get('emploi_du_temps');
            $edtFiles       = $request->files->get('emploi_du_temps');
            $directory      = $parameter->get('vacation_desc_directory');
            $uploader       = new \App\Services\FileUploader($directory);
            $today          = new \DateTime();
            $targetDirName = "";
            $descriptionFileDisplay = "";

            if($descriptionFile = $edtFiles['descriptionPath']) {
                $targetDirName = $edt->getMatiere()->getCode() . "-" . $today->getTimestamp();
                $descriptionFileDisplay = $uploader->upload($descriptionFile, $directory, $targetDirName, false);
            }

            $entityManager = $this->getDoctrine()->getManager();
            foreach ($edtTroncCommunSiblings as $edt) {
                if($descriptionFileDisplay)
                    $edt->setDescriptionPath($targetDirName . "/" . $descriptionFileDisplay["filename"]);
                $edt->setDescription($edtFormData['description']);
                $edt->setCommentaire($edtFormData['commentaire']);
                $bChechNextStatutVal = $edt->getStatut() == $profilListStatut;
                if($bChechNextStatutVal) {
                    $currPrestNextStatut = $workflowStatService->getResourceNextStatut($profilListStatut);
                } else {
                    $currPrestNextStatut = $workflowStatService->getEdtPreviousStatut($edt->getStatut());
                    $fileSystem = new FileSystem();
                    $tfileDirectory = explode('/', $edt->getDescriptionPath());
                    $fileDirectory = array_shift($tfileDirectory);
                    $fileSystem->remove($directory . '/' . $fileDirectory);
                }            

                $edt->setStatut($currPrestNextStatut);
                $entityManager->persist($edt);
            }

            //update matiere volume horaire
      
            $edtTimeDiff = date_diff($edt->getStartTime(), $edt->getEndTime());
            $matiere = $edt->getMatiere();
            $vhRest = $matiere->getVolumeHoraireTotal() - $matiere->getVolumeHoraire();
            if( $vhRest > 0 && $vhRest >= $edtTimeDiff->h) {
                $bChechNextStatutVal ? $matiere->setVolumeHoraire($vhRest - $edtTimeDiff->h) : $matiere->setVolumeHoraire($vhRest + $edtTimeDiff->h);
                $entityManager->persist($matiere);
            }
                
            $prestHistory = new PaiementHistory();
            $prestHistory->setResourceName(PaiementHistory::VACATION_RESOURCE);
            $prestHistory->setStatut($currPrestNextStatut);
            $prestHistory->setResourceId($edt->getId());
            $prestHistory->setValidator($this->getUser());
            $prestHistory->setCreatedAt(new \DateTime());
            $prestHistory->setUpdatedAt(new \DateTime());
            $prestHistory->setMontant(0);
            $entityManager->persist($prestHistory);

            $entityManager->flush();

            return $this->redirectToRoute('front_assistant_presence_enseignant_index');
        }
        
        return $this->render(
            'frontend/assistant/vacation/enseignant-edit.html.twig',
            [
                'profilListStatut'  => $profilListStatut,
                'currPrestNextStatut' => $currPrestNextStatut, 
                'c'             => $selectedCalPaiement ? $selectedCalPaiement->getId() : 0,
                'edt'           => $edt
            ]
        );
    }   

    /**
     * @Route("/presence-enseignant/matiere/{id}/fiche", name="front_assistant_presence_enseignant_matiere_fiche", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Matiere     $matiere
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function fichePresenceEnseignantFiche(Request $request, Matiere $matiere, FichePresenceEnseignantManager $ficheEnseignantManager)
    {
        $fPresenceEnseignants = $ficheEnseignantManager->getTeacherMatiereDetailHour($matiere->getId());
        
        return $this->render(
            'frontend/assistant/presence-enseignant-fiche.html.twig',
            [
                'matiere'               => $matiere,
                'fPresenceEnseignants'  => $fPresenceEnseignants
            ]
        );
    }

    /**
     * @Route("/presence-enseignant/edit/{id}", name="front_assistant_presence_enseignant_edit", methods={"GET", "POST"})
     * @param Request $request
     * @param FichePresenceEnseignant $fichePresenceEnseignant
     * @param FichePresenceEnseignantManager $ficheEnseignantManager
     * @param EnseignantManager $enseignantManager
     * @param MentionManager $mentionManager
     * @param MatiereManager $matiereManager
     * @param DepartementManager $departementManager
     * @param UniteEnseignementsManager $ueManager
     * @param NiveauManager $niveauManager
     * @param ParameterBagInterface $parameter
     * @return Response
     */
    public function fichePresenceEnseignantEdit(
        Request                         $request,
        FichePresenceEnseignant         $fichePresenceEnseignant,
        FichePresenceEnseignantManager  $ficheEnseignantManager,
        EnseignantManager               $enseignantManager,
        MentionManager                  $mentionManager,
        MatiereManager                  $matiereManager,
        DepartementManager              $departementManager,
        UniteEnseignementsManager       $ueManager,
        NiveauManager                   $niveauManager,
        ParcoursManager                 $parcoursManager,
        ParameterBagInterface           $parameter,
        FichePresenceEnseignantHistoriqueManager $historiqueManager
    ) : Response
    {
        $mention        = $mentionManager->loadOneBy(array("id" => $fichePresenceEnseignant->getMention()->getId()));
        $enseignant     = $enseignantManager->loadOneBy(array("id" => $fichePresenceEnseignant->getEnseignant()->getId()));
        $matiere        = $matiereManager->loadOneBy(array("id" => $fichePresenceEnseignant->getMatiere()->getId()));
        $departement    = $departementManager->loadOneBy(array("id" => $fichePresenceEnseignant->getDomaine()->getId()));
        $ue             = $ueManager->loadOneBy(array("id" => $fichePresenceEnseignant->getUe()->getId()));
        $niveau         = $niveauManager->loadOneBy(array("id" => $fichePresenceEnseignant->getNiveau()->getId()));

        $form   = $this->createForm(
            FichePresenceEnseignantType::class,
            $fichePresenceEnseignant,
            [
                'fichePresenceEnseignant'   => $fichePresenceEnseignant,
                'em'                        => $this->getDoctrine()->getManager()
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
            $fichePresenceEnseignant->setStatut(FichePresenceEnseignant::STATUS_VERIFIED);
            $this->getDoctrine()->getManager()->flush();

            /** @var \App\Entity\FichePresenceEnseignantHistorique $historical */
            $historical = $historiqueManager->createObject();
            $historical->setUser($this->getUser());
            $historical->setFichePresenceEnseignant($fichePresenceEnseignant);
            $historical->setStatus(FichePresenceEnseignant::STATUS_VERIFIED);
            $historiqueManager->save($historical);

            return $this->redirectToRoute('front_assistant_presence_enseignant_index');
        }
        else{
            dump($form);
        }
        return $this->render(
            'frontend/assistant/presence-enseignant-edit.html.twig',
            [
                'fpEnseignant' => $fichePresenceEnseignant,
                'form'          => $form->createView()
            ]
        );
    }

    /**
     * @Route("/list-demande-doc", name="front_assistant_demande_doc_list")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\DemandeDocManager            $demandeDocManager
     * @IsGranted("ROLE_ASSISTANT")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function demandeDocList(
        Request $request,
        DemandeDocManager $demandeDocManager,
        EtudiantManager   $etudiantManager
    )
    {
        $currentUser       = $this->getUser();
        $etudiants         = $etudiantManager->loadBy(
            [
                'mention' => $currentUser->getMention()->getId(),
            ]
        );
        $etudiantList = array();
        foreach ($etudiants as $key => $value) {
            $etudiantList[] = $value->getId();
        }
        $demandeDoc       = $demandeDocManager->getDemandeDocByParams(implode(',', $etudiantList), DemandeDoc::TYPE_LETTRE_INTRO);
        return $this->render(
            'frontend/assistant/demande-doc-list.html.twig',
            [
                'demandesDoc' => $demandeDoc
            ]
        );
    }

    /**
     * @Route("/edit-demande-doc/{id}/edit", name="front_assistant_demande_doc_edit", methods="GET|POST")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\DemandeDocManager            $demandeDocManager
     * @param \App\Entity\DemandeDoc                    $demandeDoc
     * @param \App\Manager\DemandeDocHistoriqueManager  $historiqueManager
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @IsGranted("ROLE_ASSISTANT")
     */
    public function demandeDocEdit(
        Request             $request,
        DemandeDocManager   $demandeDocManager,
        DemandeDoc          $demandeDoc,
        DemandeDocHistoriqueManager $historiqueManager
    )
    {
        $user           = $this->getUser();
        $form           = $this->createForm(
            DemandeType::class,
            $demandeDoc,
            [
                'demande' => $demandeDoc,
                'em'       => $this->getDoctrine()->getManager()
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

            if($extraData['status']== DemandeDoc::STATUS_VALIDATED){
                $this->demandeDocToPdf($request,$demandeDocManager,$demandeDoc);
            }
            return $this->redirectToRoute('front_assistant_demande_doc_list');
        }
        
        $template = ($demandeDoc->getType()->getCode() == "LETTRE_INTRODUCTION") ? "frontend/etudiant/demande-doc.html.twig" : "frontend/assistant/demande-doc-status.html.twig";

        return $this->render(
            $template,
            [
                'demandeDoc'    => $demandeDoc,
                'form'          => $form->createView()
            ]
        );
    }
    
    /**
     * @Route("/edit-demande-doc/status", name="front_assistant_demande_doc_status", methods="GET|POST")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\DemandeDocManager            $demandeDocManager
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function demandeDocStatus(
        Request             $request,
        DemandeDocManager   $demandeDocManager
    )
    {
        $user   = $this->getUser();
        $docId  = $request->get('docId');
        $status = $request->get('status');

        $demandeDoc = $demandeDocManager->loadOneBy(["id" => $docId]);
        $demandeDoc->setStatut($status);
        $demandeDocManager->persist($demandeDoc);
        $demandeDocManager->flush();

        if ($status == DemandeDoc::STATUS_VALIDATED) {
            $this->demandeDocToPdf($request, $demandeDocManager, $demandeDoc);
        }

        return $this->render(
            'frontend/assistant/demande-doc-list.html.twig',
            [
                'demandesDoc' => $demandeDoc,
            ]
        );
    }

    /**
     * @param Request $request
     * @param DemandeDocManager $demandeDocManager
     * @param DemandeDoc $demandeDoc
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function demandeDocToPdf(
        Request             $request,
        DemandeDocManager   $demandeDocManager,
        DemandeDoc          $demandeDoc
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
                'site'          => $this->getParameter('site'),
                'demandeDoc'    => $demandeDoc,
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
     * @Route("/calendrier-soutenances/ajout", name="front_thesis_calendar_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_ASSISTANT")
     * @param \Symfony\Component\HttpFoundation\Request          $request
     * @param \App\Manager\CalendrierSoutenanceManager           $manager
     * @param \App\Services\AnneeUniversitaireService            $anneeUniversitaireService
     * @param \App\Manager\CalendrierSoutenanceHistoriqueManager $historiqueManager
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function thesisCalendarNew(
        Request $request,
        CalendrierSoutenanceManager $manager,
        AnneeUniversitaireService $anneeUniversitaireService,
        CalendrierSoutenanceHistoriqueManager $historiqueManager
    ) {
        $user = $this->getUser();
        /** @var \App\Entity\CalendrierSoutenance $thesisCalendar */
        $thesisCalendar = $manager->createObject();
        $form   = $this->createForm(
            ThesisCalendarType::class,
            $thesisCalendar,
            [
                'user' => $user
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $thesisCalendar->setAnneeUniversitaire($anneeUniversitaireService->getCurrent());
            $manager->save($thesisCalendar);

            /** @var \App\Entity\CalendrierSoutenanceHistorique $historical */
            $historical = $historiqueManager->createObject();
            $historical->setUser($this->getUser());
            $historical->setStatus(CalendrierSoutenance::STATUS_CREATED);
            $historical->setCalendrierSoutenance($thesisCalendar);
            $historiqueManager->save($historical);

            return $this->redirectToRoute('front_thesis_calendar_list');
        }

        return $this->render(
            'frontend/assistant/thesis/new.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/calendrier-soutenances/{id}/edit", name="front_thesis_calendar_edit", methods={"GET", "POST"})
     * @IsGranted("ROLE_ASSISTANT")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\CalendrierSoutenance          $calendrierSoutenance
     * @param \App\Manager\CalendrierSoutenanceManager  $manager
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function thesisCalendarEdit(
        Request $request,
        CalendrierSoutenance $calendrierSoutenance,
        CalendrierSoutenanceManager $manager
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

            return $this->redirectToRoute('front_thesis_calendar_list');
        }

        return $this->render(
            'frontend/assistant/thesis/new.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/calendrier-soutenances", name="front_thesis_calendar_list", methods={"GET"})
     * @IsGranted("ROLE_ASSISTANT")
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
            'frontend/assistant/thesis/index.html.twig',
            [
                'items' => $calendarList
            ]
        );
    }

    /**
     * @Route("/calendrier-examen/ajout", name="front_examen_calendar_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_ASSISTANT")
     * @param \Symfony\Component\HttpFoundation\Request          $request
     * @param \App\Manager\CalendrierExamenManager           $manager
     * @param \App\Services\AnneeUniversitaireService            $anneeUniversitaireService
     * @param \App\Manager\CalendrierExamenHistoriqueManager $historiqueManager
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function examenCalendarNew(
        Request $request,
        CalendrierExamenManager $manager,
        AnneeUniversitaireService $anneeUniversitaireService,
        CalendrierExamenHistoriqueManager $historiqueManager
    ) {
        $user = $this->getUser();
        /** @var \App\Entity\CalendrierExamen $examenCalendar */
        $examenCalendar = $manager->createObject();
        $form   = $this->createForm(
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
            $historical = $historiqueManager->createObject();
            $historical->setUser($this->getUser());
            $historical->setStatus(WorkflowStatutService::STATUS_CREATED);
            $historical->setCalendrierExamen($examenCalendar);
            $historiqueManager->save($historical);

            return $this->redirectToRoute('front_examen_calendar_list');
        }

        return $this->render(
            'frontend/assistant/examen/new.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/calendrier-Examens/{id}/edit", name="front_examen_calendar_edit", methods={"GET", "POST"})
     * @IsGranted("ROLE_ASSISTANT")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\CalendrierExamen          $calendrierExamen
     * @param \App\Manager\CalendrierExamenManager  $manager
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function examenCalendarEdit(
        Request $request,
        CalendrierExamen $calendrierExamen,
        CalendrierExamenManager $manager
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

            return $this->redirectToRoute('front_examen_calendar_list');
        }

        return $this->render(
            'frontend/assistant/examen/new.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/calendrier-examen", name="front_examen_calendar_list", methods={"GET"})
     * @IsGranted("ROLE_ASSISTANT")
     * @param \App\Manager\CalendrierExamenManager    $calendrierExamenManager
     * @param \App\Services\AnneeUniversitaireService $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function examenCalendarList(
        Request $request,
        CalendrierExamenManager         $calendrierExamenManager,
        AnneeUniversitaireService       $anneeUniversitaireService,
        CalendrierPaiementRepository    $calPaiementRepo,
        CalendrierPaiementManager       $calPaiementManager,
        NiveauManager                   $niveauManager
    ) {
        $user = $this->getUser();
        $currentAnneUniv = $anneeUniversitaireService->getCurrent();
        // $calendarList = $calendrierExamenManager->loadBy(
        //     [
        //         'mention' => $user->getMention(),
        //         //'statut' => CalendrierExamen::STATUS_CREATED,
        //         'anneeUniversitaire' => $anneeUniversitaireService->getCurrent()
        //     ],
        //     [
        //         'dateSchedule' => 'ASC'
        //     ]
        // );

        $c = $request->get('c', '');
        $n = $request->get('n', '');
        $w = $request->get('w', '');
        $d = $request->get('d', '');
        $month = date('m');
        $month = date('m');
        //$year = date('Y');
        $selectedCalPaiement = $c? $calPaiementManager->load($c) : $calPaiementRepo->findDefault();
        $currentMonth = $selectedCalPaiement->getDateDebut()->format('m');
        $currentYear = $selectedCalPaiement->getDateDebut()->format('Y');
        $monthWeeks = UtilFunctionService::getWeekOfMonth($currentMonth, $currentYear);
        $calPaiements = $calPaiementManager->loadAll();
        $niveaux = $niveauManager->loadAll();
        $calendarList = $calendrierExamenManager->getAll(
            $currentAnneUniv->getId(), $user->getMention()->getId(), $selectedCalPaiement, null, $n, $w, $d
        );
        
        return $this->render(
            'frontend/assistant/examen/index.html.twig',
            [
                'items' => $calendarList,
                'c'                         => $selectedCalPaiement->getId(),
                'calPaiements'              => $calPaiements,
                'monthWeeks'                => $monthWeeks,
                'niveaux'                   => $niveaux,
                'n'                         => $n,
                'w'                         => $w,
                'd'                         => $d
            ]
        );
    }

    /**
     * @IsGranted("ROLE_ASSISTANT")
     * @Route("/extra-note", name="front_assistant_extranote_list", methods={"GET"})
     * @param \App\Manager\NiveauManager   $niveaumanager
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
            'frontend/assistant/note/extranote-list.html.twig',
            [
                'm'        => (int) $mentionId,
                'niveaux'  => $niveaux,
            ]
        );
    }

    /**
     * @IsGranted("ROLE_ASSISTANT")
     * @Route("/extra-note/{id}/ajout", name="front_assistant_extranote_new", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Mention                       $mention
     * @param \App\Manager\ExtraNotesManager            $notesManager
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     * @param \App\Manager\ExtraNoteHistoriqueManager   $historicalManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function extraNoteNew(
        Request $request,
        Mention $mention,
        ExtraNotesManager $notesManager,
        AnneeUniversitaireService $anneeUniversitaireService,
        ExtraNoteHistoriqueManager $historicalManager
    ) {
        /** @var \App\Entity\ExtraNote $notes */
        $notes = $notesManager->createObject();

        $form = $this->createForm(
            ExtraNoteType::class,
            $notes,
            [
                'mention'     => $mention,
                'collegeYear' => $anneeUniversitaireService->getCurrent()
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $collegeYear = $anneeUniversitaireService->getCurrent();

            // check uniqueness
            if ($notesManager->isUnique($notes->getEtudiant()->getId(), $collegeYear->getId(), $notes->getType())) {
                $notes->setAnneeUniversitaire($collegeYear);
                $notesManager->save($notes);

                /** @var \App\Entity\ExtraNoteHistorique $historical */
                $historical = $historicalManager->createObject();
                $historical->setStatus($notes->getStatus());
                $historical->setExtraNote($notes);
                $historical->setUser($this->getUser());
                $historicalManager->save($historical);
            }

            return $this->redirectToRoute('front_assistant_extranote_list', ['m' => $mention->getId()]);
        }

        return $this->render(
            'frontend/assistant/note/extranote-new.html.twig',
            [
                'form'  => $form->createView(),
                'notes' => $notes
            ]
        );
    }

    /**
     * @Route("/ajax-parcours", name="front_options_ajax_parcours", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\MentionManager               $mentionManager
     * @param \App\Manager\NiveauManager                $niveauManager
     * @param \App\Manager\ParcoursManager              $parcoursManager
     *
     * @return string
     * @throws \Doctrine\ORM\ORMException
     */
    public function ajaxGetParcoursOptions(
        Request $request,
        MentionManager $mentionManager,
        NiveauManager $niveauManager,
        ParcoursManager $parcoursManager
    ) {

        $mention  = $mentionManager->getReference($request->query->getInt('mentionId'));
        $niveau   = $niveauManager->getReference($request->query->getInt('niveauId'));
        $parcours = $parcoursManager->loadBy(
            [
                'mention' => $mention,
                'niveau'  => $niveau
            ],
            [
                'nom' => 'ASC'
            ]
        );

        return $this->render(
            'frontend/concours/_concours_parcours_options.html.twig',
            [
                'parcours' => $parcours,
                'defaultOpt' => 'Sélectionner',
            ]
        );
    }

    /**
     * @Route("/ajax-get-extra-note", name="front_ajax_get_extra_note", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\ExtraNotesManager            $notesManager
     * @param \App\Manager\MentionManager               $mentionManager
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     * @param \App\Manager\EtudiantManager              $studentManager
     * @param \App\Manager\NiveauManager                $niveaumanager
     * @param \App\Manager\ParcoursManager              $parcoursManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function ajaxGetExtraNote(
        Request $request,
        ExtraNotesManager $notesManager,
        MentionManager $mentionManager,
        AnneeUniversitaireService $anneeUniversitaireService,
        EtudiantManager $studentManager,
        NiveauManager $niveaumanager,
        ParcoursManager $parcoursManager
    ) {
        $mentionId  = (int) $request->get('mentionId', 0);
        $niveauId   = (int) $request->get('niveauId', 0);
        $parcoursId = (int) $request->get('parcoursId', 0);
        if ($mention = $this->getUser()->getMention()) {
            $mentionId = $mention->getId();
        }
        $collegeYear = $anneeUniversitaireService->getCurrent();
        $students    = $studentManager->getByInscriptionAndCollegeYear(
            $mentionManager->getReference($mentionId),
            $collegeYear,
            $niveaumanager->getReference($niveauId),
            ($parcoursId) ? $parcoursManager->getReference($parcoursId) : null
        );
        $notes       = $notesManager->getByStudentsAndCollegeYear($students, $collegeYear);

        return $this->render(
            'frontend/assistant/note/extranote-ajax-list.html.twig',
            [
                'm'             => $mentionId,
                'notes'         => $notes,
                'extraNoteEdit' => $this->getExtraNoteEditRoute(),
            ]
        );
    }

    private function getExtraNoteEditRoute()
    {
        $extraNoteEdit = 'front_cm_extranote_edit';
        if (in_array('ROLE_RECTEUR', $this->getUser()->getRoles())) {
            $extraNoteEdit = 'front_recteur_extranote_edit';
        }
        if (in_array('ROLE_SG', $this->getUser()->getRoles())) {
            $extraNoteEdit = 'front_sg_extranote_edit';
        }
        if (in_array('ROLE_DOYEN', $this->getUser()->getRoles())) {
            $extraNoteEdit = 'front_doyen_extranote_edit';
        }

        return $extraNoteEdit;
    }

    /**
     * @Route("/prestations", name="front_assistant_prestation_index", methods={"GET"})
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
        AnneeUniversitaireService   $anneeUniversitaireService
    ) {
        $mention = $this->getUser()->getMention();
        $c = $request->get('c', '');
        $selectedCalPaiement = $c? $calPaiementManager->load($c) : $calPaiementRepo->findDefault();
        $calPaiements = $calPaiementManager->loadAll();
        $collegeYear = $anneeUniversitaireService->getCurrent();
        $prestations = $prestationManager->getListGroupById($collegeYear->getId(), $mention->getId(), $selectedCalPaiement);

//        dump($prestations);die;

        return $this->render(
            'frontend/assistant/prestation/index.html.twig',
            [
                'c'            => $selectedCalPaiement ? $selectedCalPaiement->getId() : 0,
                'calPaiements' => $calPaiements,
                'list'         => $prestations
            ]
        );
    }

    /**
     * @Route("/prestation/new", name="front_assistant_prestation_new", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\PrestationManager            $prestationManager
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     * @param \App\Manager\MentionManager               $mentionManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function prestationNew(
        Request                     $request,
        MentionManager              $mentionmanager,
        TypePrestationRepository    $typePrestRepo,
        UserRepository              $userRepository,
        ProfilRepository            $profilRepository,
        PrestationManager           $prestationManager,
        AnneeUniversitaireService   $anneeUniversitaireService
    ) {
        $mention = $this->getUser()->getMention();
        $typePrestations = $typePrestRepo->findByMention($mention->getId());
        $prestation = $prestationManager->createObject();
        $form   = $this->createForm(
            PrestationType::class,
            $prestation,
            [
                'profil_repo' => $profilRepository,
                'mention'   => $mention
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $prestation->setStatut(EmploiDuTemps::STATUS_CREATED);
            $prestation->setCreatedAt(new \DateTime());
            $prestation->setUpdatedAt(new \DateTime());
            $prestation->setAnneeUniversitaire($anneeUniversitaireService->getCurrent());
            $prestation->setAuteur($this->getUser());
            $prestation->setMention($this->getUser()->getMention());
            $entityManager = $this->getDoctrine()->getManager();
            foreach($form['user']->getData() as $user){
                $prestTemp = clone($prestation);
                $prestTemp->setUser($user);
                $entityManager->persist($prestTemp);
            }
            $entityManager->flush();
            return $this->redirectToRoute('front_assistant_prestation_index');
        } else{
            dump($form);
        }

        return $this->render(
            'frontend/assistant/prestation/new.html.twig',
            [
                'typePrestations'   => $typePrestations,
                'prestation'        => $prestation,
                'form'              => $form->createView()
            ]
        );
    }

    /**
     * @Route("/prestation/{id}/edit", name="front_assistant_prestation_edit", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\PrestationManager            $prestationManager
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     * @param \App\Manager\MentionManager               $mentionManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function prestationEdit(
        Request                     $request,
        Prestation                  $prestation,
        PrestationUserRepository    $prestUserRepository,
        MentionManager              $mentionmanager,
        TypePrestationRepository    $typePrestRepo,
        UserRepository              $userRepository,
        ProfilRepository            $profilRepository,
        PrestationManager           $prestationManager,
        AnneeUniversitaireService   $anneeUniversitaireService
    ) {
        
        $mention = $this->getUser()->getMention();
        $typePrestations = $typePrestRepo->findByMention($mention->getId());
        $form   = $this->createForm(
            PrestationType::class,
            $prestation,
            [
                'profil_repo' => $profilRepository,
                'mention'   => $mention
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $prestation->setMention($this->getUser()->getMention());
            $prestation->setUpdatedAt(new \DateTime());
            $entityManager->persist($prestation);
            foreach($form['user']->getData() as $user){
                $prestTemp = $prestation;
                $prestTemp->setUser($user);
                $entityManager->persist($prestTemp);
            }
            $entityManager->flush();
            return $this->redirectToRoute('front_assistant_prestation_index');
        } else{
            dump($form);
        }

        return $this->render(
            'frontend/assistant/prestation/new.html.twig',
            [
                'typePrestations'   => $typePrestations,
                'prestation'        => $prestation,
                'form'              => $form->createView()
            ]
        );
    }

    /**
     * @Route("/resume/prestations/", name="front_assistant_me_prestation_index", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\PrestationManager            $prestationManager
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     * @param \App\Manager\MentionManager               $mentionManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function mePrestationIndex(
        Request                     $request,
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
        $prestations = $prestationManager->getListResumeByUser(
            $this->getUser()->getId(),
            $collegeYear->getId(), 
            $mention->getId(), 
            $selectedCalPaiement,
            null
        );

        // dump($prestations);die;

        return $this->render(
            'frontend/assistant/prestation/resume-index.html.twig',
            [
                'c'            => $selectedCalPaiement ? $selectedCalPaiement->getId() : 0,
                'author'       => $this->getUser(), 
                'calPaiements' => $calPaiements,
                'profilListStatut' => $profilListStatut,
                'list'         => $prestations
            ]
        );
    }

    /**
     * @Route("/prestations/{id}/validatable/", name="front_assistant_valid_prestation_index", methods={"GET"})
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
        ParameterBagInterface       $parameter
    ) {
        // dump($parameter->get('version'));die;
        $mention = $this->getUser()->getMention();
        $c = $request->get('c', 0);
        $selectedCalPaiement = $c? $calPaiementManager->load($c) :$calPaiementRepo->findDefault();
        $calPaiements = $calPaiementManager->loadAll();
        $collegeYear = $anneeUniversitaireService->getCurrent();
        $prestations = $prestationManager->getUserValidatablePrestation(
            $user->getId(),
            $collegeYear->getId(), 
            $mention->getId(), 
            $selectedCalPaiement,
            null
        );

        return $this->render(
            'frontend/assistant/prestation/validatable-index.html.twig',
            [
                'c'            => $selectedCalPaiement ? $selectedCalPaiement->getId() : 0,
                'author'       => $this->getUser(), 
                'calPaiements' => $calPaiements,
                'list'         => $prestations
            ]
        );
    }

    /**
     * @Route("/validate/prestations/", name="front_assistant_prestation_validation", methods={"POST"})
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
        PaiementHistoryManager      $presHistorytManager,
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
            $prestHistory->setStatut($currPrestNextStatut);
            $prestHistory->setResourceName(PaiementHistory::PRESTATION_RESOURCE);
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
     * @Route("/surveillances/assignement", name="front_assistant_surveillance_assing_index", methods={"GET"})
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
    public function surveillanceAssingIndex(
        Request                     $request,
        MentionManager              $mentionmanager,
        CalendrierExamenManager     $calExamenManager,
        CalendrierPaiementManager   $calPaiementManager,
        CalendrierPaiementRepository   $calPaiementRepo,
        AnneeUniversitaireService   $anneeUniversitaireService
    ) {

        $c = $request->get('c', '');
        $selectedCalPaiement = $c? $calPaiementManager->load($c) : $calPaiementRepo->findDefault();
        $user               = $this->getUser();
        $calPaiements = $calPaiementManager->loadAll();
        $currentAnneUniv    = $anneeUniversitaireService->getCurrent();
      
      
        // $surveillants = $calExamenManager->loadBy(
        //     [
        //         'mention' => $user->getMention(),
        //         'statut' => [CalendrierExamen::STATUS_CREATED, WorkflowStatutService::STATUS_ASSIST_VALIDATED],
        //         //'statut' => WorkflowStatutService::STATUS_ASSIST_VALIDATED,                
        //         'anneeUniversitaire' => $currentAnneUniv
        //     ]
        // );
        // dump($surveillants);die;



        
        // $surveillants = $calExamenManager->getCurrentVacation(
        //     $currentAnneUniv->getId(),
        //     $user->getMention()->getId(),
        //     $selectedCalPaiement
        // );

        $surveillants = $calExamenManager->getAll(
            $currentAnneUniv->getId(), $user->getMention()->getId(), $selectedCalPaiement, null, null, null, null
        );


        return $this->render(
            'frontend/assistant/surveillance/assign/index.html.twig',
            [
                'items'         => $surveillants,
                'c'            => $selectedCalPaiement ? $selectedCalPaiement->getId() : 0,
                'calPaiements' => $calPaiements
            ]
        );
    }

    /**
     * @Route("/surveillances/assignement/{id}/edit", name="front_assistant_surveillance_assing_edit", methods={"GET", "POST"})
     * @IsGranted("ROLE_ASSISTANT")
     * @param \Symfony\Component\HttpFoundation\Request          $request
     * @param \App\Manager\CalendrierExamenManager           $manager
     * @param \App\Services\AnneeUniversitaireService            $anneeUniversitaireService
     * @param \App\Manager\CalendrierExamenHistoriqueManager $historiqueManager
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function surveillanceAssingEdit(
        Request                             $request,
        CalendrierExamen                    $examenCalendar,
        CalendrierExamenManager             $manager,
        CalendrierExamenSurveillanceManager $calExSurvmanager,
        AnneeUniversitaireService           $anneeUniversitaireService,
        CalendrierExamenHistoriqueManager   $historiqueManager,
        WorkflowStatutService               $workflowStatService
    ) {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        /** @var \App\Entity\CalendrierExamen $examenCalendar */
        $form   = $this->createForm(
            ExamenCalendarType::class,
            $examenCalendar,
            [
                'user' => $user,
                'em'=>$em,
                'calendar' => null
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // dump($form->getData());die;
            // dump($examenCalendar->getCalendrierExamenSurveillances());die;
            // dump($form->get('surveillant')->getData());die;
            $surveillants = $form->get('surveillant')->getData();
            $resNextStatus = $workflowStatService->getResourceNextStatut(WorkflowStatutService::STATUS_CREATED);
            $examenCalendar->setStatut($resNextStatus);

            //remove older calExmSurv
            $oldClExSurv = $calExSurvmanager->loadBy([
                    'calendrier_examen' => $examenCalendar],[]);
            // dump($oldClExSurv);die;
            foreach($oldClExSurv as $surv){
                $examenCalendar->removeCalendrierExamenSurveillance($surv);
            }
            foreach($surveillants as $surv){
            
                $currCalExamSurv = new CalendrierExamenSurveillance();
                $currCalExamSurv->setSurveillant($surv);
                $em->persist($currCalExamSurv);
                $examenCalendar->addCalendrierExamenSurveillance($currCalExamSurv);
            }
            $em->persist($examenCalendar);
            $em->flush();

            /** @var \App\Entity\CalendrierExamenHistorique $historical */
            $historical = $historiqueManager->createObject();
            $historical->setUser($this->getUser());
            $historical->setStatus($resNextStatus);
            $historical->setCalendrierExamen($examenCalendar);
            $historiqueManager->save($historical);

            return $this->redirectToRoute('front_assistant_surveillance_assing_index');
        }

        return $this->render(
            'frontend/assistant/surveillance/assign/new.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/surveillances", name="front_assistant_surveillance_index", methods={"GET"})
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
        AnneeUniversitaireService   $anneeUniversitaireService
    ) {
        $c = $request->get('c', '');
        $selectedCalPaiement = $c? $calPaiementManager->load($c) : $calPaiementRepo->findDefault();
        $user               = $this->getUser();
        $calPaiements = $calPaiementManager->loadAll();
        $currentAnneUniv    = $anneeUniversitaireService->getCurrent();
        $surveillants = $calExamenManager->getCurrentVacation(
            $currentAnneUniv->getId(),
            $user->getMention()->getId(),
            $selectedCalPaiement
        );

        return $this->render(
            'frontend/assistant/surveillance/index.html.twig',
            [
                'c'            => $selectedCalPaiement ? $selectedCalPaiement->getId() : 0,
                'calPaiements' => $calPaiements,
                'list'         => $surveillants
            ]
        );
    }

    /**
     * @Route("/surveillance/{id}/details", name="front_assistant_surveillance_details", methods={"GET", "POST"})
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
        $c = $request->get('c', '');
        $selectedCalPaiement = $c? $calPaiementManager->load($c) : "";
        $user               = $this->getUser();
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
            'frontend/assistant/surveillance/details-index.html.twig',
            [
                'c'                     => $c,
                'surveillant'           => $surveillant,
                'calPaiement'           => $selectedCalPaiement,
                'list'                  => $surveillantVacations,
                'profilListStatut'      => $profilListStatut,
                'profilNextStatut'      => $profilNextStatut
            ]
        );
    }  

    /**
     * IsGranted("ROLE_ASSISTANT")
     * @Route("/validate/surveillance/", name="front_assistant_surveillance_validate", methods={"POST"})
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