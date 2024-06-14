<?php

namespace App\Controller\Frontend;

use App\Entity\ConcoursCandidature;
use App\Entity\Mention;
use App\Entity\Concours;

use App\Form\ConcoursCandidatureFormType;
use App\Form\ConcoursNotesFormType;
use App\Manager\AnneeUniversitaireManager;
use App\Manager\CandidatureHistoriqueManager;
use App\Manager\ConcoursCandidatureManager;
use App\Manager\ConcoursConfigManager;
use App\Manager\ConcoursManager;
use App\Manager\ConcoursMatiereManager;
use App\Manager\ConcoursNotesManager;
use App\Manager\MentionManager;
use App\Manager\NiveauManager;
use App\Manager\ParcoursManager;

use App\Repository\MentionRepository;
use App\Security\EmailVerifier;
use App\Services\AnneeUniversitaireService;
use App\Services\ConcoursService;
use App\Services\ExportConcoursService;
use App\Services\Mailer;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;


use \Gumlet\ImageResize;

/**
 * Description of ConcoursController.php.
 *
 * @package App\Controller\Frontend
 */
class ConcoursController extends AbstractController
{


    //modif sedra


    /**
 * @Route("/details_votre_candidature", name="info_concours_pour_candidature", methods={"GET"})
   * @param \Symfony\Component\HttpFoundation\Request                                 $request
     * @param \App\Repository\MentionRepository                                         $mentionRepo
     * @param \App\Manager\ConcoursCandidatureManager                                   $manager
     * @param \App\Manager\CandidatureHistoriqueManager                                 $historiqueManager
     * @param \App\Manager\NiveauManager                                                $niveauManager
     * @param \App\Security\EmailVerifier                                               $emailVerifier
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameter
     * @param \App\Services\Mailer                                                      $mailer
     * @param \App\Services\AnneeUniversitaireService                                   $anneeUniversitaireService
     * @param \Psr\Log\LoggerInterface                                                  $logger
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
 */
    public function index(): Response
    {
    // Utilize the injected repository to build your query
       return $this->render('frontend/concours/voir_info_candidature.html.twig', []);
    }

    /**
     * @Route("/recherchecandidat_par_num_inscription", name="search_candidates")
     * @param \Symfony\Component\HttpFoundation\Request                                 $request
     * @param \App\Repository\MentionRepository                                         $mentionRepo
     * @param \App\Manager\ConcoursCandidatureManager                                   $manager
     * @param \App\Manager\CandidatureHistoriqueManager                                 $historiqueManager
     * @param \App\Manager\NiveauManager                                                $niveauManager
     * @param \App\Security\EmailVerifier                                               $emailVerifier
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameter
     * @param \App\Services\Mailer                                                      $mailer
     * @param \App\Services\AnneeUniversitaireService                                   $anneeUniversitaireService
     * @param \Psr\Log\LoggerInterface                                                  $logger
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function searchCandidateByNumInscription(Request $request): Response
    {
        $numeroInscription = $request->request->get('numeroInscription');

        // Effectuez votre recherche dans la base de données en fonction du numéro d'inscription
        $repository = $this->getDoctrine()->getRepository(ConcoursCandidature::class);
        $result = $repository->findOneBy(['bacc_num_inscription' => $numeroInscription]);

        // Rendez une vue Twig pour afficher les résultats
        return $this->render('frontend/concours/search_candidate_by_nom_inscription_results.html.twig', ['result' => $result]);
    }


    /**
     * @Route("/concours/candidature", name="front_concours_candidature_inscription")
     * @param \Symfony\Component\HttpFoundation\Request                                 $request
     * @param \App\Repository\MentionRepository                                         $mentionRepo
     * @param \App\Manager\ConcoursCandidatureManager                                   $manager
     * @param \App\Manager\CandidatureHistoriqueManager                                 $historiqueManager
     * @param \App\Manager\NiveauManager                                                $niveauManager
     * @param \App\Security\EmailVerifier                                               $emailVerifier
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameter
     * @param \App\Services\Mailer                                                      $mailer
     * @param \App\Services\AnneeUniversitaireService                                   $anneeUniversitaireService
     * @param \Psr\Log\LoggerInterface                                                  $logger
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function register(
        Request $request,
        MentionManager $mentionManager,
        ConcoursCandidatureManager $manager,
        ConcoursConfigManager $concoursConfManager,
        CandidatureHistoriqueManager $historiqueManager,
        NiveauManager $niveauManager,
        EmailVerifier $emailVerifier,
        ParameterBagInterface $parameter,
        Mailer $mailer,
        ConcoursService $concoursService,
        LoggerInterface $logger
    ) : Response {
        /** @var \App\Entity\ConcoursCandidature $candidature */
        // $mentions   = $mentionRepo->findBy(array('active' => 1), array('nom' => 'ASC'));
        $mentions   = $mentionManager->findConcourable();
        $niveaux    = $niveauManager->getConcourable();        
        $candidature = $manager->createObject();
        $form        = $this->createForm(ConcoursCandidatureFormType::class, $candidature);
        $form->handleRequest($request);
        $concoursConf = $concoursConfManager->loadOneBy([]);
        $concoursAnneeUniv = $concoursConf->getAnneeUniversitaire();
        // dump($form->getErrors(true));die;
        // dump($candidature->getAnneeUniversitaire());die;
        if ($form->isSubmitted() && $form->isValid()) {
            // check uniqueness
            if ($manager->isUnique($candidature->getEmail(), $candidature->getMention()->getId(), $concoursAnneeUniv->getId(), $candidature->getPayementRef())) {
                $photoFile     = $form->get('photo')->getData();
                $diplomeFile     = $form->get('diplomePath')->getData();
                $payementRefFile = $form->get('payementRefPath')->getData();
                $directory     = $parameter->get('concours_directory');
                if(!is_dir($directory)){
                     try {
                        $filesystem = new Filesystem();
                        $filesystem->mkdir($directory);
                     } catch (IOExceptionInterface $exception) {
                        echo "An error occurred while creating your directory at ".$exception->getPath();
                    }
                }
                $uploader      = new \App\Services\FileUploader($directory);
                $today         = new \DateTime();
                $fileDirectory = $concoursAnneeUniv->getAnnee() . '/' . $candidature->getFirstName() . '-' . $candidature->getFirstName() . "-" . $today->getTimestamp();
                // Upload file
                if ($diplomeFile) {
                    $diplomeFileDisplay = $uploader->upload($diplomeFile, $directory, $fileDirectory, false);
                    $candidature->setDiplomePath($fileDirectory . "/" . $diplomeFileDisplay["filename"]);
                }
                if ($photoFile) {
                    // $imgTempPath        = $photoFile->getPath();
                    // $imgTempFileName    = $photoFile->getFileName();
                    // $tmpPhotoFile       = $imgTempPath . '/' . $imgTempFileName;
                    // $imageTempResized   = $tmpPhotoFile . '-resized';                    
                    //resize
                    // dump($photoFile);                    
                    // $image = new ImageResize($tmpPhotoFile);
                    // $image->resizeToHeight(500);
                    // $image->save('image2.jpg');
                    // $image = new ImageResize($tmpPhotoFile);
                    // $image->resizeToWidth(300);
                    // $image->save($imageTempResized);                  
                    // $newTempFile = new UploadedFile($imageTempResized);
                    // dump($newTempUploadedFile);die;
                    $photoFileDisplay = $uploader->upload($photoFile, $directory, $fileDirectory, false);
                    // dump($photoFileDisplay);die;
                    $candidature->setPhoto($fileDirectory . "/" . $photoFileDisplay["filename"]);
                }
                if ($payementRefFile) {
                    $payementFileDisplay = $uploader->upload($payementRefFile, $directory, $fileDirectory, false);
                    $candidature->setPayementRefPath($fileDirectory . "/" . $payementFileDisplay["filename"]);
                }
                $candidature->setAnneeUniversitaire($concoursAnneeUniv);
                $concoursService->setMatricule($candidature);
                $manager->save($candidature);
                /** @var \App\Entity\CandidatureHistorique $historical */
                $historical = $historiqueManager->createObject();
                $historical->setCandidature($candidature);
                $historical->setStatus(ConcoursCandidature::STATUS_CREATED);
                $manager->save($historical);
                return $this->redirectToRoute('front_concours_candidature_confirmation', ['id' => $candidature->getId()]);
            } else {
                return $this->redirectToRoute('front_concours_candidature_error');
            }
        }

        return $this->render(
            'frontend/concours/candidature.html.twig',
            [
                'mentions'          => $mentions,
                'niveaux'           => $niveaux,
                'registrationForm'  => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/concours/mention/{id}/parcours", name="front_concours_mention_parcours", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\MentionManager               $mentionManager
     * @param \App\Manager\ConcoursManager              $concoursManager
     * @param \App\Manager\ConcoursMatiereManager       $concoursMatiereManager
     *
     * @return string
     */
    public function ajaxGetParcoursOptions(
        Request $request,
        Mention $mention,
        ParcoursManager $parcoursManager
    ) {
        $mentionId = $request->get('mention_id');
        $niveauId = $request->get('niveau_id');
        $parcours = $parcoursManager->loadBy(
            [
                'mention' => $mentionId,
                'niveau' => $niveauId
            ],
            [
                'nom' => 'ASC'
            ]
        );

        return $this->render(
            'frontend/concours/_mention_parcours_options.html.twig',
            [
                'parcours' => $parcours
            ]
        );
    }

    /**
     * @Route("/concours/mention/{id}/niveau", name="front_concours_mention_niveau", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\MentionManager               $mentionManager
     * @param \App\Manager\ConcoursManager              $concoursManager
     * @param \App\Manager\ConcoursMatiereManager       $concoursMatiereManager
     *
     * @return string
     */
    public function ajaxGetNiveauOptions(
        Request $request,
        Mention $mention,
        ParcoursManager $parcoursManager
    ) {
        $mentionId = $request->get('mention_id');
        $parcours = $parcoursManager->loadBy(
            [
                'mention' => $mentionId,
                'niveau' => $niveauId
            ],
            [
                'nom' => 'ASC'
            ]
        );

        return $this->render(
            'frontend/concours/_mention_niveau_options.html.twig',
            [
                'niveau' => $niveau
            ]
        );
    }

    /**
     * @Route("/concours/candidature/confirmation/{id}", name="front_concours_candidature_confirmation", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\ConcoursCandidature           $candidature
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function confirmation(Request $request, ConcoursCandidature $candidature)
    {
        return $this->render(
            'frontend/concours/confirmation-candidature.html.twig',
            [
                'candidature' => $candidature,
            ]
        );
    }

    /**
     * @Route("/concours/candidature/doublon", name="front_concours_candidature_error", methods={"GET"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function doublon()
    {
        return $this->render(
            'frontend/concours/doublon-candidature.html.twig'
        );
    }

    /**
     * @Route("/concours/candidats", name="front_concours_candidates", methods={"GET"})
     * @IsGranted({"ROLE_SCOLARITE", "ROLE_SSRS"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\MentionManager               $mentionManager
     * @param \App\Manager\ConcoursManager              $concoursManager
     * @param \App\Manager\ParcoursManager              $parcoursManager
     * @param \App\Manager\ConcoursCandidatureManager   $manager
     * @param \App\Service\ConcoursConfigManager        $concoursConfManager
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listCandidates(
        Request                         $request,
        MentionManager                  $mentionManager,
        ConcoursManager                 $concoursManager,
        ParcoursManager                 $parcoursManager,
        ConcoursCandidatureManager      $manager,
        ConcoursConfigManager           $concoursConfManager
    ) {
        $selectedM = $request->get('m', 0);
        $selectedP = $request->get('p');
        $selectedC = $request->get('c', 0);

        $concoursConf = $concoursConfManager->loadOneBy([]);
        $currentAnneUniv= $concoursConf->getAnneeUniversitaire();
        $mentions = $mentionManager->loadBy(['active' => 1], ['nom' => 'ASC']);
        $selectedMentions = $mentionManager->load($selectedM);
        $parcours = $parcoursManager->loadBy(
            [
                'mention'  => $selectedMentions,
            ],
            [
                'nom' => 'ASC'
            ]
        );
        $concours = (int) $selectedM > 0 ? $concoursManager->loadBy(
            [
                'mention' => $selectedM,
                'parcours' => $selectedP ? $parcours : null
            ],
            [
                'libelle' => 'ASC'
            ]
        ) : [];

        $candidates = [];
        $tFilters = [];
        $tFilters['anneeUniversitaire'] = $currentAnneUniv;
        $tFilters['status'] = ConcoursCandidature::STATUS_SU_VALIDATED;
        $selectedConcours = '';
        /** @var \App\Entity\Concours $selectedConcours */
        if ($selectedC
            && ($selectedConcours = $concoursManager->load($selectedC))
            && $selectedMentions
        ) {            
            $tFilters['mention'] = $selectedMentions->getId();
            $tFilters['niveau'] = $selectedConcours->getNiveau()->getId();
            if($selectedP)
                $tFilters['parcours'] = $selectedP;
            $candidates = $manager->findByCriteria($tFilters);
        }

        return $this->render(
            'frontend/concours/candidates.html.twig',
            [
                'm'          => $selectedM,
                'c'          => $selectedC,
                'p'          => $selectedP,
                'mentions'   => $mentions,
                'concours'   => $concours,
                'parcours'   => $parcours,
                'candidates' => $candidates,
                'selectedConcours' => $selectedConcours
            ]
        );
    }

    /**
     * @Route("/concours/ajax-candidats", name="front_concours_ajax_candidates", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\MentionManager               $mentionManager
     * @param \App\Manager\ConcoursManager              $concoursManager
     * @param \App\Manager\ParcoursManager              $parcoursManager
     * @param \App\Manager\ConcoursCandidatureManager   $manager
     * @param \App\Service\ConcoursConfigManager        $concoursConfManager
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajaxListCandidates(
        Request                         $request,
        MentionManager                  $mentionManager,
        ConcoursManager                 $concoursManager,
        ParcoursManager                 $parcoursManager,
        ConcoursCandidatureManager      $manager,
        ConcoursConfigManager          $concoursConfManager
    ) {
        $selectedM  = $request->get('m');
        $selectedP  = $request->get('p');
        $selectedC  = $request->get('c');
        $qString    = $request->get('q');
        $selectedConcours = "";
        $concoursConf = $concoursConfManager->loadOneBy([]);
        $currentAnneUniv= $concoursConf->getAnneeUniversitaire();
        $selectedConcours = $concoursManager->load($selectedC);
        $candidates = [];
        /** @var \App\Entity\Concours $selectedConcours */
        // if ($selectedC
        //     && ($selectedConcours = $concoursManager->load($selectedC))
        //     && ($selectedMentions = $mentionManager->load($selectedM))
        // ) {
            // $criterias        = [
            //     'mention' => $selectedMentions->getId(),
            //     'niveau'  => $selectedConcours->getNiveau()->getId(),
            //     'status'  => ConcoursCandidature::STATUS_SU_VALIDATED,
            //     'anneeUniversitaire' => $currentAnneUniv
            // ];
            // $selectedParcours = $parcoursManager->load($selectedP);
            // if ($selectedParcours) {
            //     $criterias['parcours'] = $selectedParcours;
            // }
            // $candidates = $manager->findByCriteria($criterias);
        // }
        $candidates = $manager->findValidatedList(
                $selectedM, 
                $selectedConcours->getNiveau()->getId(),
                $selectedP, 
                $currentAnneUniv->getId(), 
                $qString);

        // dump($selectedConcours->getNiveau()->getId());die;

        return $this->render(
            'frontend/concours/ajax-candidates.html.twig',
            [
                'm' => $selectedM,
                'p' => $selectedP,
                'c' => $selectedC,
                'candidates' => $candidates,
                'selectedConcours' => $selectedConcours
            ]
        );
    }

    /**
     * @Route("/scolarite/concours/ajax-options", name="front_scolarite_concours_ajax_option", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\ConcoursManager              $concoursManager
     * @param \App\Manager\ConcoursMatiereManager       $concoursMatiereManager
     *
     * @return string
     */
    public function ajaxGetConcoursOptions(
        Request $request,
        ConcoursManager $concoursManager,
        ConcoursMatiereManager $concoursMatiereManager
    ) {

        $concours = $concoursManager->loadBy(['mention' => $request->query->getInt('mentionId')], ['libelle' => 'ASC']);

        return $this->render(
            'frontend/concours/_concours_options.html.twig',
            [
                'concours' => $concours
            ]
        );
    }

    /**
     * @Route("/concours/notes/{id}", name="front_concours_candidate_notes", methods={"GET", "POST"})
     * @IsGranted({"ROLE_SCOLARITE", "ROLE_SSRS"})
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
    public function notes(
        Request $request,
        ConcoursCandidature $candidate,
        ConcoursCandidatureManager $concoursCandidatureManager,
        ConcoursMatiereManager $concoursMatiereManager,
        ConcoursNotesManager $concoursNotesManager,
        ConcoursManager $concoursManager,
        MentionManager $mentionManager
    ) {
        $selectedM = $request->get('m');
        $selectedC = $request->get('c');
        $selectedConcours = $concoursManager->load($selectedC);
        $selectedP = $request->get('p');

        $concoursNotes = $concoursNotesManager->createObject();
        $form          = $this->createForm(ConcoursNotesFormType::class, $concoursNotes);
        $form->handleRequest($request);

        if ($form->isSubmitted() && intval($selectedConcours->getResultStatut()) === Concours::STATUS_CREATED) {
            $formData = $request->request->all();    
            $concoursNotesManager->manageNotes(
                $formData,
                $candidate,
                $concoursCandidatureManager,
                $concoursMatiereManager,
                $concoursManager
            );
            return $this->redirectToRoute('front_concours_candidates', ['c' => $formData['c'], 'm' => $formData['m'], 'p' => $formData['p']]);
        }
        $matieres = $concoursMatiereManager->findByCriteria(
            [
                'mention'  => $selectedM,
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
            'frontend/concours/notes.html.twig',
            [
                'm'             => $selectedM,
                'c'             => $selectedC,
                'p'             => $selectedP,
                'candidate'     => $candidate,
                'matieresNotes' => $matieresNotes,
                'form'          => $form->createView(),
                'mention'       => $mentionManager->load($selectedM),
                'concours'      => $selectedConcours
            ]
        );
    }

    /**
     * @Route("/concours/resultat", name="front_concours_resultat", methods={"GET"})
     * @IsGranted({"ROLE_SCOLARITE", "ROLE_SSRS"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\MentionManager               $mentionManager
     * @param \App\Manager\ConcoursManager              $concoursManager
     * @param \App\Manager\ParcoursManager              $parcoursManager
     * @param \App\Manager\ConcoursNotesManager         $notesManager
     * @param \App\Service\ConcoursConfigManager        $concoursConfManager
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function resultat(
        Request                     $request,
        MentionManager              $mentionManager,
        ConcoursManager             $concoursManager,
        ParcoursManager             $parcoursManager,
        ConcoursNotesManager        $notesManager,
        ConcoursConfigManager       $concoursConfManager
    ) {
        $selectedM = $request->get('m', 0);
        $selectedC = $request->get('c');
        $selectedP = $request->get('p');

        $concoursConf = $concoursConfManager->loadOneBy([]);
        $currentAnneUniv= $concoursConf->getAnneeUniversitaire();
        $mentions = $mentionManager->loadBy(['active' => 1], ['nom' => 'ASC']);
        $concours = (int) $selectedM > 0 ? $concoursManager->loadBy(['mention' => $selectedM], ['libelle' => 'ASC']) : [];
        $selectedMentions = $mentionManager->load($selectedM);

        $parcours = $parcoursManager->loadBy(
            [
                'mention'  => $selectedMentions,
            ],
            [
                'nom' => 'ASC'
            ]
        );

        $resultats        = [];
        $selectedConcours = '';
        $selectedMentions = '';
        /** @var \App\Entity\Concours $selectedConcours */
        if ($selectedC
            && ($selectedConcours = $concoursManager->load($selectedC))
            && ($selectedMentions = $mentionManager->load($selectedM))
        ) {
            $resultats = $notesManager->getResultByConcours($selectedConcours, $selectedMentions, $currentAnneUniv->getId(), $selectedP);
        }

        return $this->render(
            'frontend/concours/resultat.html.twig',
            [
                'm'                => $selectedM,
                'c'                => $selectedC,
                'p'                => $selectedP,
                'mentions'         => $mentions,
                'concours'         => $concours,
                'parcours'         => $parcours,
                'selectedConcours' => $selectedConcours,
                'selectedMentions' => $selectedMentions,
                'resultats'        => $resultats,
            ]
        );
    }

    /**
     * @Route("/concours/ajax-result", name="front_concours_ajax_resultat", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\MentionManager               $mentionManager
     * @param \App\Manager\ConcoursManager              $concoursManager
     * @param \App\Manager\ParcoursManager              $parcoursManager
     * @param \App\Manager\ConcoursNotesManager         $notesManager
     * @param \App\Service\ConcoursConfigManager        $concoursConfManager
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function ajaxResultat(
        Request $request,
        MentionManager $mentionManager,
        ConcoursManager $concoursManager,
        ParcoursManager $parcoursManager,
        ConcoursNotesManager $notesManager,
        ConcoursConfigManager       $concoursConfManager
    ) {
        $selectedM = $request->get('m');
        $selectedP = $request->get('p');
        $selectedC = $request->get('c');
        $concoursConf = $concoursConfManager->loadOneBy([]);
        $currentAnneUniv= $concoursConf->getAnneeUniversitaire();
        $resultats = [];

        $selectedConcours = '';
        $selectedMentions = '';

        // if ($selectedC
        //     && ($selectedConcours = $concoursManager->load($selectedC))
        //     && ($selectedMentions = $mentionManager->load($selectedM))
        // ) {
        //     $resultats = $notesManager->getResultByConcours($selectedConcours, $selectedMentions, $currentAnneUniv->getId(), $selectedP);
        // }


        /** @var \App\Entity\Concours $selectedConcours */
        if ( ($selectedMentions = $mentionManager->load($selectedM)) && $selectedC && ($selectedConcours = $concoursManager->load($selectedC))) {
            $resultats = $notesManager->getLevelNotesByMixedParams(
                $selectedConcours,
                $selectedMentions,
                $currentAnneUniv->getId(),
                $selectedP,
                Concours::STATUS_VALIDATED_RECTEUR,
                NULL,
                NULL,
                ConcoursCandidature::RESULT_ADMITTED
            );
        }



        return $this->render(
            'frontend/concours/ajax-resultat.html.twig',
            [
                'm'                => $selectedM,
                'c'                => $selectedC,
                'p'                => $selectedP,
                'selectedConcours' => $selectedConcours,
                'selectedMentions' => $selectedMentions,
                'resultats'        => $resultats,
            ]
        );

        dd($request->query->all());
    }


    // resultat concours accessible dans site


    /**
     * @Route("/concours/result", name="front_concours_resultat_site", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\MentionManager               $mentionManager
     * @param \App\Manager\ConcoursManager              $concoursManager
     * @param \App\Manager\ParcoursManager              $parcoursManager
     * @param \App\Manager\ConcoursNotesManager         $notesManager
     * @param \App\Service\ConcoursConfigManager        $concoursConfManager
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function resultatSite(
        Request                     $request,
        MentionManager              $mentionManager,
        NiveauManager              $niveauManager,
        ConcoursManager             $concoursManager,
        ParcoursManager             $parcoursManager,
        ConcoursNotesManager        $notesManager,
        ConcoursConfigManager       $concoursConfManager
    ) {
        $selectedM = $request->get('m', 0);
        $selectedN = $request->get('n');
        $selectedP = $request->get('p');

        $concoursConf = $concoursConfManager->loadOneBy([]);
        $currentAnneUniv= $concoursConf->getAnneeUniversitaire();
        $mentions = $mentionManager->loadBy(['active' => 1], ['nom' => 'ASC']);
        $niveau = $niveauManager->loadBy([], ['libelle' => 'ASC']);
        $selectedMentions = $mentionManager->load($selectedM);


        $parcours = $parcoursManager->loadBy(
            [
                'mention'  => $selectedMentions,
                'niveau' =>$selectedN
            ],
            [
                'nom' => 'ASC'
            ]
        );

        $resultats        = [];
        $selectedConcours = '';
        $selectedMentions = '';
        
        /** @var \App\Entity\Concours $selectedConcours */
        if ($selectedM && $selectedN
        ) {
            $selectedConcours= $concoursManager->loadOneBy(
                ['mention'=> $selectedM,'niveau'=> $selectedN,'parcours'=> $selectedP]
            );

            $resultats = $notesManager->getResultByConcours($selectedConcours, $selectedMentions, $currentAnneUniv->getId(), $selectedP);
        }

        return $this->render(
            'frontend/concours/resultatSite.html.twig',
            [
                'm'                => $selectedM,
                'n'                => $selectedN,
                'p'                => $selectedP,
                'mentions'         => $mentions,
                'niveau'         => $niveau,
                'parcours'         => $parcours,
                'selectedMentions' => $selectedMentions,
                'resultats'        => $resultats,
            ]
        );
    }

     /**
     * @Route("/concours/ajax-resultatConcours", name="front_result_ajax_concours", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\MentionManager               $mentionManager
     * @param \App\Manager\ConcoursManager              $concoursManager
     * @param \App\Manager\ParcoursManager              $parcoursManager
     * @param \App\Manager\ConcoursNotesManager         $notesManager
     * @param \App\Service\ConcoursConfigManager        $concoursConfManager
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function ajaxResultatConcoursL1(
        Request $request,
        MentionManager $mentionManager,
        ConcoursManager $concoursManager,
        ParcoursManager $parcoursManager,
        ConcoursNotesManager $notesManager,
        ConcoursConfigManager       $concoursConfManager
    ) {
        $selectedM = $request->get('m');
        $selectedP = $request->get('p');
        $selectedN = $request->get('n');
        $concoursConf = $concoursConfManager->loadOneBy([]);
        $currentAnneUniv= $concoursConf->getAnneeUniversitaire();
        $resultats = [];

        $selectedConcours = '';
        $selectedMentions = '';

        // if ($selectedC
        //     && ($selectedConcours = $concoursManager->load($selectedC))
        //     && ($selectedMentions = $mentionManager->load($selectedM))
        // ) {
        //     $resultats = $notesManager->getResultByConcours($selectedConcours, $selectedMentions, $currentAnneUniv->getId(), $selectedP);
        // }

        $selectedConcours = $concoursManager->loadOneBy([
            'mention'=> $selectedM , 
            'niveau'=> $selectedN ,
            'parcours'=> $selectedP ,
            'annee_universitaire' => $currentAnneUniv
        ]);

        /** @var \App\Entity\Concours $selectedConcours */
        if ( ($selectedMentions = $mentionManager->load($selectedM)) && ($selectedConcours)) {
            $resultats = $notesManager->getLevelNotesByMixedParams(
                $selectedConcours,
                $mentionManager->load($selectedM),
                $currentAnneUniv->getId(),
                $selectedP,
                Concours::STATUS_VALIDATED_RECTEUR,
                NULL,
                NULL,
                ConcoursCandidature::RESULT_ADMITTED
            );
        }



        return $this->render(
            'frontend/concours/ajax-resultat-concours.html.twig',
            [
                'm'                => $selectedM,
                'p'                => $selectedP,
                'selectedConcours' => $selectedConcours,
                'selectedMentions' => $selectedMentions,
                'resultats'        => $resultats,
            ]
        );

        dd($request->query->all());
    }


    /**
     * @Route("/concours/ajax-get-niveau", name="front_result_ajax_get_niveau", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\MentionManager               $mentionManager
     * @param \App\Manager\ConcoursManager              $concoursManager
     * @param \App\Manager\ParcoursManager              $parcoursManager
     * @param \App\Manager\ConcoursNotesManager         $notesManager
     * @param \App\Service\ConcoursConfigManager        $concoursConfManager
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function ajaxResultatGetNiveau(
        Request $request,
        MentionManager $mentionManager,
        ConcoursManager $concoursManager,
        ParcoursManager $parcoursManager,
        ConcoursNotesManager $notesManager,
        ConcoursConfigManager       $concoursConfManager
    ) {
        $selectedM = $request->get('m');        
        $concoursConf = $concoursConfManager->loadOneBy([]);
        $currentAnneUniv= $concoursConf->getAnneeUniversitaire();
        $niveau = $concoursManager->loadBy(['mention' => $selectedM, 'annee_universitaire' => $currentAnneUniv]);

        return $this->render(
            'frontend/concours/_ajax-resultat-niveau.html.twig',
            [
                'niveau' => $niveau
            ]
        );
        /* dd($request->query->all()); */
    }


/**
     * @Route("/concours/ajax-get-parcours", name="front_result_ajax_get_parcours", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\MentionManager               $mentionManager
     * @param \App\Manager\ConcoursManager              $concoursManager
     * @param \App\Manager\ParcoursManager              $parcoursManager
     * @param \App\Manager\ConcoursNotesManager         $notesManager
     * @param \App\Service\ConcoursConfigManager        $concoursConfManager
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function ajaxResultatGetParcours(
        Request $request,
        MentionManager $mentionManager,
        NiveauManager $niveauManager,
        ConcoursManager $concoursManager,
        ParcoursManager $parcoursManager,
        ConcoursNotesManager $notesManager,
        ConcoursConfigManager  $concoursConfManager
    ) {
        $selectedM = $request->get('m');   
        $selectedN = $request->get('n');

        $concoursConf = $concoursConfManager->loadOneBy([]);
        $currentAnneUniv= $concoursConf->getAnneeUniversitaire();
        $parcours = $concoursManager->loadBy(['mention' => $selectedM,'niveau'=> $selectedN, 'annee_universitaire' => $currentAnneUniv]);

        return $this->render(
            'frontend/concours/_ajax-resultat-parcours.html.twig',
            [
                'parcours' => $parcours
            ]
        );
        /* dd($request->query->all()); */
    }






    /**
     * @IsGranted({"ROLE_SCOLARITE", "ROLE_SSRS"})
     * @Route("/concours/export/candidature", name="front_concours_candidature_export", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     * @param \App\Services\ExportConcoursService       $ExportConcoursService
     * @param \App\Manager\concoursCandidatureManager   $concoursCandidatureManager
     * @param \App\Manager\MentionManager               $mentionManager
     * @param \App\Manager\NiveauManager                $niveauManager
     * @param \App\Manager\ParcoursManager              $parcoursManager
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function exportCandidature(
        Request                         $request, 
        ConcoursCandidatureManager      $concoursCandidatureManager,
        MentionManager                  $mentionManager,
        NiveauManager                   $niveauManager,
        ParcoursManager                 $parcoursManager,
        ConcoursConfigManager           $concoursConfManager,
        ExportConcoursService           $exportConcoursService,
        ParameterBagInterface           $parameter)
    {
        $selectedM = $request->get('m', 0);
        $selectedN = $request->get('n', 0);
        $selectedP = $request->get('p');
        $selectedS = $request->get('s', 0);
        $concoursConf = $concoursConfManager->loadOneBy([]);
        $concoursAnneeUniv = $concoursConf->getAnneeUniversitaire();
        $options = [
            'anneeUniversitaire'    => $concoursAnneeUniv,
            'status'                => $selectedS
        ];
        if($selectedM)
            $options['mention'] = $mentionManager->load($selectedM);
        if($selectedN)
            $options['niveau'] = $mentionManager->load($selectedN);
        if($selectedP)
            $options['parcours'] = $parcoursManager->load($selectedP);
        $candidatesAdmis = $concoursCandidatureManager->loadBy($options, []);   
        $file = $exportConcoursService->getCandidature($candidatesAdmis, $options, $parameter);
        
        return $this->file($file);       
    }

    /**
     * @IsGranted("ROLE_SSRS")
     * @Route("/concours/deliberation", name="front_concours_deliberation_index", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\ConcoursManager              $concoursManager
     * @param \App\Manager\AnneeUniversitaireManager    $anneeUniversitaireManager
     * @param \App\Manager\ConcoursConfigManager $concoursConfManager,
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function deliberationIndex(
        Request                         $request, 
        ConcoursManager                 $concoursManager,
        AnneeUniversitaireManager       $anneeUniversitaireManager,
        ConcoursConfigManager $concoursConfManager)
    {
        $concoursConf = $concoursConfManager->loadOneBy([]);
        $concoursAnneeUniv = $concoursConf->getAnneeUniversitaire();
        //dump($concoursAnneeUniv);die;
        //$anneeUnivList = $anneeUniversitaireManager->loadBy([], ['annee' => 'ASC']);
        $concoursList = $concoursManager->loadBy(['annee_universitaire' => $concoursAnneeUniv], []);  
        //dump($concoursList);die;         
        $em = $this->getDoctrine()->getManager();
        if($request->isMethod('POST')) {
            $formData = $request->get('deliberation', []);
            foreach($formData as $concoursId => $deliberation) {
                $current = $concoursManager->load($concoursId);
                $current->setDeliberation($deliberation);
                $em->persist($current);
            }
            $em->flush();
        }

        return $this->render(
            'frontend/concours/deliberation.html.twig',
            [
                'anneeUnivList'        => [$concoursAnneeUniv],
                'concoursList'         => $concoursList
            ]
        );       
    }

    /**
     * @IsGranted({"ROLE_SCOLARITE", "ROLE_SSRS"})
     * @Route("/concours/export/result", name="front_concours_result_export", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     * @param \App\Services\ExportConcoursService       $ExportConcoursService
     * @param \App\Manager\concoursCandidatureManager   $concoursCandidatureManager
     * @param \App\Manager\MentionManager               $mentionManager
     * @param \App\Manager\NiveauManager                $niveauManager
     * @param \App\Manager\ParcoursManager              $parcoursManager
     * @param \App\Manager\ConcoursConfigManager        $concoursConfManager
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function exportResult(
        Request $request,
        MentionManager $mentionManager,
        ConcoursManager $concoursManager,
        ParcoursManager $parcoursManager,
        ConcoursNotesManager $notesManager,
        ExportConcoursService $exportConcoursService,
        ConcoursConfigManager $concoursConfManager,
        ParameterBagInterface           $parameter
    ) {
        $selectedM = $request->get('m');
        $selectedP = $request->get('p');
        $selectedC = $request->get('c');
        $concoursConf = $concoursConfManager->loadOneBy([]);
        $currentAnneUniv = $concoursConf->getAnneeUniversitaire();
        $resultats = [];

        $selectedConcours = '';
        $selectedMention = '';

        // if ($selectedC
        //     && ($selectedConcours = $concoursManager->load($selectedC))
        //     && ($selectedMention = $mentionManager->load($selectedM))
        // ) {
        //     $resultats = $notesManager->getResultByConcours($selectedConcours, $selectedMention, $currentAnneUniv->getId(), $selectedP);
        // }
        /** @var \App\Entity\Concours $selectedConcours */
        if ( ($selectedMention = $mentionManager->load($selectedM)) && $selectedC && ($selectedConcours = $concoursManager->load($selectedC))) {
            $resultats = $notesManager->getLevelNotesByMixedParams(
                $selectedConcours,
                $selectedMention,
                $currentAnneUniv->getId(),
                $selectedP,
                Concours::STATUS_VALIDATED_RECTEUR,
                NULL,
                NULL,
                ConcoursCandidature::RESULT_ADMITTED
            );
        }

        
        $options = [
            'anneeUniversitaire'    => $currentAnneUniv, 
            'mention'               => $selectedMention,
            'concours'              => $selectedConcours,
            'parcours'              => ''
        ];
        if($selectedP)
            $options['parcours'] = $parcoursManager->load($selectedP);

        $file = $exportConcoursService->getResultat($resultats, $options, $parameter);
        
        return $this->file($file);       
    }

}