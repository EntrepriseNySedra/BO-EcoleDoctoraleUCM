<?php

namespace App\Controller\Frontend;

use App\Entity\CalendrierExamen;
use App\Entity\CalendrierSoutenance;
use App\Entity\CalendrierSoutenanceHistorique;
use App\Entity\Concours;
use App\Entity\ConcoursCandidature;

use App\Form\ConcoursNotesFormType;
use App\Entity\DemandeDoc;
use App\Entity\EmploiDuTemps;
use App\Entity\Enseignant;
use App\Entity\EnseignantMatiere;
use App\Entity\Etudiant;
use App\Entity\ExtraNote;
use App\Entity\FichePresenceEnseignant;
use App\Entity\Matiere;
use App\Entity\Mention;
use App\Entity\PaiementHistory;
use App\Entity\Roles;
use App\Entity\Salles;
use App\Entity\User;

use App\Form\DemandeType;
use App\Form\EmploiDuTempsType;
use App\Form\EnseignantMatiereType;
use App\Form\EnseignantParcoursType;
use App\Form\EnseignantType;
use App\Form\EtudiantInfoFormType;
use App\Form\ExamenCalendarType;
use App\Form\ExtraNoteType;
use App\Form\FichePresenceEnseignantType;
use App\Form\MatiereType;
use App\Form\NoteType;
use App\Form\SallesType;
use App\Form\ThesisCalendarType;

use App\Manager\AnneeUniversitaireManager;
use App\Manager\CalendrierExamenHistoriqueManager;
use App\Manager\CalendrierExamenManager;
use App\Manager\CalendrierPaiementManager;
use App\Manager\CalendrierSoutenanceHistoriqueManager;
use App\Manager\CalendrierSoutenanceManager;
use App\Manager\ConcoursCandidatureManager;
use App\Manager\ConcoursConfigManager;
use App\Manager\ConcoursManager;
use App\Manager\ConcoursMatiereManager;
use App\Manager\ConcoursNotesManager;
use App\Manager\DemandeDocHistoriqueManager;
use App\Manager\DemandeDocManager;
use App\Manager\DepartementManager;
use App\Manager\EmploiDuTempsManager;
use App\Manager\EnseignantManager;
use App\Manager\EnseignantMentionManager;
use App\Manager\EnseignantMatiereManager;
use App\Manager\EtudiantManager;
use App\Manager\ExtraNoteHistoriqueManager;
use App\Manager\ExtraNotesManager;
use App\Manager\FichePresenceEnseignantHistoriqueManager;
use App\Manager\FichePresenceEnseignantManager;
use App\Manager\MatiereManager;
use App\Manager\MentionManager;
use App\Manager\NiveauManager;
use App\Manager\ParcoursManager;
use App\Manager\PrestationManager;
use App\Manager\ProfilManager;
use App\Manager\NotesManager;
use App\Manager\SallesManager;
use App\Manager\SemestreManager;
use App\Manager\UserManager;
use App\Manager\UniteEnseignementsManager;

use App\Repository\ConcoursCandidatureRepository;
use App\Repository\CalendrierPaiementRepository;
use App\Repository\EmploiDuTempsRepository;
use App\Repository\EnseignantRepository;
use App\Repository\EtudiantRepository;
use App\Repository\MatiereRepository;

use App\Services\AnneeUniversitaireService;
use App\Services\EtudiantService;
use App\Services\Mailer;
use App\Services\UtilFunctionService;
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
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;


use Dompdf\Dompdf;
use Dompdf\Options;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Description of RecteurController.php.
 *
 * @package App\Controller\Frontend
 * @Route("/recteur")
 */
class RecteurController extends AbstractController
{

    //modif sedra


    /**
 * @Route("/dashboard", name="dashboard", methods={"GET"})
 * @param \App\Repository\ConcoursCandidatureRepository $repository
 * @return \Symfony\Component\HttpFoundation\Response
 * @throws \Doctrine\ORM\ORMException
 * @throws \Doctrine\ORM\OptimisticLockException
 * @IsGranted("ROLE_RECTEUR")
 */
public function index(ConcoursCandidatureRepository $repository): Response
{
    // Utilize the injected repository to build your query
    $query = $repository->createQueryBuilder('cc')
        ->select('c.name AS centreNom', 'COUNT(cc.id) AS nombreCandidatures')
        ->join('cc.centre', 'c') // Use the correct association name
        ->groupBy('cc.centre') // Group by the association property, not centre_id
        ->getQuery();
    
    $queryvalidated = $repository->createQueryBuilder('cc')
        ->select('c.name AS centreNom', 'COUNT(cc.id) AS nombreCandidatures')
        ->join('cc.centre', 'c') // Use the correct association name
        ->where('cc.status = 3') // Add the WHERE condition
        ->groupBy('cc.centre') // Group by the association property, not centre_id
        ->getQuery();


    $queryByField = $repository->createQueryBuilder('cc')
    ->select('m.nom AS mentionNom', 'COUNT(cc.id) AS nombreCandidatures')
    ->join('cc.mention', 'm')
    ->groupBy('m.id')
    ->getQuery();

        
    $result = $query->getResult();
    $resultvalidated = $queryvalidated->getResult();
    $resultByField = $queryByField->getResult();


    //Pourcentage

// Calculate the total number of candidatures
    $totalCandidatures = array_sum(array_column($result, 'nombreCandidatures'));

// Calculate the percentage for each row
foreach ($result as &$row) {
    $row['pourcentageCandidatures'] = ($row['nombreCandidatures'] / $totalCandidatures) * 100;
}

    return $this->render('frontend/recteur/dashboard.html.twig', [
        'result' => $result,
        'resultvalidated' => $resultvalidated,
        'resultByField' => $resultByField
    ]);
}



    /** @var array $commonParams */
    static $commonParams = [
        'workspaceTitle'      => 'Recteur',
        'edtPath'             => 'front_recteur_gestion_emploi_du_temps',
        'fpePath'             => 'front_recteur_presence_enseignant_index',
        'fpeFichePath'        => 'front_recteur_presence_enseignant_matiere_fiche',
        'fpeEditPath'         => 'front_recteur_presence_enseignant_edit',
        'noteIndex'           => 'front_recteur_enseignant',
        'noteEdit'            => 'front_recteur_manage_notes',
        'calendarIndex'       => 'front_recteur_examen_calendar_list',
        'thesisCalendarIndex' => 'front_recteur_thesis_calendar_list',
        'extraNoteIndex'      => 'front_recteur_extranote_list',
        'extraNoteEdit'       => 'front_recteur_extranote_edit',
        'resultConcoursPath'  => 'front_recteur_concours_result',
        'validateResultConcoursPath'    => 'front_recteur_validate_concours_result',
        'concoursCandidatNotesPath'     => 'front_recteur_concours_candidate_notes',
        'vacationPath'        => 'front_recteur_presence_enseignant_index',
        'vacationEnseignantPath'        => 'front_recteur_vacation_enseignant_index',
        'vacationEnseignantEditPath'    => 'front_recteur_vacation_enseignant_edit',
        'validateVacation'    => 'front_sg_vacation_validation',
        'prestationPath'                => 'front_recteur_prestation_index',
        'prestationDetailsPath'         => 'front_recteur_prestation_details_index',
        'prestationValidationPath'      => 'front_recteur_prestation_validation',
        'prestValidIndexPath'           => 'front_recteur_valid_prestation_index',
        'surveyIndexPath'     => 'front_recteur_surveillance_index',
        'surveyDetailsPath'   => 'front_recteur_surveillance_details',
        'syrveyValidatePath'  => 'front_recteur_surveillance_validate',
        'concoursAjaxResult'  => 'front_recteur_concours_ajax_result'
    ];

    /**
     * @Route("/gestion-emploi-temps", name="front_recteur_gestion_emploi_du_temps")
     * @IsGranted("ROLE_RECTEUR")
     * @param \App\Manager\EmploiDuTempsManager       $emploiDuTempsManager
     * @param \App\Services\AnneeUniversitaireService $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function edtList(
        EmploiDuTempsManager $emploiDuTempsManager,
        AnneeUniversitaireService $anneeUniversitaireService,
        PaginatorInterface $paginator,
        Request $request
    ) {
        $emploiDuTemps = $emploiDuTempsManager->getByMentionAndOrCollegeYear($anneeUniversitaireService->getCurrent());
        // Paginer les données
        $page = $request->query->getInt('page', 1);
        $pagination = $paginator->paginate(
            $emploiDuTemps, // Vos données à paginer
            $page, // Numéro de la page
            $itemsPerPage=20 // Nombre d'éléments par page
        );

        $params = [
            'emploiDuTemps' => $emploiDuTemps,
            'pagination'    => $pagination
        ];

        $params = array_merge($params, self::$commonParams);

        return $this->render(
            'frontend/chef-mention/edtList.html.twig',
            $params
        );
    }

    /**
     * @Route("/vacation/list", name="front_recteur_presence_enseignant_index", methods={"GET", "POST"})
     * @param \App\Manager\FichePresenceEnseignantManager $ficheEnseignantManager
     * @param \App\Services\AnneeUniversitaireService     $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @IsGranted("ROLE_RECTEUR")
     */
    public function fichePresenceEnseignantList(
        Request                         $request,
        EmploiDuTempsManager            $edtManager,
        CalendrierPaiementManager       $calPaiementManager,
        CalendrierPaiementRepository    $calPaiementRepo,
        FichePresenceEnseignantManager  $ficheEnseignantManager,
        AnneeUniversitaireService       $anneeUniversitaireService,
        WorkflowStatutService           $workflowStatService,
        MentionManager                  $mentionManager
    ) {
        $user = $this->getUser();
        $c = $request->get('c', '');
        $m = $request->get('m', '');
        $selectedCalPaiement = $c? $calPaiementManager->load($c) : $calPaiementRepo->findDefault();
        $calPaiements = $calPaiementManager->loadAll();
        $mentions           = $mentionManager->loadAll();
        $currentAnneUniv    = $anneeUniversitaireService->getCurrent();
        $profilListStatut = $workflowStatService->getStatutForListByProfil($user->getRoles());
        $profilNextStatut = $workflowStatService->getResourceNextStatut($profilListStatut);
        $profilPrevStatut = $workflowStatService->getEdtPreviousStatut($profilListStatut);
        $fPresenceEnseignants = $edtManager->getCurrentVacation(
            $currentAnneUniv->getId(),
            $m,
            $selectedCalPaiement
        );

        $params = [
                'c'                     => $selectedCalPaiement ? $selectedCalPaiement->getId() : 0,
                'm'                     => $m,
                'calPaiements'          => $calPaiements,
                'mentions'              => $mentions,
                'fPresenceEnseignants'  => $fPresenceEnseignants,
                'profilListStatut'  => $profilListStatut,
                'profilNextStatut'  => $profilNextStatut,
                'profilPrevStatut'  => $profilPrevStatut
            ];

        $params = array_merge($params, self::$commonParams);

        return $this->render(
            'frontend/chef-mention/presence-enseignant-list.html.twig', $params
        );
    }

    /**
     * @Route("/etudiants", name="front_recteur_student_index", methods={"GET"})
     * @param \App\Manager\etudiantManager $studentManager
     * @param \App\Services\AnneeUniversitaireService     $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @IsGranted("ROLE_RECTEUR")
     */
    public function studentIndex(
        Request                         $request,
        EtudiantManager                 $studentManager,
        MentionManager                  $mentionManager,
        NiveauManager                   $niveauManager,
        ParcoursManager                 $parcoursManager,
        AnneeUniversitaireService       $anneeUniversitaireService) {
        $mention = $request->get('m', 0);
        $niveau  = $request->get('n', 0);
        $currParcours  = $request->get('p', 0);
        $studentFilters = [
            'mention' => $mention,
            'niveau'  => $niveau
        ];
        $mentions = $mentionManager->loadAll();
        $niveaux  = $niveauManager->loadAll();
        $parcours = $parcoursManager->loadBy($studentFilters, ['nom' => 'ASC']);
        if($currParcours) $studentFilters['parcours'] = $currParcours;
        // $students = $studentManager->loadby($studentFilters, ['immatricule' => 'ASC']);
        $anneeUniversitaire = $anneeUniversitaireService->getCurrent();
        $students = $studentManager->getActive(
            $mentionManager->load($mention), 
            $niveauManager->load($niveau), 
            $parcoursManager->load($currParcours)
        );       
        $params = [
                'mentions'         => $mentions,
                'niveaux'          => $niveaux,
                'parcours'         => $parcours,
                'students'         => $students,
                'm' => $mention,
                'n' => $niveau,
                'p' => $currParcours
            ];
        $params = array_merge($params, self::$commonParams);
        return $this->render(
            'frontend/recteur/etudiant/index.html.twig',
            $params
        );
    }

    /**
     * @Route("/etudiant/new", name="front_recteur_student_new", methods={"GET", "POST"})
     * @param \App\Manager\etudiantManager $studentManager
     * @param \App\Services\AnneeUniversitaireService     $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @IsGranted("ROLE_RECTEUR")
     */
    public function studentNew(
        Request                         $request,
        EtudiantManager                 $studentManager,
        MentionManager                  $mentionManager,
        NiveauManager                   $niveauManager,
        ParcoursManager                 $parcoursManager,
        AnneeUniversitaireService       $anneeUniversitaireService,
        UserPasswordEncoderInterface    $passwordEncoder,
        ProfilManager                   $profilManager,
        EtudiantService                 $etudiantService
    ) {        
        $mentions[] = $mentionManager->loadAll();
        $niveaux[]  = $niveauManager->loadAll();
        $parcours = [];
        $anneeUniversitaire = $anneeUniversitaireService->getCurrent();
        /** @var \App\Entity\Etudiant $student */
        $student = $studentManager->createObject();
        $form    = $this->createForm(EtudiantInfoFormType::class, $student,
            [
                'em' => $this->getDoctrine()->getManager()
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $cv                 = $form->get('cv')->getData();
            $lettreMotivation   = $form->get('lettreMotivation')->getData();
            $photo1             = $form->get('photo1')->getData();
            $photo2             = $form->get('photo2')->getData();
            $acteNaissance      = $form->get('acteNaissance')->getData();
            $baccFile           = $form->get('baccFile')->getData();
            $cinFile            = $form->get('cinFile')->getData();
            $autreDocFichier    = $form->get('autreDocFichier')->getData();
            $directory     = $this->getParameter('students_directory');
            $uploader      = new \App\Services\FileUploader($directory);
            $today         = new \DateTime();
            $fileDirectory = UtilFunctionService::seoFriendlyUrl($student->getLastName()) . "-" . $today->getTimestamp();

            // Upload file
            if ($cv) {
                $cvDisplay = $uploader->upload($cv, $directory, $fileDirectory, false);
                $student->setCv($fileDirectory . "/" . $cvDisplay["filename"]);
            }
            if ($lettreMotivation) {
                $lettreMotivationDisplay = $uploader->upload($lettreMotivation, $directory, $fileDirectory, false);
                $student->setLettreMotivation($fileDirectory . "/" . $lettreMotivationDisplay["filename"]);
            }
            
            if ($photo1) {
                $photo1Display = $uploader->upload($photo1, $directory, $fileDirectory, false);
                $student->setPhoto1($fileDirectory . "/" . $photo1Display["filename"]);
            }
            
            if ($acteNaissance) {
                $acteNaissanceDisplay = $uploader->upload($acteNaissance, $directory, $fileDirectory, false);
                $student->setActeNaissance($fileDirectory . "/" . $acteNaissanceDisplay["filename"]);
            }
            if ($baccFile) {
                $baccFileDisplay = $uploader->upload($baccFile, $directory, $fileDirectory, false);
                $student->setBaccFile($fileDirectory . "/" . $baccFileDisplay["filename"]);
            }
            if ($cinFile) {
                $cinFileDisplay = $uploader->upload($cinFile, $directory, $fileDirectory, false);
                $student->setCinFile($fileDirectory . "/" . $cinFileDisplay["filename"]);
            }
            if ($autreDocFichier) {
                $autreDocFichierDisplay = $uploader->upload($autreDocFichier, $directory, $fileDirectory, false);
                $student->setAutreDocFichier($fileDirectory . "/" . $autreDocFichierDisplay["filename"]);
            }

            $user  = new User();
            $user
                ->setFirstname($student->getFirstName())
                ->setLastname($student->getLastName())
                ->setEmail($student->getEmail())
                ->setLogin($student->getEmail());
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    '123456789'
                )
            );
            $user->setStatus(User::STATUS_ENABLED);
            $profil = $profilManager->findOneByRoleCode(Roles::ROLE_ETUDIANT);
            $user->setProfil($profil);
            $em->persist($user);

            $student->setUser($user);
            $student->setStatus(Etudiant::STATUS_ACTIVE);
            $etudiantService->setMatricule($student);
            $em->persist($student);
            $em->flush();

            return $this->redirectToRoute('front_recteur_presence_enseignant_index');
        }

        $params = [
                'mentions'         => $mentions,
                'niveaux'          => $niveaux,
                'parcours'         => $parcours,
                'form' => $form->createView()
            ];
        $params = array_merge($params, self::$commonParams);
        return $this->render(
            'frontend/recteur/etudiant/new.html.twig',
            $params
        );

    }


    /**
     * @Route("/etudiant/parcours", name="front_recteur_ajax_parcours", methods={"GET"})
     * @param \App\Manager\ParcoursManager $parcoursManager
     * @param \App\Services\AnneeUniversitaireService     $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @IsGranted("ROLE_RECTEUR")
     */
    public function ajaxParcours(
        Request                         $request,
        ParcoursManager                 $parcoursManager
    ) {        
        $mention = $request->get('mention_id', 0);
        $niveau  = $request->get('niveau_id', 0);
        $parcours = $parcoursManager->loadBy(
            [
                'mention' => $mention,
                'niveau'  => $niveau
            ], ['nom' => 'ASC']);
        return $this->render(
            'frontend/recteur/etudiant/_parcours_options.html.twig',
            [
                'parcours' => $parcours
            ]
        );
    }

    /**
     * @Route("/vacations/enseignant/{id}", name="front_recteur_vacation_enseignant_index", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Enseignant                    $enseignant
     * @param \App\Manager\CalendrierPaiementManager    $calPaiementManager
     * @param \App\Manager\EmploiDuTempsManager         $edtManager
     * @param \App\Service\AnneeUniversitaireService    $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @IsGranted({"ROLE_RECTEUR"})
     */
    public function vacationEnseignantList(
        Request                         $request, 
        Enseignant                      $enseignant,
        EmploiDuTempsManager            $edtManager,
        CalendrierPaiementManager       $calPaiementManager,
        AnneeUniversitaireService       $anneeUniversitaireService,
        WorkflowStatutService           $workflowStatService)
    {
        $user = $this->getUser();
        $c = $request->get('c', '');
        $m = $request->get('m', '');
        $selectedCalPaiement = $c? $calPaiementManager->load($c) : "";
        $calPaiements = $calPaiementManager->loadAll();
        $currentAnneUniv    = $anneeUniversitaireService->getCurrent();
        $profilListStatut = $workflowStatService->getStatutForListByProfil($user->getRoles());
        $profilNextStatut = $workflowStatService->getResourceNextStatut($profilListStatut);
        $profilPrevStatut = $workflowStatService->getEdtPreviousStatut($profilListStatut);
        $fPresenceEnseignants = $edtManager->getEnseignantVacation(
            $enseignant->getId(),
            $currentAnneUniv->getId(),
            $m,
            $selectedCalPaiement
        );

        $params = [
                'c'                     => $c,
                'm'                     => $m,
                'enseignant'            => $enseignant,
                'calPaiement'           => $selectedCalPaiement,
                'fPresenceEnseignants'  => $fPresenceEnseignants,
                'profilListStatut'  => $profilListStatut,
                'profilNextStatut'  => $profilNextStatut,
                'profilPrevStatut'  => $profilPrevStatut
            ];

        $params = array_merge($params, self::$commonParams);

        return $this->render(
            'frontend/chef-mention/vacation/enseignant-index.html.twig', $params
        );
    }

    /**
     * @Route("/vacation/enseignant/{id}/edit", name="front_recteur_vacation_enseignant_edit", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Enseignant $enseignant
     * @param \App\Manager\EmploiDuTempsManager $edtManager
     * @param \App\Service\AnneeUniversitaireService $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @IsGranted("ROLE_RECTEUR")
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

            return $this->redirectToRoute('front_recteur_presence_enseignant_index');
        }
        
        $params = [
            'c'             => $c,
            'edt'           => $edt,
            'edtNextStatut' => $edtNextStatut,
            'edtPreviousStatut' => $edtPreviousStatut
        ];
        $params = array_merge($params, self::$commonParams);

        return $this->render(
            'frontend/chef-mention/vacation/enseignant-edit.html.twig',
            $params
        );
    } 

    /**
     * @IsGranted("ROLE_RECTEUR")
     * @Route("/presence-enseignant/matiere/{id}/fiche", name="front_recteur_presence_enseignant_matiere_fiche", methods={"GET", "POST"})
     * @param \App\Entity\Matiere                         $matiere
     * @param \App\Manager\FichePresenceEnseignantManager $ficheEnseignantManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function fichePresenceEnseignantFiche(
        Matiere $matiere,
        FichePresenceEnseignantManager $ficheEnseignantManager
    ) {
        $fPresenceEnseignants = $ficheEnseignantManager->getTeacherMatiereDetailHour($matiere->getId());

        $params = [
            'matiere'              => $matiere,
            'fPresenceEnseignants' => $fPresenceEnseignants
        ];
        $params = array_merge($params, self::$commonParams);

        return $this->render(
            'frontend/chef-mention/presence-enseignant-fiche.html.twig',
            $params
        );
    }

    /**
     * @IsGranted("ROLE_RECTEUR")
     * @Route("/presence-enseignant/edit/{id}", name="front_recteur_presence_enseignant_edit", methods={"GET", "POST"})
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
            $fichePresenceEnseignant->setStatut('RECTEUR_VALIDATED');
            $this->getDoctrine()->getManager()->flush();

            /** @var \App\Entity\FichePresenceEnseignantHistorique $historical */
            $historical = $historiqueManager->createObject();
            $historical->setUser($this->getUser());
            $historical->setFichePresenceEnseignant($fichePresenceEnseignant);
            $historical->setStatus('RECTEUR_VALIDATED');
            $historiqueManager->save($historical);

            return $this->redirectToRoute('front_recteur_presence_enseignant_index');
        } else {
            dump($form);
        }

        $params = [
            'fpEnseignant' => $fichePresenceEnseignant,
            'form'         => $form->createView()
        ];
        $params = array_merge($params, self::$commonParams);

        return $this->render(
            'frontend/chef-mention/presence-enseignant-edit.html.twig',
            $params
        );
    }

    /**
     * @IsGranted("ROLE_RECTEUR")
     * @Route("/validate/vacations/", name="front_sg_vacation_validation", methods={"POST"})
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
        EmploiDuTempsManager        $edtManager,
        CalendrierPaiementManager   $calPaiementManager,
        WorkflowStatutService       $workflowStatService,
        AnneeUniversitaireService   $anneeUniversitaireService
    ) {
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
                $vacationHistory->setUpdatedAt(new \DateTime());
                $entityManager->persist($vacationHistory);
            }
        };
        $entityManager->flush();
        
        return new JsonResponse(array('statut' => '200'));
    }

    /**
     * @Route("/notes/{id}", name="front_recteur_notes")
     * @IsGranted("ROLE_RECTEUR")
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
        $teachersMention = $enseignantManager->getByMention();

        $matieres = $matiereManager->getByEnseignant($enseignant->getId());

        $params = [
            'teachers'  => $teachersMention,
            'matieres'  => $matieres,
            'teacherId' => $enseignant->getId()
        ];
        $params = array_merge($params, self::$commonParams);

        return $this->render(
            'frontend/chef-mention/notes/notes.html.twig',
            $params
        );
    }

    /**
     * @Route("/notes", name="front_recteur_enseignant")
     * @IsGranted("ROLE_RECTEUR")
     * @param \App\Manager\EnseignantManager $enseignantManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function enseignants(EnseignantManager $enseignantManager)
    {
        $teachersMention = $enseignantManager->getByMention();

        $params = [
            'teachers' => $teachersMention
        ];
        $params = array_merge($params, self::$commonParams);

        return $this->render(
            'frontend/chef-mention/notes/notes.html.twig',
            $params
        );
    }

    /**
     * Add new notes function
     * @IsGranted("ROLE_RECTEUR")
     * @Route("/notes/{teacher_id}/{matiere_id}/add", name="front_recteur_manage_notes",  methods={"GET", "POST"})
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

            return $this->redirectToRoute('front_recteur_notes', ['id' => $enseignant->getId()]);
        }

        $params = [
            'teacherId' => $enseignant->getId(),
            'etudiants' => $etudiants,
            'matiere'   => $matiere,
            'form'      => $form->createView(),
        ];
        $params = array_merge($params, self::$commonParams);

        return $this->render(
            'frontend/chef-mention/notes/addNote.html.twig',
            $params
        );
    }

    /**
     * @Route("/calendrier-examen", name="front_recteur_examen_calendar_list", methods={"GET"})
     * @IsGranted("ROLE_RECTEUR")
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

        $params = [
            'items' => $calendarList
        ];
        $params = array_merge($params, self::$commonParams);

        return $this->render(
            'frontend/chef-mention/examen/index.html.twig',
            $params
        );
    }

    /**
     * @Route("/calendrier-soutenances", name="front_recteur_thesis_calendar_list", methods={"GET"})
     * @IsGranted("ROLE_ASSISTANT", "ROLE_RECTEUR")
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

        $params = [
            'items' => $calendarList
        ];
        $params = array_merge($params, self::$commonParams);

        return $this->render(
            'frontend/chef-mention/thesis/index.html.twig',
            $params
        );
    }

    /**
     * @IsGranted("ROLE_RECTEUR")
     * @Route("/concours/result/index", name="front_recteur_concours_result", methods={"GET", "POST"})
     * @param \App\Manager\ConcoursManager              $concoursManager
     * @param \App\Manager\ConcoursNotesManager         $notesManager
     * @param \App\Serive\ConcoursConfigManager         $concoursConfManager
     * @param \App\Manager\ParcoursManager              $parcoursManager
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function ShowConcoursResult(
        Request                     $request,
        MentionManager              $mentionManager,
        ConcoursManager             $concoursManager,
        ParcoursManager             $parcoursManager,
        ConcoursNotesManager        $notesManager,
        ConcoursConfigManager       $concoursConfManager
    ) {
        $user = $this->getUser();
        $selectedM       = $request->get('m');
        $selectedC       = $request->get('c');
        $selectedP       = $request->get('p');
        $concoursConf = $concoursConfManager->loadOneBy([]);
        $currentAnneUniv= $concoursConf->getAnneeUniversitaire();

        $tParcoursFilters = [];
        $tConcoursFilters = [];
        $selectedMention  = '';
        if ($selectedM) {
            $selectedMention             = $mentionManager->load($selectedM);
            $tParcoursFilters['mention'] = $selectedMention;
            $tConcoursFilters['mention'] = $selectedMention;
        }
        if ($selectedP) {
            $selectedParcours             = $parcoursManager->load($selectedP);
            $tConcoursFilters['parcours'] = $selectedParcours;
        }

        $mentions = $mentionManager->loadAll();
        $parcours = $parcoursManager->loadBy($tParcoursFilters, ['nom' => 'ASC']);
        $concours = $concoursManager->loadBy($tConcoursFilters, ['libelle' => 'ASC']);

        $resultats        = [];
        $selectedConcours = '';
        $form             = '';
        /** @var \App\Entity\Concours $selectedConcours */
        if ($selectedMention && $selectedC && ($selectedConcours = $concoursManager->load($selectedC))) {
            $resultats = $notesManager->getLevelNotesByMixedParams(
                $selectedConcours,
                $selectedMention,
                $currentAnneUniv->getId(),
                $selectedP,
                null
            );
        }

        $urlParams = [
                'c'                => $selectedC,
                'p'                => $selectedP,
                'm'                => $selectedM,
                'mentions'          => $mentions,
                'concours'         => $concours,
                'parcours'         => $parcours,
                'selectedConcours' => $selectedConcours,
                'form'             => $form,
                'resultats'        => $resultats
            ];
        $params = array_merge($urlParams, self::$commonParams);

        return $this->render('frontend/chef-mention/concours/result-list.html.twig', $params);
    }






    /**
     * @Route("/concours/ajax-result", name="front_recteur_concours_ajax_result", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\MentionManager               $mentionManager
     * @param \App\Manager\ConcoursManager              $concoursManager
     * @param \App\Manager\ParcoursManager              $parcoursManager
     * @param \App\Manager\ConcoursCandidatureManager   $manager
     * @param \App\Service\ConcoursConfigManager        $concoursConfManager
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function concoursAjaxResult(
        Request                         $request,
        MentionManager                  $mentionManager,
        ConcoursManager                 $concoursManager,
        ParcoursManager                 $parcoursManager,
        ConcoursCandidatureManager      $manager,
        ConcoursConfigManager           $concoursConfManager,
        ConcoursNotesManager            $notesManager
    ) {
        $selectedM  = $request->get('m');
        $selectedP  = $request->get('p');
        $selectedC  = $request->get('c');
        $qString    = $request->get('q');
        $selectedConcours = "";
        $concoursConf = $concoursConfManager->loadOneBy([]);
        $currentAnneUniv= $concoursConf->getAnneeUniversitaire();  

        $tParcoursFilters = [];
        $tConcoursFilters = [];
        $selectedMention  = '';
        if ($selectedM) {
            $selectedMention             = $mentionManager->load($selectedM);
            $tParcoursFilters['mention'] = $selectedMention;
            $tConcoursFilters['mention'] = $selectedMention;
        }
        if ($selectedP) {
            $selectedParcours             = $parcoursManager->load($selectedP);
            $tConcoursFilters['parcours'] = $selectedParcours;
        }

        $mentions = $mentionManager->loadAll();
        $parcours = $parcoursManager->loadBy($tParcoursFilters, ['nom' => 'ASC']);
        $concours = $concoursManager->loadBy($tConcoursFilters, ['libelle' => 'ASC']);

        $resultats        = [];
        $selectedConcours = '';
        $form             = '';
        /** @var \App\Entity\Concours $selectedConcours */
        if ($selectedMention && $selectedC && ($selectedConcours = $concoursManager->load($selectedC))) {
            $resultats = $notesManager->getLevelNotesByMixedParams(
                $selectedConcours,
                $selectedMention,
                $currentAnneUniv->getId(),
                $selectedP,
                null,
                null,
                $qString
            );
        }
        $urlParams = [
                'p'                => $selectedP,
                'c'                => $selectedC,
                'selectedConcours' => $selectedConcours,
                'resultats'        => $resultats
            ]; 
        $params = array_merge($urlParams, self::$commonParams);

        return $this->render('frontend/recteur/concours/ajax-result.html.twig', $params);
    }

    /**
     * @IsGranted("ROLE_RECTEUR")
     * @Route("/concours/result/validate/{id}", name="front_recteur_validate_concours_result", methods={"POST"})
     * @param \App\Entity\Concours                      $concours
     * @param \App\Manager\ConcoursManager              $concoursManager
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function validateConcoursResult(Concours $concours, ConcoursCandidatureManager $concoursCandManager, Request $request, ConcoursNotesManager $concoursNotesManager, AnneeUniversitaireService $anneeUnivService, LoggerInterface $logger, Mailer $mailer)
    {      
        $candidateAdmittedIds = $request->get('candidat_id', array());   
        $em = $this->getDoctrine()->getManager();     
        $concours->setResultStatut(Concours::STATUS_VALIDATED_RECTEUR);
        $em->persist($concours);
        $concoursCandManager->setConcoursResult($concours, $candidateAdmittedIds);
        $parcoursId = $concours->getParcours() ? $concours->getParcours()->getId() : null;
        $currentAnneUniv = $anneeUnivService->getCurrent();
        $concoursResults = $concoursNotesManager->getResultByConcours($concours, $concours->getMention(), $currentAnneUniv->getId(), $parcoursId);

        return $this->redirectToRoute('front_recteur_concours_result');
    }

    /**
     * @IsGranted("ROLE_RECTEUR")
     * @Route("/concours/{id}/result/send", name="front_recteur_send_concours_result", methods={"POST"})
     * @param \App\Entity\Concours                      $concours
     * @param \App\Manager\ConcoursManager              $concoursManager
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function sendResult(Concours $concours, ConcoursCandidatureRepository $conCandidatureRepo, Request $request, LoggerInterface $logger, Mailer $mailer)
    {      
        $candidateAdmittedIds = $request->get('candidat_id', array());   
        $candidates = $conCandidatureRepo->findByIds($candidateAdmittedIds);
        // try {                
            $siteConfig   = $this->getParameter('site');
            $mailerConfig = $this->getParameter('mailer');

            $params = [
                "sender"      => $mailerConfig['smtp_username'],
                "pwd"         => $mailerConfig['smtp_password'],
                "sendTo"      => array_map(function($item){
                    return $item->getEmail();
                }, $candidates),
                "subject"     => 'Resultat concours',
                "senderName"  => $siteConfig['name'],
                "senderEmail" => $siteConfig['contact_email'],
            ];

            $html = $this->renderView(
                "frontend/concours/email-concours-result.html.twig",
                [
                    'concours' => $concours,
                    'site'     => $siteConfig
                ]
            );
            // Send email
            

            
            //$mailer->sendMail($params, $html);




        // } catch (\Throwable $e) {
        //     $logger->error('[RecteurController::validateConcoursResult] error=' . $e->getMessage());
        // }

        return new JsonResponse(true);
        //return $this->redirectToRoute('front_recteur_concours_result');
    }

    /**
     * @Route("/concours/notes/{id}", name="front_recteur_concours_candidate_notes", methods={"GET", "POST"})
     * @IsGranted("ROLE_RECTEUR")
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
        $user    = $this->getUser();
        $selectedC        = $request->get('c');
        $selectedConcours = $concoursManager->load($selectedC);
        $selectedP        = $request->get('p');
        $selectedM        = $request->get('m');

        $concoursNotes = $concoursNotesManager->createObject();
        $form          = $this->createForm(ConcoursNotesFormType::class, $concoursNotes);
        $form->handleRequest($request);

        $matieres      = $concoursMatiereManager->findByCriteria(
            [
                'mention'  => $selectedM,
                'concours' => $selectedC,
                'parcours' => $selectedP
            ],
            [
                'libelle' => 'ASC'
            ]
        );
        $notes         = $concoursNotesManager->getByCandidateIdAndConcoursId($candidate->getId(), $selectedC);
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
        if ($form->isSubmitted() ) {
            $formData = $request->request->all();            
            $concoursNotesManager->manageNotes(
                $formData,
                $candidate,
                $concoursCandidatureManager,
                $concoursMatiereManager,
                $concoursManager
            );

            return $this->redirectToRoute('front_recteur_concours_result', ['c' => $selectedC, 'p' => $selectedP, 'm' => $selectedM]);
        }
        $urlParams = [
                'c'             => $selectedC,
                'p'             => $selectedP,
                'm'             => $selectedM,
                'candidate'     => $candidate,
                'matieresNotes' => $matieresNotes,
                'form'          => $form->createView(),
                'concours'      => $selectedConcours
            ];
        $params = array_merge($urlParams, self::$commonParams);

        return $this->render(
            'frontend/chef-mention/concours/notes.html.twig',
            $params
        );
    }

    /**
     * @IsGranted("ROLE_RECTEUR")
     * @Route("/extra-note", name="front_recteur_extranote_list", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\ExtraNotesManager            $notesManager
     * @param \App\Manager\MentionManager               $mentionManager
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     * @param \App\Manager\EtudiantManager              $studentManager
     * @param \App\Manager\NiveauManager                $niveaumanager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function extraNoteList(
        Request $request,
        ExtraNotesManager $notesManager,
        MentionManager $mentionManager,
        AnneeUniversitaireService $anneeUniversitaireService,
        EtudiantManager $studentManager,
        NiveauManager $niveaumanager
    ) {
        $selectedM   = $request->get('m', 0);
        $collegeYear = $anneeUniversitaireService->getCurrent();
        $mentions    = $mentionManager->getByInscriptionAndCollegeYear($collegeYear);
        $niveaux     = $niveaumanager->loadBy([], ['libelle' => 'ASC']);
        $students    = $studentManager->getByMentionAndCollegeYear(
            $mentionManager->getReference($selectedM), $collegeYear
        );
        $notes       = $notesManager->getByStudentsAndCollegeYear($students, $collegeYear);

        $params = [
            'm'        => (int) $selectedM,
            'mentions' => $mentions,
            'notes'    => $notes,
            'niveaux'  => $niveaux,
        ];
        $params = array_merge($params, self::$commonParams);

        return $this->render(
            'frontend/recteur/notes/extranote-list.html.twig',
            $params
        );
    }

    /**
     * @IsGranted("ROLE_RECTEUR")
     * @Route("/extra-note/{mention_id}/{note_id}/edit", name="front_recteur_extranote_edit", methods={"GET", "POST"})
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
                'status'  => ExtraNote::$recteurStatusList
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

            return $this->redirectToRoute('front_recteur_extranote_list', ['m' => $mention->getId()]);
        }

        $params = [
            'form'  => $form->createView(),
            'notes' => $note
        ];
        $params = array_merge($params, self::$commonParams);

        return $this->render(
            'frontend/chef-mention/notes/extranote-edit.html.twig',
            $params
        );
    }

    /**
     * @IsGranted("ROLE_RECTEUR")
     * @Route("/prestations", name="front_recteur_prestation_index", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\PrestationManager            $prestationManager
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function prestationIndex(
        Request                     $request,
        PrestationManager           $prestationManager,
        CalendrierPaiementManager   $calPaiementManager,
        CalendrierPaiementRepository   $calPaiementRepo,
        AnneeUniversitaireService   $anneeUniversitaireService,
        WorkflowStatutService       $workflowStatService
    ) {
        $currentUserRoles = $this->getUser()->getRoles();
        $userDisplayStatut = $workflowStatService->getStatutForListByProfil($currentUserRoles);
        $c = $request->get('c', '');
        $selectedCalPaiement = $c? $calPaiementManager->load($c) : $calPaiementRepo->findDefault();
        $calPaiements = $calPaiementManager->loadAll();
        $collegeYear = $anneeUniversitaireService->getCurrent();
        $prestations = $prestationManager->getListGroupByAthor(
            $collegeYear->getId(), 
            null,
            $selectedCalPaiement,
            $userDisplayStatut
        );

        $params = [
            'c'            => $c,
            'calPaiements' => $calPaiements,
            'list'         => $prestations
        ];

        $params = array_merge($params, self::$commonParams);

        return $this->render(
            'frontend/chef-mention/prestation/index.html.twig', $params
        );
    }

    /**
     * @IsGranted("ROLE_RECTEUR")
     * @Route("/prestations/details/{id}", name="front_recteur_prestation_details_index", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\PrestationManager            $prestationManager
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function PrestationDetailsIndex(
        Request                     $request,
        User                        $user,
        PrestationManager           $prestationManager,
        CalendrierPaiementManager   $calPaiementManager,
        CalendrierPaiementRepository   $calPaiementRepo,
        AnneeUniversitaireService   $anneeUniversitaireService,
        WorkflowStatutService       $workflowStatService
    ) {
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
            null, 
            $selectedCalPaiement,
            null
        );

        $params = [
            'c'            => $c,
            'author'       => $user, 
            'calPaiements' => $calPaiements,
            'list'         => $prestations,
            'profilListStatut'  => $profilListStatut, 
            'profilNextStatut'  => $profilNextStatut,
            'profilPrevStatut'  => $profilPrevStatut
        ];

        $params = array_merge($params, self::$commonParams);

        $params = array_merge($params, self::$commonParams);

        return $this->render(
            'frontend/chef-mention/prestation/resume-index.html.twig', $params
        );
    }

    /**
     * IsGranted("ROLE_RECTEUR")
     * @Route("/prestations/{id}/validatable/", name="front_recteur_valid_prestation_index", methods={"GET"})
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
            null, 
            $selectedCalPaiement,
            null
        );

        $params = [
                'c'            => $selectedCalPaiement ? $selectedCalPaiement->getId() : 0,
                'author'       => $this->getUser(), 
                'user'         => $user, 
                'calPaiements' => $calPaiements,
                'list'         => $prestations,
                'profilListStatut'  => $profilListStatut,
                'profilNextStatut'  => $profilNextStatut,
                'profilPrevStatut'  => $profilPrevStatut
            ];

        $params = array_merge($params, self::$commonParams);

        return $this->render(
            'frontend/chef-mention/prestation/validatable-index.html.twig', $params
        );
    }

    /**
     * @IsGranted("ROLE_RECTEUR")
     * @Route("/validate/prestations/", name="front_recteur_prestation_validation", methods={"POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\PrestationManager            $prestationManager
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function validatePrestation(
        Request                     $request,
        PrestationManager           $prestationManager,
        CalendrierPaiementManager   $calPaiementManager,
        WorkflowStatutService       $workflowStatService,
        AnneeUniversitaireService   $anneeUniversitaireService
    ) {
        $currentUserRoles = $this->getUser()->getRoles();
        $userDisplayStatut = $workflowStatService->getStatutForListByProfil($currentUserRoles);
        $prestationIds = $request->get('prestation', array());
        $entityManager = $this->getDoctrine()->getManager();

        foreach($prestationIds as $id) {
            $currPrest = $prestationManager->load($id);
            $currPrestNextStatut = $currPrest->getStatut() == $userDisplayStatut ? 
                $workflowStatService->getResourceNextStatut($userDisplayStatut) :
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
        }

        $entityManager->flush();
        
        return new JsonResponse(array('statut' => '200'));
    }

    /**
     * IsGranted("ROLE_RECTEUR")
     * @Route("/surveillances", name="front_recteur_surveillance_index", methods={"GET"})
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
        WorkflowStatutService       $workflowStatService,
        MentionManager              $mentionManager
    ) {
         $c = $request->get('c', '');
         $m = $request->get('m', '');
        $selectedCalPaiement = $c? $calPaiementManager->load($c) : $calPaiementRepo->findDefault();
        $user               = $this->getUser();
        $calPaiements = $calPaiementManager->loadAll();
        $mentions           = $mentionManager->loadAll();
        $currentAnneUniv    = $anneeUniversitaireService->getCurrent();

        $profilListStatut = $workflowStatService->getStatutForListByProfil($user->getRoles());
        $profilNextStatut = $workflowStatService->getResourceNextStatut($profilListStatut);
        $profilPrevStatut = $workflowStatService->getEdtPreviousStatut($profilListStatut);

        $surveillants = $calExamenManager->getCurrentVacation(
            $currentAnneUniv->getId(),
            $m,
            $selectedCalPaiement
        );

        $params = [
                'c'            => $selectedCalPaiement ? $selectedCalPaiement->getId() : 0,
                'm'            => $m,
                'calPaiements' => $calPaiements,
                'mentions'     => $mentions,
                'list'         => $surveillants,
                'profilListStatut'      => $profilListStatut,
                'profilNextStatut'      => $profilNextStatut,
                'profilPrevStatut'      => $profilPrevStatut
            ];

        $params = array_merge($params, self::$commonParams);

        return $this->render(
            'frontend/chef-mention/surveillance/index.html.twig', $params
        );
    }

    /**
     * IsGranted("ROLE_RECTEUR")
     * @Route("/surveillance/{id}/details", name="front_recteur_surveillance_details", methods={"GET", "POST"})
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
        $m = $request->get('m', '');
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
            $m,
            $selectedCalPaiement,
            null
        );

        $params = [
                'c'                     => $c,
                'm'                     => $m,
                'surveillant'           => $surveillant,
                'calPaiement'           => $selectedCalPaiement,
                'list'                  => $surveillantVacations,
                'profilListStatut'      => $profilListStatut,
                'profilNextStatut'      => $profilNextStatut
            ];

        $params = array_merge($params, self::$commonParams);

        return $this->render(
            'frontend/chef-mention/surveillance/details-index.html.twig', $params
        );
    }  

    /**
     * IsGranted("ROLE_RECTEUR")
     * @Route("/validate/surveillance/", name="front_recteur_surveillance_validate", methods={"POST"})
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