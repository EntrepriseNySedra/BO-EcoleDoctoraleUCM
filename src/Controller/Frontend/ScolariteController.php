<?php

namespace App\Controller\Frontend;

use App\Entity\Absences;
use App\Entity\ConcoursCandidature;
use App\Entity\Ecolage;
use App\Entity\EmploiDuTemps;
use App\Entity\FichePresenceEnseignant;
use App\Entity\FraisScolarite;
use App\Entity\Inscription;
use App\Entity\Mention;
use App\Entity\PaiementHistory;
use App\Entity\Roles;
use App\Entity\User;

use App\Entity\Etudiant;
use App\Entity\DemandeDoc;

use App\Form\AbsenceType;
use App\Form\ConcoursEmploiDuTempsType;
use App\Form\ExtraNoteType;
use App\Form\FichePresenceEnseignantType;
use App\Form\FraisScolariteType;
use App\Form\EtudiantInfoFormType;

use App\Manager\AbsencesManager;
use App\Manager\AnneeUniversitaireManager;
use App\Manager\CandidatureHistoriqueManager;
use App\Manager\CalendrierPaiementManager;
use App\Manager\ConcoursCandidatureManager;
use App\Manager\ConcoursConfigManager;
use App\Manager\ConcoursEmploiDuTempsManager;
use App\Manager\ConcoursManager;
use App\Manager\ConcoursMatiereManager;
use App\Manager\EmploiDuTempsManager;
use App\Manager\EtudiantManager;
use App\Manager\ExtraNoteHistoriqueManager;
use App\Manager\ExtraNotesManager;
use App\Manager\FichePresenceEnseignantHistoriqueManager;
use App\Manager\FichePresenceEnseignantManager;
use App\Manager\FraisScolariteManager;
use App\Manager\InscriptionManager;
use App\Manager\MatiereManager;
use App\Manager\MentionManager;
use App\Manager\NiveauManager;
use App\Manager\NotesManager;
use App\Manager\ParcoursManager;
use App\Manager\ProfilManager;
use App\Manager\RoleManager;
use App\Manager\UniteEnseignementsManager;
use App\Manager\UserManager;
use App\Manager\DemandeDocManager;
use App\Manager\SemestreManager;

use App\Repository\CalendrierPaiementRepository;
use App\Repository\DemandeDocRepository;
use App\Repository\EmploiDuTempsRepository;
use App\Repository\EtudiantRepository;
use App\Repository\FichePresenceEnseignantRepository;
use App\Repository\FraisScolariteRepository;
use App\Repository\MatiereRepository;

use App\Services\AnneeUniversitaireService;
use App\Services\ConcoursService;
use App\Services\EcolageService;
use App\Services\ExportDataService;
use App\Services\Mailer;
use App\Services\NotesService;
use App\Services\UtilFunctionService;
use App\Services\EtudiantService;

use Dompdf\Dompdf;
use Dompdf\Options;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Snappy\Pdf;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use Symfony\Component\Filesystem\Filesystem;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Class SecretariatController
 * @IsGranted({"ROLE_SCOLARITE", "ROLE_SSRS"})
 *
 * @package App\Controller\Frontend
 */
class ScolariteController extends AbstractFrontController
{

    /**
     * @IsGranted({"ROLE_SCOLARITE", "ROLE_SSRS"})
     * @Route("/scolarite/candidature", name="front_scolarite_candidature", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\ConcoursCandidatureManager   $manager
     * @param \App\Manager\MentionManager               $mentionManager
     * @param \App\Manager\NiveauManager                $niveauManager
     * @param \App\Manager\ParcoursManager              $parcoursManager
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     * @param \Knp\Component\Pager\PaginatorInterface   $paginator
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function listCandidature(
        Request $request, 
        ConcoursCandidatureManager $manager,
        MentionManager   $mentionManager,
        NiveauManager   $niveauManager,
        ParcoursManager   $parcoursManager,
        AnneeUniversitaireService $anneeUniversitaireService,
        PaginatorInterface $paginator,
        ConcoursConfigManager $concoursConfManager
    ) : Response {
        $userRoles = $this->getUser()->getRoles();
        $selectedM      = $request->get('m');
        $selectedN      = $request->get('n');
        $selectedP      = $request->get('p');
        $mentions       = $mentionManager->loadBy([], ['nom' => 'ASC']);
        $niveaux        = $niveauManager->loadBy([], ['libelle' => 'ASC']);
        $parcours       = $parcoursManager->loadBy(
            [
                'mention' => $selectedM,
                'niveau'  => $selectedN
            ], 
            ['nom' => 'ASC']);
        //$currentAnneUniv= $anneeUniversitaireService->getCurrent();
        $concoursConf = $concoursConfManager->loadOneBy([]);
        $currentAnneUniv= $concoursConf->getAnneeUniversitaire();

        $tFilters                       = [];
        $tFilters['anneeUniversitaire'] = $currentAnneUniv;
        if($selectedM)
            $tFilters['mention'] = $selectedM;
        if($selectedN)
            $tFilters['niveau']  = $selectedN;
        if($selectedP)
            $tFilters['parcours']= $selectedP;

        $page = $request->query->getInt('page', 1);
        $tFilters['status'] = in_array('ROLE_SSRS', $userRoles) ? ConcoursCandidature::STATUS_APPROVED : ConcoursCandidature::STATUS_CREATED;
        $stFilters = [];
        $stFilters['email_notification'] = 'IS NOT NULL';
        $created            = in_array('ROLE_SSRS', $userRoles) ? $paginator->paginate(
            $manager->findByCriteriaQuery($tFilters),
            $page,
            ConcoursCandidature::PER_PAGE
        ) : $paginator->paginate(
            $manager->findByMixedCriteria($tFilters, $stFilters),
            $page,
            ConcoursCandidature::PER_PAGE
        );
        // dump($tFilters);die;
        $tFilters['status'] = in_array('ROLE_SSRS', $userRoles) ? ConcoursCandidature::STATUS_SU_VALIDATED : ConcoursCandidature::STATUS_APPROVED;
        $validated      = $paginator->paginate(
            $manager->findByCriteriaQuery($tFilters),
            $page,
            ConcoursCandidature::PER_PAGE
        );
        $tFilters['status'] = ConcoursCandidature::STATUS_DISAPPROVED;
        $disapproved    = $paginator->paginate(
            $manager->findByCriteriaQuery($tFilters),
            $page,
            ConcoursCandidature::PER_PAGE
        );

        $tFilters['status'] = ConcoursCandidature::STATUS_SU_VALIDATED;
        $convocations    = $paginator->paginate(
            $manager->findByCriteriaQuery($tFilters),
            $page,
            ConcoursCandidature::PER_PAGE
        );

        return $this->render(
            'frontend/scolarite/candidature.html.twig',
            [
                'm'             => $selectedM,
                'n'             => $selectedN,
                'p'             => $selectedP,
                'mentions'      => $mentions,
                'niveaux'       => $niveaux,
                'parcours'      => $parcours,
                'created'       => $created,
                'validated'   => $validated,
                'disapproved' => $disapproved,
                'convocations' => $convocations
            ]
        );
    }

    /**
     * @Route("/scolarite/candidature/{id}", name="front_scolarite_detail_candidature", methods={"GET"})
     * @param \App\Entity\ConcoursCandidature $candidature
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function detailCandidature(
        ConcoursCandidature $candidature, 
        ConcoursManager     $concoursManager,
        MentionManager      $mentionManager,
        NiveauManager       $niveauManager,
        ParcoursManager     $parcoursManager
    )
    {
        $candidatCivility = "";//array_flip(ConcoursCandidature::$civilityList)[$candidature->getCivility()];
        $mentions = $mentionManager->loadBy(
            [], 
            [
                'nom' => 'ASC'
            ]
        );
        $niveaux = $niveauManager->loadBy(
            [],
            [
                'libelle' => 'ASC'
            ]
        );
        $parcours = $parcoursManager->loadBy(
            [
                'mention'   => $candidature->getMention(),
                'niveau'    => $candidature->getNiveau()
            ]
        );

        return $this->render(
            'frontend/scolarite/candidature-detail.html.twig',
            [
                'mentions'         => $mentions,
                'niveaux'          => $niveaux,
                'parcours'         => $parcours,
                'candidatCivility' => $candidatCivility,
                'candidature'      => $candidature,
                'concours'         => $concoursManager->loadOneBy(['annee_universitaire' => $candidature->getAnneeUniversitaire()], [])
            ]
        );
    }


     /**
     * @Route("/scolarite/candidature/convocated/{id}", name="front_scolarite_detail_candidature_convovated", methods={"GET"})
     * @param \App\Entity\ConcoursCandidature $candidature
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function detailCandidatureconvocated(
        ConcoursCandidature $candidature, 
        ConcoursManager     $concoursManager,
        MentionManager      $mentionManager,
        NiveauManager       $niveauManager,
        ParcoursManager     $parcoursManager
    )
    {
        $candidatCivility = "";//array_flip(ConcoursCandidature::$civilityList)[$candidature->getCivility()];
        $mentions = $mentionManager->loadBy(
            [], 
            [
                'nom' => 'ASC'
            ]
        );
        $niveaux = $niveauManager->loadBy(
            [],
            [
                'libelle' => 'ASC'
            ]
        );
        $parcours = $parcoursManager->loadBy(
            [
                'mention'   => $candidature->getMention(),
                'niveau'    => $candidature->getNiveau()
            ]
        );

        return $this->render(
            'frontend/scolarite/candidature-convocated-detail.html.twig',
            [
                'mentions'         => $mentions,
                'niveaux'          => $niveaux,
                'parcours'         => $parcours,
                'candidatCivility' => $candidatCivility,
                'candidature'      => $candidature,
                'concours'         => $concoursManager->loadOneBy(['annee_universitaire' => $candidature->getAnneeUniversitaire()], [])
            ]
        );
    }

    /**
     * 
     * @Route("/secretariat/candidature/{id}", name="front_scolarite_validate_candidature", methods={"POST"})
     * @param \Symfony\Component\HttpFoundation\Request                             $request
     * @param \App\Entity\ConcoursCandidature                                       $candidature
     * @param \App\Manager\ConcoursCandidatureManager                               $manager
     * @param \App\Manager\CandidatureHistoriqueManager                             $historiqueManager
     * @param \App\Manager\ConcoursEmploiDuTempsManager                             $emploiDuTempsManager
     * @param \App\Manager\ConcoursManager                                          $concoursManager
     * @param \App\Manager\UserManager                                              $userManager
     * @param \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface $passwordEncoder
     * @param \App\Manager\RoleManager                                              $roleManager
     * @param \App\Manager\ProfilManager                                            $profilManager
     * @param \App\Services\Mailer                                                  $mailer
     * @param \Psr\Log\LoggerInterface                                              $logger
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function traitementCandidature(
        Request                         $request,
        ConcoursCandidature             $candidature,
        ConcoursCandidatureManager      $manager,
        CandidatureHistoriqueManager    $historiqueManager,
        ConcoursEmploiDuTempsManager    $emploiDuTempsManager,
        ConcoursManager                 $concoursManager,
        UserManager                     $userManager,
        UserPasswordEncoderInterface    $passwordEncoder,
        RoleManager                     $roleManager,
        ProfilManager                   $profilManager,
        Mailer                          $mailer,
        ConcoursService                 $concoursService,
        MentionManager                  $mentionManager,
        NiveauManager                   $niveauManager,
        ParcoursManager                 $parcoursManager,
        LoggerInterface                 $logger,
        ConcoursConfigManager $concoursConfManager
    ) {

        $status = (int) $request->request->get('action');
        $motif  = $request->request->get('motif');
        $candidature->setStatus($status);
        $candidature->setMotif($motif);
        $candidature->setEmailNotification(NULL);
        $em = $this->getDoctrine()->getManager();
        $em->persist($candidature);
        /** @var \App\Entity\CandidatureHistorique $historical */
        $historical = $historiqueManager->createObject();
        $historical->setCandidature($candidature);
        $historical->setStatus($status);
        $historical->setMotif($motif);
        $em->persist($historical);

        $em->flush();

        return $this->redirectToRoute('front_scolarite_candidature');
    }

    /**
     * @Route("/emploie-du-temps", name="front_scolarite_edt", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edt(Request $request)
    {
        return $this->render(
            'frontend/scolarite/edt.html.twig',
            [

            ]
        );
    }

    /**
     * @Route("/download/paiement/concours/{id}", name="front_scolarite_download_paiement_ref", methods={"GET"})
     * @param \App\Entity\ConcoursCandidature $concoursCandidature
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadPaymentRef(ConcoursCandidature $concoursCandidature)
    {
        $uploadDir = $this->getParameter('concours_directory');
        $file = new File($uploadDir . '/' . $concoursCandidature->getPayementRefPath());

        return $this->file($file);
    }

    /**
     * @Route("/download/diplome/concours/{id}", name="front_scolarite_download_diplome", methods={"GET"})
     * @param \App\Entity\ConcoursCandidature $concoursCandidature
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadDiploma(ConcoursCandidature $concoursCandidature)
    {
        $uploadDir = $this->getParameter('concours_directory');
        $file = new File($uploadDir . '/' . $concoursCandidature->getDiplomePath());

        return $this->file($file);
    }

     /**
     * @Route("/download/photos/concours/{id}", name="front_scolarite_download_photo", methods={"GET"})
     * @param \App\Entity\ConcoursCandidature $concoursCandidature
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadPhoto(ConcoursCandidature $concoursCandidature)
    {
        $uploadDir = $this->getParameter('concours_directory');
        $file = new File($uploadDir . '/' . $concoursCandidature->getPhoto());

        return $this->file($file);
    }

    /**
     * @Route("/scolarite/concours/emploie-du-temps", name="front_scolarite_concours_edt", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\ConcoursEmploiDuTempsManager $manager
     * @param \App\Manager\MentionManager               $mentionManager
     * @param \App\Manager\ConcoursManager              $concoursManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function edtList(
        Request $request,
        ConcoursEmploiDuTempsManager $manager,
        MentionManager $mentionManager,
        ConcoursManager $concoursManager
    ) {
        $selectedM = $request->get('m');
        $selectedC = $request->get('c');

        $mentions = $mentionManager->loadBy(['active' => 1], ['nom' => 'ASC']);

        $concours = $concoursManager->loadBy([], ['libelle' => 'ASC']);

        $emploiDuTemps = [];
        /** @var \App\Entity\Concours $selectedConcours */
        if ($selectedC
            && $selectedM
        ) {
            $emploiDuTemps = $manager->getByConcoursIdAndMentionId($selectedC, $selectedM);
        }

        return $this->render(
            'frontend/scolarite/concours-edt.html.twig',
            [
                'm'             => $selectedM,
                'c'             => $selectedC,
                'mentions'      => $mentions,
                'concours'      => $concours,
                'emploiDuTemps' => $emploiDuTemps,
            ]
        );
    }

    /**
     * @Route("/scolarite/concours/emploie-du-temps/ajax-list", name="front_scolarite_concours_edt_ajax_list", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\MentionManager               $mentionManager
     * @param \App\Manager\ConcoursManager              $concoursManager
     * @param \App\Manager\ConcoursEmploiDuTempsManager $manager
     *
     * @return string
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function ajaxGetList(
        Request $request,
        MentionManager $mentionManager,
        ConcoursManager $concoursManager,
        ConcoursEmploiDuTempsManager $manager
    ) {

        $emploiDuTemps = [];

        $mention  = $mentionManager->loadOneBy(["id" => $request->query->getInt('mentionId')]);
        $concours = $concoursManager->loadOneBy(["id" => $request->query->getInt('concoursId')]);
        if ($mention && $concours) {
            $emploiDuTemps = $manager->getByConcoursIdAndMentionId($concours->getId(), $mention->getId());
        }

        return $this->render(
            'frontend/concours/_concours_edt_list.html.twig',
            [
                'emploiDuTemps' => $emploiDuTemps
            ]
        );
    }

    /**
     * @Route("/scolarite/concours/emploie-du-temps/nouveau", name="front_scolarite_concours_edt_add", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\ConcoursEmploiDuTempsManager $concoursEmploiDuTempsManager
     * @param \App\Manager\MentionManager               $mentionManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function edtAdd(
        Request $request,
        ConcoursEmploiDuTempsManager $concoursEmploiDuTempsManager,
        MentionManager $mentionManager,
        AnneeUniversitaireService   $anneeUniversitaireService
    ) {
        /** @var \App\Entity\ConcoursEmploiDuTemps $emploiDuTemps */
        $emploiDuTemps = $concoursEmploiDuTempsManager->createObject();
        $form          = $this->createForm(
            ConcoursEmploiDuTempsType::class,
            $emploiDuTemps
        );

        // handle form submit
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \App\Entity\ConcoursEmploiDuTemps $postedData */
            $postedData = $emploiDuTemps;

            $currentCollegeYear = $anneeUniversitaireService->getCurrent();

            $emploiDuTempsCheck = $concoursEmploiDuTempsManager->loadOneBy(
                [
                    'concours' => $emploiDuTemps->getConcours(),
                    'concoursMatiere' => $emploiDuTemps->getConcoursMatiere(),
                    'anneeUniversitaire' => $currentCollegeYear
                ]
            );
            if ($emploiDuTempsCheck) {
                $emploiDuTemps = $emploiDuTempsCheck;
            } else {
                $emploiDuTemps->setAnneeUniversitaire($currentCollegeYear);
            }
            $emploiDuTemps->setConcours($postedData->getConcours());
            $emploiDuTemps->setConcoursMatiere($postedData->getConcoursMatiere());
            $emploiDuTemps->setSalle($postedData->getSalle());
            $emploiDuTemps->setStartDate($postedData->getStartDate());
            $emploiDuTemps->setEndDate($postedData->getEndDate());

            $concoursEmploiDuTempsManager->save($emploiDuTemps);

            return $this->redirectToRoute('front_scolarite_concours_edt');
        }

        $mentions = $mentionManager->loadBy(['active' => 1], ['nom' => 'ASC']);

        return $this->render(
            'frontend/scolarite/concours-edtadd.html.twig',
            [
                'mentions' => $mentions,
                'form'     => $form->createView()
            ]
        );
    }

    /**
     * @Route("/scolarite/concours/emploie-du-temps/ajax-m", name="front_scolarite_concours_edt_ajax_matiere", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\MentionManager               $mentionManager
     * @param \App\Manager\ConcoursManager              $concoursManager
     * @param \App\Manager\ConcoursMatiereManager       $concoursMatiereManager
     *
     * @return string
     */
    public function ajaxGetMatiereOptions(
        Request $request,
        MentionManager $mentionManager,
        ConcoursManager $concoursManager,
        ConcoursMatiereManager $concoursMatiereManager,
        ParcoursManager $parcoursManager
    ) {

        $mention  = $mentionManager->loadOneBy(["id" => $request->query->getInt('mentionId')]);
        $concours = $concoursManager->loadOneBy(["id" => $request->query->getInt('concoursId')]);
        $parcours = $parcoursManager->loadOneBy(["id" => $request->query->get('parcoursId')]);
        $concoursMatiere = $concoursMatiereManager->loadBy(
            [
                'mention' => $mention,
                'concours' => $concours,
                'parcours' => $parcours
            ],
            [
                'libelle' => 'ASC'
            ]
        );

        return $this->render(
            'frontend/concours/_concours_matiere_options.html.twig',
            [
                'matieres' => $concoursMatiere
            ]
        );
    }

    /**
     * @Route("/scolarite/concours/emploie-du-temps/ajax-c", name="front_scolarite_concours_edt_ajax_concours", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\MentionManager               $mentionManager
     * @param \App\Manager\ConcoursManager              $concoursManager
     * @param \App\Manager\ParcoursManager              $parcoursManager
     * @param \App\Manager\ConcoursConfigManager        $concoursConfManager
     *
     * @return string
     */
    public function ajaxGetConcoursOptions(
        Request $request,
        MentionManager $mentionManager,
        ConcoursManager $concoursManager,
        ParcoursManager $parcoursManager,
        ConcoursConfigManager $concoursConfManager
    ) {
        $concoursConf = $concoursConfManager->loadOneBy([]);
        $currentAnneUniv= $concoursConf->getAnneeUniversitaire();
        $mention  = $mentionManager->loadOneBy(["id" => $request->query->getInt('mentionId')]);
        $parcours = $parcoursManager->loadOneBy(["id" => $request->query->get('parcoursId')]);
        $concours = $concoursManager->loadBy(
            [
                'mention'  => $mention,
                'parcours' => $parcours,
                'deletedAt' => NULL,
                'annee_universitaire' => $currentAnneUniv
            ],
            [
                'libelle' => 'ASC'
            ]
        );

        return $this->render(
            'frontend/concours/_concours_concours_options.html.twig',
            [
                'concours' => $concours,
            ]
        );
    }

    /**
     * @Route("/scolarite/concours/emploie-du-temps/ajax-p", name="front_scolarite_concours_edt_ajax_parcours", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\MentionManager               $mentionManager
     * @param \App\Manager\ParcoursManager              $parcoursManager
     *
     * @return string
     */
    public function ajaxGetParcoursOptions(
        Request $request,
        MentionManager $mentionManager,
        ParcoursManager $parcoursManager
    ) {

        $mentionId  = $request->query->getInt('mentionId', 0);
        $niveauId     = $request->query->getInt('niveauId', 0);
        $tFilters   = [];
        $tFilters['mention'] = $mentionId;
        if($niveauId)
            $tFilters['niveau'] = $niveauId;
        $parcours   = $parcoursManager->loadBy(
            $tFilters,
            [
                'nom' => 'ASC'
            ]
        );

        return $this->render(
            'frontend/concours/_concours_parcours_options.html.twig',
            [
                'parcours' => $parcours,
            ]
        );
    }

    /**
     * @Route("/scolarite/presence-enseignant/list", name="front_scolarite_presence_enseignant_index", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Repository\FichePresenceEnseignantRepository $presenceEnseignantRepo
     * @param \App\Service\AnneeUniversitaireService            $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexPresenceEnseignant(
        Request                         $request,
        FichePresenceEnseignantManager  $ficheEnseignantManager,
        AnneeUniversitaireService       $anneeUniversitaireService)
    {
        $currentAnneUniv        = $anneeUniversitaireService->getCurrent();
        $fPresenceEnseignants   = $ficheEnseignantManager->loadBy(
            [
                'statut'                => FichePresenceEnseignant::STATUS_CREATED,
                'anneeUniversitaire'    => $currentAnneUniv
            ]
        );
        return $this->render(
            'frontend/scolarite/presence_enseignant/list.html.twig',
            [
                'fPresenceEnseignants' => $fPresenceEnseignants
            ]
        );
    }

    /**
     * @Route("/scolarite/presence-enseignant/add", name="front_scolarite_presence_enseignant_add", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newPresenceEnseignant(Request $request, FichePresenceEnseignantManager $ficheEnseignantManager, MatiereManager $matiereManager, AnneeUniversitaireService   $anneeUniversitaireService, FichePresenceEnseignantHistoriqueManager $historiqueManager)
    {
        $fpEnseignant = new FichePresenceEnseignant();
        $form   = $this->createForm(
            FichePresenceEnseignantType::class,
            $fpEnseignant
        );
        $bDuplicateError = false;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $matiere = $matiereManager->load($fpEnseignant->getMatiere());
            $enseignant = $matiere->getEnseignant();
            //check duplicate entry for enseignant 

            $duplicateFichePresence = $ficheEnseignantManager->checkExistingEntry(
                $enseignant->getId(), 
                $fpEnseignant->getDate(),
                $fpEnseignant->getStartTime(),
                $fpEnseignant->getEndTime()
            );

            if(count($duplicateFichePresence) > 0) {
                $bDuplicateError = true;
                return $this->render(
                'frontend/scolarite/presence_enseignant/add.html.twig',
                    [
                        'form'          => $form->createView(),
                        'fpEnseignant'  => $fpEnseignant,
                        'duplicateEntryError' => $bDuplicateError
                    ]
                );
            }

            $fpEnseignant->setCreatedAt(new \DateTime());
            $fpEnseignant->setAnneeUniversitaire($anneeUniversitaireService->getCurrent());
            $fpEnseignant->setStatut(FichePresenceEnseignant::STATUS_CREATED);

            $fpEnseignant->setEnseignant($enseignant);
            $ficheEnseignantManager->save($fpEnseignant);

            /** @var \App\Entity\FichePresenceEnseignantHistorique $historical */
            $historical = $historiqueManager->createObject();
            $historical->setUser($this->getUser());
            $historical->setFichePresenceEnseignant($fpEnseignant);
            $historical->setStatus(FichePresenceEnseignant::STATUS_CREATED);
            $historiqueManager->save($historical);
            return $this->redirectToRoute('front_scolarite_presence_enseignant_index');
        }
        
        return $this->render(
            'frontend/scolarite/presence_enseignant/add.html.twig',
            [
                'form'          => $form->createView(),
                'fpEnseignant'  => $fpEnseignant,
                'duplicateEntryError' => $bDuplicateError
            ]
        );
    }

    /**
     * @Route("/scolarite/presence-enseignant/ajax/options", name="front_scolarite_presence_enseignant_ajax_options", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajaxOptionsPresenceEnseignant(
        Request $request,
        MentionManager $mentionManager,
        ParcoursManager $parcoursManager,
        UniteEnseignementsManager $ueManager,
        MatiereManager $matiereManager
    )
    {
        $parentName = $request->get('parent_name');
        $parentValue = $request->get('parent_id');
        $childTarget = $request->get('child_target');
        $mentions = [];
        $parcours = [];
        $uniteEnseignements = [];
        $matieres = [];
        switch($parentName) {
            case 'departement':
                $mentions = $mentionManager->loadBy([$parentName => $parentValue]);
                break;
            // case 'mention':
            //     $parcours = $parcoursManager->loadBy([$parentName => $parentValue]);
            //     break;
            case 'niveau':
                $filterOptions = [];
                $mentionId  = $request->get('mention_id');
                $filterOptions['mention'] = $mentionId;
                $filterOptions[$parentName] = $parentValue;
                if($childTarget === 'parcours') {
                    $parcours   = $parcoursManager->loadBy($filterOptions);
                }
                if($childTarget === 'unite_enseignement') {
                    $parcoursId = $request->get('parcours_id');
                    $filterOptions['parcours'] = null;
                    if(!empty($parcoursId)) {
                        $filterOptions['parcours'] = $parcoursId;
                    }
                    $uniteEnseignements   = $ueManager->loadBy($filterOptions);
                }
                break;
            case 'uniteEnseignements':
                $matieres = $matiereManager->loadBy([$parentName => $parentValue]);
                break;
             case 'parcours':
                $uniteEnseignements = $ueManager->loadBy([$parentName => $parentValue]);
                break;
            default:
                break;
        }

        return $this->render(
            'frontend/scolarite/presence_enseignant/_ajax_options.html.twig',
            [
                'mentions'              => $mentions,
                'parcours'              => $parcours,
                'uniteEnseignements'    => $uniteEnseignements,
                'matieres'              => $matieres
            ]
        );        
        
    }

    /**
     * @Route("/scolarite/presence-enseignant/edit/{id}", name="front_scolarite_presence_enseignant_edit", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\FichePresenceEnseignant $fpEnseignant
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updatePresenceEnseignant(Request $request, FichePresenceEnseignant $fpEnseignant)
    {
        return $this->render(
            'frontend/scolarite/presence_enseignant/edit.html.twig',
            [
                'fpEnseignant' => $fpEnseignant
            ]
        );
    }

    /**
     * @Route("/scolarite/absence/list", name="front_scolarite_absence_index", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Repository\AbsencesManager $absManager
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAbsence(
            Request $request, 
            AbsencesManager $absManager,
            MentionManager $mentionManager,
            NiveauManager $niveauManager,
            ParcoursManager $parcoursManager,
            AnneeUniversitaireService   $anneeUniversitaireService,
            PaginatorInterface $paginator
        )
    {        
        $currentAnneUniv = $anneeUniversitaireService->getCurrent();
        $m = $request->get('m', 0);
        $n = $request->get('n', 0);
        $p = $request->get('p');
        $page = $request->query->getInt('page', 1);
        $mentions       = $mentionManager->loadBy([], ['nom' => 'ASC']);
        $niveaux        = $niveauManager->loadBy([], ['libelle' => 'ASC']);
        $parcours       = $parcoursManager->loadBy(
            [
                'mention' => $m,
                'niveau'  => $n
            ], 
            ['nom' => 'ASC']);
        $options = ['mention' => $m, 'niveau' => $n, 'parcours' => $p];
        $absences = $absManager->getAbsenceParMatiere($currentAnneUniv->getId(), $options);
        $pagination = $paginator->paginate(
            $absences, // Vos données à paginer
            $page, // Numéro de la page
            $itemsPerPage=20 // Nombre d'éléments par page
        );

        return $this->render(
            'frontend/scolarite/absences/list.html.twig',
            [
                'm'                     => $m,
                'n'                     => $n,
                'p'                     => $p,
                'mentions'              => $mentions,
                'niveaux'               => $niveaux,
                'parcours'              => $parcours,
                'absences' => $pagination
            ]
        );
    }

    /**
     * @Route("/scolarite/absence/add", name="front_scolarite_absence_add", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\AbsencesManager              $absManager
     * @param \App\Manager\EtudiantManager              $etudiantManager
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function newAbsence(Request $request, AbsencesManager $absManager, EtudiantManager $etudiantManager, AnneeUniversitaireService $anneeUniversitaireService, EmploiDuTempsManager $edtManager)
    {
        $absence = new Absences();
        $form   = $this->createForm(
            AbsenceType::class,
            $absence
        );
        $absParams = $request->get('absence');
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // dd($form->getErrors(true));die;
            $absence->setCreatedAt(new \DateTime());
            $absence->setUpdatedAt(new \DateTime());
            $absence->setAnneeUniversitaire($anneeUniversitaireService->getCurrent());
            $edtId = $absParams['emploi_du_temps'];
            $edt = $edtManager->load($edtId);
            $absence->setEmploiDuTemps($edt);
            foreach($absParams['etudiant'] as $etudiantId){
                $currAbs = clone($absence);
                $etudiant = $etudiantManager->load($etudiantId);
                $currAbs->setEtudiant($etudiant);
                $absManager->persist($currAbs);
                $absManager->save($currAbs);
            }

            return $this->redirectToRoute('front_scolarite_absence_index');
        }
        
        return $this->render(
            'frontend/scolarite/absences/add.html.twig',
            [
                'form'      => $form->createView(),
                'absence'   => $absence
            ]
        );
    }

    /**
     * @Route("/scolarite/absence/{id}/edit", name="front_scolarite_absence_edit", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\AbsencesManager              $absManager
     * @param \App\Manager\EtudiantManager              $etudiantManager
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function editAbsence(Absences $absence, Request $request, AbsencesManager $absManager, EtudiantManager $etudiantManager, AnneeUniversitaireService $anneeUniversitaireService, EmploiDuTempsManager $edtManager, ParameterBagInterface $parameter)
    {
        $form   = $this->createForm(
            AbsenceType::class,
            $absence
        );
        $absParams = $request->get('absence');
        $edtFilters = [
            'mention' => $absence->getMention(),
            'niveau' => $absence->getNiveau(),
            'dateSchedule' => $absence->getDate()
        ];
        $edtFilters['parcours'] = $absence->getParcours() ? $absence->getParcours() : null;
        $edts = $edtManager->loadby($edtFilters, []);
        $form->handleRequest($request);
        $currentAnneUniv = $anneeUniversitaireService->getCurrent();
        if ($form->isSubmitted() && $form->isValid()) {
            $absence->setUpdatedAt(new \DateTime());

            $justification = $form->get('justification')->getData();
            $directory     = $parameter->get('students_absence_scan');
            if(!is_dir($directory)){
                 try {
                    $filesystem = new Filesystem();
                    $filesystem->mkdir($directory);
                 } catch (IOExceptionInterface $exception) {
                    echo "An error occurred while creating your directory at ".$exception->getPath();
                }
            }
            $uploader      = new \App\Services\FileUploader($directory);
            
            $fileDirectory = $currentAnneUniv->getAnnee() . '/' . $absence->getMention()->getDiminutif() . '/' . $absence->getNiveau()->getCode() . '/' . $absence->getEtudiant()->getId();
            // Upload file
            if ($justification) {
                $justFileDisplay = $uploader->upload($justification, $directory, $fileDirectory, false);
                $absence->setJustification($fileDirectory . "/" . $justFileDisplay["filename"]);
            }

            $absManager->save($absence);

            return $this->redirectToRoute('front_scolarite_absence_index');
        }
        
        return $this->render(
            'frontend/scolarite/absences/edit.html.twig',
            [
                'form'      => $form->createView(),
                'absence'   => $absence,
                'edts' => $edts
            ]
        );
    }

    /**
     * @Route("/download/absence/{id}/justification", name="front_scolarite_download_absence_justification", methods={"GET"})
     *
     * @param \App\Entity\Inscription $inscription
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadAbsenceJustification(Absences $absence)
    {
        $uploadDir = $this->getParameter('students_absence_scan');
        $file = new File($uploadDir . '/' . $absence->getJustification());

        return $this->file($file);
    }

    /**
     * @Route("/scolarite/absence/etudiant/list", name="front_scolarite_absence_etudiant_list", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajaxAbsEtudiantList(
        Request $request,
        EtudiantRepository $etudiantRepo,
        AnneeUniversitaireService $anneeUniversitaireService
    )
    {
        $mentionId  = $request->get('mention_id');
        $parcoursId = $request->get('parcours_id');
        $niveauId   = $request->get('niveau_id');
        $displayType = $request->get('display_type');
        $collegeYear = $anneeUniversitaireService->getCurrent();
        $etudiants = [];
        $filterOptions = [];
        $filterOptions['mention']   = $mentionId;
        $filterOptions['niveau']    = $niveauId;
        $etudiants   = $etudiantRepo->getInscritPerClass($mentionId, $niveauId, $parcoursId, $collegeYear->getId());
                
        return $this->render(
            'frontend/scolarite/absences/_ajax_etudiant_list.html.twig',
            [
                'etudiants' => $etudiants,
                'displayType' => $displayType
            ]
        );       
    }

    /**
     * @Route("/scolarite/absence/edt/list", name="front_scolarite_absence_edt_list", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajaxAbsEdtList(
        Request $request,
        EtudiantRepository $etudiantRepo,
        AnneeUniversitaireService $anneeUniversitaireService,
        EmploiDuTempsManager $edtManager
    )
    {
        $mentionId  = $request->get('mention_id');
        $parcoursId = $request->get('parcours_id');
        $niveauId   = $request->get('niveau_id');
        $date = $request->get('date');

        $collegeYear = $anneeUniversitaireService->getCurrent();
        $etudiants = [];
        $filterOptions = [];
        $filterOptions['mention']   = $mentionId;
        $filterOptions['niveau']    = $niveauId;
        $edt   = $edtManager->getListActive($mentionId, EmploiDuTemps::STATUS_CREATED, null, $niveauId, null, $date);

        return $this->render(
            'frontend/scolarite/absences/_ajax_edt_options.html.twig',
            [
                'list' => $edt
            ]
        );       
    }

    /**
     * @Route("/scolarite/frais-scolarite/etudiant/list", name="front_scolarite_fs_etudiant_list", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajaxFsEtudiantList(
        Request $request,
        EtudiantRepository $etudiantRepo,
        AnneeUniversitaireService $anneeUniversitaireService
    )
    {
        $mentionId  = $request->get('mention_id');
        $parcoursId = $request->get('parcours_id');
        $niveauId   = $request->get('niveau_id');
        $displayType = $request->get('display_type');
        $collegeYear = $anneeUniversitaireService->getCurrent();
        $etudiants = [];
        $filterOptions = [];
        $filterOptions['mention']   = $mentionId;
        $filterOptions['niveau']    = $niveauId;
        $etudiants   = $etudiantRepo->getPerClass($mentionId, $niveauId, $parcoursId, $collegeYear->getId());
                
        return $this->render(
            'frontend/scolarite/frais-scolarite/_ajax_etudiant_list.html.twig',
            [
                'etudiants' => $etudiants,
                'displayType' => $displayType
            ]
        );       
    }

    // ETAT D'absence -----------------------------------------------------

    /**
     * @Route("/absences/etat", name="front_scol_absence_etat", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request   $request
     * @param \App\Services\AnneeUniversitaireService     $anneeUniversitaireService
     * @param \App\Manager\AbsencesManager                $absenceManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @IsGranted({"ROLE_SCOLARITE", "ROLE_SSRS"})
     */
    public function etatAbsencesParEtudiant(
        Request                         $request, 
        MentionManager                  $mentionManager,
        NiveauManager                   $niveauManager,
        ParcoursManager                 $parcoursManager,
        SemestreManager                 $semManager,
        MatiereManager                  $matManager,
        AnneeUniversitaireService       $anneeUniversitaireService,
        AbsencesManager                 $absencesManager)
    {
        $user = $this->getUser();
        $m = $request->get('m', 0);
        $n = $request->get('n', 0);
        $p = $request->get('p');
        $s = $request->get('s');
        $mt = $request->get('mt');
        $mentions       = $mentionManager->loadBy([], ['nom' => 'ASC']);
        $niveaux        = $niveauManager->loadBy([], ['libelle' => 'ASC']);
        $parcours       = $parcoursManager->loadBy(
            [
                'mention' => $m,
                'niveau'  => $n
            ], 
            ['nom' => 'ASC']);
        $p = count($parcours) > 0 ? $p : null;
        $options = ['mention' => $m, 'niveau' => $n, 'parcours' => $p, 'semestre' => $s, 'matiere' => $mt];
        //dd($options);die;
        $semestres = $semManager->loadBy(['niveau' => $n], ['libelle' => 'ASC']);
        $matieres = $matManager->getAllByFilters($options);        
        $currentAnneUniv    = $anneeUniversitaireService->getCurrent();
        $absences = $absencesManager->getPerClass_scol($currentAnneUniv->getId(), $options);
         //dump($absences);die;
        $spreadsheet = new Spreadsheet();

        return $this->render(
            'frontend/scolarite/absences/etat.html.twig',
            [
                'm'                     => $m,
                'n'                     => $n,
                'p'                     => $p,
                's'                     => $s,
                'mt'                    => $mt,
                'mentions'              => $mentions,
                'niveaux'               => $niveaux,
                'parcours'              => $parcours,
                'list'                  => $absences,
                'matieres'              => $matieres,
                'semestres'             => $semestres
            ]
        );
    }
    
    /**
     * @Route("/absences/etat/export", name="export_absences_excel", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request   $request
     * @param \App\Services\AnneeUniversitaireService     $anneeUniversitaireService
     * @param \App\Manager\AbsencesManager                $absenceManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @IsGranted({"ROLE_SCOLARITE", "ROLE_SSRS"})
     */
    public function exportEtatAbsencesParEtudiant(
        Request                         $request, 
        MentionManager                  $mentionManager,
        NiveauManager                   $niveauManager,
        ParcoursManager                 $parcoursManager,
        SemestreManager                 $semManager,
        MatiereManager                  $matManager,
        AnneeUniversitaireService       $anneeUniversitaireService,
        AbsencesManager                 $absencesManager)
    {
        $user = $this->getUser();
        $m = $request->get('m', 0);
        $n = $request->get('n', 0);
        $p = $request->get('p');
        $s = $request->get('s');
        $mt = $request->get('mt');
        $mentions       = $mentionManager->loadBy([], ['nom' => 'ASC']);
        $niveaux        = $niveauManager->loadBy([], ['libelle' => 'ASC']);
        $parcours       = $parcoursManager->loadBy(
            [
                'mention' => $m,
                'niveau'  => $n
            ], 
            ['nom' => 'ASC']);
        $p = count($parcours) > 0 ? $p : null;
        $options = ['mention' => $m, 'niveau' => $n, 'parcours' => $p, 'semestre' => $s, 'matiere' => $mt];
        $semestres = $semManager->loadBy(['niveau' => $n], ['libelle' => 'ASC']);
        $matieres = $matManager->getAllByFilters($options);
        $currentAnneUniv    = $anneeUniversitaireService->getCurrent();
        $absences = $absencesManager->getPerClass_scol($currentAnneUniv->getId(), $options);
        // dump($absences);die;
        $spreadsheet = new Spreadsheet();

        // Sélectionner la feuille de calcul active
        $sheet = $spreadsheet->getActiveSheet();

        // En-têtes de colonnes pour le fichier Excel
        $sheet->setCellValue('A1', 'Etudiant');
        $sheet->setCellValue('B1', 'Absence');
        $sheet->setCellValue('C1', 'Absence Justifiée');

        // Récupérer les données des absences et les ajouter au fichier Excel
        $row = 2; // Commencer à la ligne 2 après les en-têtes
        foreach ($absences as $item) {
            $sheet->setCellValue('A' . $row, $item['last_name'] . ' ' . $item['first_name']);
            $sheet->setCellValue('B' . $row, $item['nbrAbsence']);
            $sheet->setCellValue('C' . $row, $item['nbrJustifiedAbsence']);
            // Ajoutez d'autres colonnes si nécessaire et ajustez les lettres de colonnes (A, B, C, etc.)
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $mentionId = $request->get('m', 0);
        $mentions = $mentionManager->loadBy([], ['nom' => 'ASC']);

        $mentionName = null;
        foreach ($mentions as $mention) {
            if ($mention->getId() == $mentionId) {
                $mentionName = $mention->getNom();
                break;
            }
        }
        $niveauId = $request->get('n', 0);
        $niveaux = $niveauManager->loadBy([], ['libelle' => 'ASC']);

        $niveauName = null;
        foreach ($niveaux as $niveau) {
            if ($niveau->getId() == $niveauId) {
                $niveauName = $niveau->getLibelle();
                break;
            }
        }
        $excelFileName = 'export_absences_' . $mentionName .'_' . $niveauName . '.xlsx';
        // Je vais le renvoyer directement au navigateur pour le téléchargement
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $excelFileName . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
        return $this->render(
           
        );
    }

    /**
     * @Route("/scolarite/inscription", name="front_scolarite_inscription", methods={"GET"})
     *
     * @param \App\Manager\InscriptionManager           $manager
     * @param \App\Manager\AnneeUniversitaireService           $anneeUniversitaireService
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listInscription(Request $request,InscriptionManager $manager, AnneeUniversitaireService $anneeUniversitaireService,PaginatorInterface $paginator) : Response
    {
        $currentAnneUniv = $anneeUniversitaireService->getCurrent();
        $inscriptions = $manager->loadBy(
            [
                'anneeUniversitaire' => $currentAnneUniv,
                'status' => Inscription::STATUS_CREATED
            ]
        );
        $page = $request->query->getInt('page', 1);
        $pagination = $paginator->paginate(
            $inscriptions, // Vos données à paginer
            $page, // Numéro de la page
            $itemsPerPage=20 // Nombre d'éléments par page
        );

        return $this->render(
            'frontend/scolarite/inscription.html.twig',
            [
                'inscriptions' => $inscriptions,
                'pagination' => $pagination,
            ]
        );
    }

    /**
     * @Route("/scolarite/inscrits", name="front_scolarite_inscrits", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\InscriptionManager           $manager
     * @param \App\Manager\MentionManager               $mentionManager
     * @param \App\Manager\NiveauManager                $niveauManager
     * @param \App\Manager\ParcoursManager              $parcoursManager
     * @param \App\Manager\AnneeUniversitaireManager    $anneeUniversitaireManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listInscrits(
        Request $request,
        InscriptionManager $manager,
        MentionManager $mentionManager,
        NiveauManager $niveauManager,
        ParcoursManager $parcoursManager,
        AnneeUniversitaireManager $anneeUniversitaireManager,
        AnneeUniversitaireService $anneeUniversitaireService,
        ExportDataService $exportService,
        ParameterBagInterface $parameter
    ) : Response {
        $defaultAnneeUniv = $anneeUniversitaireService->getCurrent();
        $selectedA = $request->get('a', $defaultAnneeUniv ? $defaultAnneeUniv->getId() : 0);
        $selectedM = $request->get('m');
        $selectedN = $request->get('n');
        $selectedP = $request->get('p');
        $actionType = $request->get('act', '');

        $currentAnneUniv = $anneeUniversitaireManager->load($selectedA);

        $selectedMention = '';
        if ($selectedM && $mention = $mentionManager->load((int) $selectedM)) {
            $selectedMention = $mention->getNom();
        }

        $selectedNiveau = '';
        if ($selectedN && $niveau = $niveauManager->load((int) $selectedN)) {
            $selectedNiveau = $niveau->getLibelle();
        }

        $mentions = $mentionManager->loadBy(['active' => 1], ['nom' => 'ASC']);
        $anneUniv = $anneeUniversitaireManager->loadBy([], ['annee' => 'DESC']);
        $niveaux  = $niveauManager->loadBy([], ['libelle' => 'ASC']);
        $parcours  = $parcoursManager->loadBy(
            [
                'mention' => $selectedM,
                'niveau' => $selectedN
            ], 
            ['nom' => 'ASC']
        );

        $inscrits = $manager->getByCollegeYearAndMentionAndLevel(
            (int) $selectedA, (int) $selectedM, (int) $selectedN, (int) $selectedP
        );
        
        if($actionType == 'export') {
            $inscriptionFile = $exportService->getEtudiantInscrit($inscrits, $currentAnneUniv, $parameter);
            return $this->file($inscriptionFile);
        }

        return $this->render(
            'frontend/scolarite/inscrits.html.twig',
            [
                'a'               => $selectedA,
                'm'               => $selectedM,
                'n'               => $selectedN,
                'p'               => $selectedP,
                'selectedMention' => $selectedMention,
                'selectedNiveau'  => $selectedNiveau,
                'anneeUniv'       => $anneUniv,
                'mentions'        => $mentions,
                'niveaux'         => $niveaux,
                'parcours'        => $parcours,
                'inscrits'        => $inscrits,
            ]
        );
    }

    
    /**
     * @Route("/scolarite/etudiants", name="listes_etudiants", methods={"GET"})
     * @param \App\Manager\etudiantManager $studentManager
     * @param \App\Services\AnneeUniversitaireService     $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
   /* public function listEtudiants(
        Request                         $request,
        EtudiantManager                 $studentManager,
        MentionManager                  $mentionManager,
        NiveauManager                   $niveauManager,
        ParcoursManager                 $parcoursManager,
        AnneeUniversitaireService       $anneeUniversitaireService
    ) {
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
    }*/

    /**
     * @Route("/scolarite/inscription/{id}", name="front_scolarite_detail_inscription", methods={"GET"})
     * @param \App\Entity\Inscription $inscription
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function detailInscription(Inscription $inscription)
    {
        return $this->render(
            'frontend/scolarite/inscription-detail.html.twig',
            [
                'inscription' => $inscription
            ]
        );
    }

    /**
     * @Route("/download/paiement/inscription/{id}", name="front_scolarite_download_inscription_paiement_ref", methods={"GET"})
     *
     * @param \App\Entity\Inscription $inscription
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadInscriptionPayment(Inscription $inscription)
    {
        $uploadDir = $this->getParameter('students_directory');
        $file = new File($uploadDir . '/' . $inscription->getPayementRefPath());

        return $this->file($file);
    }

    /**
     * @Route("/scolarite/valisation-inscriptioin/{id}", name="front_scolarite_validate_inscription", methods={"POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Inscription                   $inscription
     * @param \App\Manager\InscriptionManager           $manager
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function traitementInscription(
        Request $request,
        Inscription $inscription,
        InscriptionManager $manager
    ) {

        $status = $request->get('inscription_status');
        $motif  = $request->get('motif');
        $inscription->setStatus($status);

        $updateStudent = false;

        // send mail notifications
        $this->setSendTo($inscription->getEtudiant()->getEmail());
        $this->setEmailTpl('frontend/etudiant/email-validation-inscription.html.twig');
        $emailTplParams = [
            'fullname' => $inscription->getEtudiant()->getFirstName() . ' ' . $inscription->getEtudiant()->getLastName(),
        ];
        if ($status == Inscription::STATUS_REFUSED) { // refused
            $emailTplParams['message'] = 'Votre inscription a été refusée pour la raison suivante : <br>' . nl2br(
                    $motif
                );
        } else { // accepted
            $emailTplParams['message'] = 'Votre inscription a été bien validée.';
            $updateStudent = true;
        }
        $this->setEmailSubject('Validation inscription');
        $this->setEmailTplParams($emailTplParams);
        //$this->sendMail();

        $manager->save($inscription);

        if ($updateStudent) {
            $student = $inscription->getEtudiant();
            $student->setNiveau($inscription->getNiveau());
            $student->setMention($inscription->getMention());

            $manager->save($student);
        }

        return $this->redirectToRoute('front_scolarite_inscription');
    }

    /**
     * @param \App\Entity\ConcoursCandidature                                       $candidature
     * @param \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface $passwordEncoder
     * @param \App\Manager\RoleManager                                              $roleManager
     * @param \App\Manager\ProfilManager                                            $profilManager
     * @param \App\Manager\UserManager                                              $userManager
     * @param string                                                                $password
     * @param                                                                       $user
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function createUser(
        ConcoursCandidature $candidature,
        UserPasswordEncoderInterface $passwordEncoder,
        RoleManager $roleManager,
        ProfilManager $profilManager,
        UserManager $userManager,
        string $password,
        $user
    )  {
        $roleEtudiant = Roles::ROLE_ETUDIANT;
        $user = !$user ? new User() : $user;
        $user
            ->setFirstname($candidature->getFirstName())
            ->setLastname($candidature->getLastName())
            ->setEmail($candidature->getEmail())
            ->setLogin($candidature->getEmail())
        ;
        $user->setPassword(
            $passwordEncoder->encodePassword(
                $user,
                $password
            )
        );
        $user->setStatus(User::STATUS_ENABLED);

        // check role
        $role = $roleManager->getRepository()->findOneByCode($roleEtudiant);
        if (!$role) {
            $role = $roleManager->create('Etudiant', $roleEtudiant);
        }
        // check profil
        $profil = $profilManager->findOneByRoleCode($roleEtudiant);
        if (!$profil) {
            $profil = $profilManager->create('Etudiant', $role);
        }
        $user->setProfil($profil);
        $userManager->save($user);
    }

    /**
     * @Route("/scolarite/etudiant/list", name="front_scolarite_etudiant_list", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\EtudiantManager                           $etudiantManager
     */

    public function etudiantList(
        Request $request,
        AnneeUniversitaireManager $anneeUniversitaireManager,
        InscriptionManager $inscriptionManager,
        EtudiantManager $etudiantManager,
        MentionManager $mentionManager,
        NiveauManager $niveauManager,
        ParcoursManager $parcoursManager
    ) {
        $anneeUnivId = $request->get('a');
        $mentionId  = $request->get('m');
        $niveauId   = $request->get('n');
        $parcoursId = $request->get('p');

        $selectedMention = '';
        if ($mentionId && $mention = $mentionManager->load((int) $mentionId)) {
            $selectedMention = $mention->getNom();
        }

        $selectedNiveau = '';
        if ($niveauId && $niveau = $niveauManager->load((int) $niveauId)) {
            $selectedNiveau = $niveau->getLibelle();
        }    

        $anneeUniversitaires  = $anneeUniversitaireManager->loadBy([], ['libelle' => 'ASC']);
        $mentions   = $mentionManager->loadBy(['active' => 1], ['nom' => 'ASC']);
        $niveaux    = $niveauManager->loadBy([], ['libelle' => 'ASC']);
        $parcours   = $parcoursManager->loadBy(
            [
                'mention' => $mentionId,
                'niveau' => $niveauId
            ], 
            ['nom' => 'ASC']
        );

        $etudiants  = [];
        if($mentionId && $niveauId) {
            $filterOptions = ['mention' => $mentionId, 'niveau' => $niveauId];
            if(!(empty($parcoursId)))
                $filterOptions['parcours'] = $parcoursId;
            else 
                $filterOptions['parcours'] = null;

            $etudiants = $inscriptionManager->getByCollegeYearAndMentionAndLevel(
            (int) $anneeUnivId, (int) $mentionId, (int) $niveauId, (int) $parcoursId
            );
        }

        return $this->render(
            'frontend/scolarite/etudiant/index.html.twig',
            [
                'a'         => $anneeUnivId,
                'm'         => $mentionId,
                'n'         => $niveauId,
                'p'         => $parcoursId,
                'anneeUniversitaires' => $anneeUniversitaires,
                'mentions'  => $mentions,
                'niveaux'   => $niveaux,
                'parcours'  => $parcours,
                'etudiants' => $etudiants
            ]
        );   
    }

    /**
     * @Route("/scolarite/etudiant/{id}/notes", name="front_scolarite_etudiant_releve", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Etudiant                                                  $etudiant
     * @param \App\Manager\NotesManager                                             $noteManager
     * @param \App\Repository\SemestreManager                                       $semestreManager
     * @param \App\Services\NotesService                                            $noteService   
     */

    public function etudiantNote(
        Etudiant $etudiant,
        NotesManager $noteManager,
        SemestreManager $semestreManager,
        NotesService $noteService
    ) {
        $niveauSemestre1 = null;
        $resultUeSem1 = null;
        $moyenneSem1 = 0;
        $niveauSemestre2 = null;
        $resultUeSem2 = null;
        $moyenneSem2 = 0;
        $moyenneGenerale = null;


        $semestres = $semestreManager->loadBy(['niveau' => $etudiant->getNiveau()]);
        
        if(isset($semestres[0])) {
            $niveauSemestre1 = $semestres[0];
            $etudiantNotesSemestre1 = $noteManager->getEtudiantReleve( $etudiant->getId(), $niveauSemestre1->getId());
            $resultUeSem1 = $noteService->getNotePerTypeUE($etudiantNotesSemestre1);
            $moyenneSem1 = $noteService->getMoyenneUE($resultUeSem1);
        }

        if(isset($semestres[1])) {
            $niveauSemestre2 = $semestres[1];
            $etudiantNotesSemestre2 = $noteManager->getEtudiantReleve( $etudiant->getId(), $niveauSemestre2->getId());
            $resultUeSem2 = $noteService->getNotePerTypeUE($etudiantNotesSemestre2);
            $moyenneSem2 = $noteService->getMoyenneUE($resultUeSem2);
        }

        $moyenneGenerale = round(($moyenneSem1 + $moyenneSem2) / 2, 2, PHP_ROUND_HALF_DOWN);
        
        return $this->render(
            'frontend/scolarite/etudiant/note.html.twig',
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
     * @Route("/scolarite/demandedoc/list", name="front_scolarite_demande_doc_list", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\EtudiantManager                           $etudiantManager
     */
    public function demandeDocList(
        Request $request,
        DemandeDocManager $demandeDocManager,
        EtudiantManager   $etudiantManager,
        DemandeDocRepository $demandeDocRepository
    )
    {
        $currentUser = $this->getUser();
        $demandesDoc = "";
        $demandesDoc   = $demandeDocManager->getDemandeDocTypeDoc();

        return $this->render(
            'frontend/scolarite/demande-doc-list.html.twig',
            [
                'demandesDoc' => $demandesDoc
            ]
        );
    }

    /**
     * @Route("/edit-demande-doc/{id}/edit", name="front_scolarite_demande_doc_edit", methods="GET|POST")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\DemandeDocManager            $demandeDocManager
     * @param \App\Entity\DemandeDoc                    $demandeDoc
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function demandeDocEdit(
        Request             $request,
        DemandeDocManager   $demandeDocManager,
        DemandeDoc          $demandeDoc
    )
    {
        $user           = $this->getUser();

        return $this->render(
            'frontend/scolarite/demande-doc-edit.html.twig',
            [
                'demandeDoc' => $demandeDoc,
            ]
        );

    }

    /**
     * @Route("/edit-demande-doc/status", name="front_scolarite_demande_doc_status", methods="GET|POST")
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
        $user           = $this->getUser();
        $docId          = $request->get('docId');
        $status         = $request->get('status');

        $demandeDoc     = $demandeDocManager->loadOneBy(array("id" => $docId));
        $demandeDoc->setStatut($status);
        $demandeDocManager->persist($demandeDoc);
        $demandeDocManager->flush();

        if($status==="VALIDATED"){
            $this->demandeDocToPdf($request,$demandeDocManager,$demandeDoc);
        }

        return $this->render(
            'frontend/scolarite/demande-doc-list.html.twig',
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

        switch($demandeDoc->getType()->getCode()) {
            case DemandeDoc::TYPE_RELEVE_NOTE:
                $tPl = 'frontend/demandedoc/releve_note.html.twig';
                break;
            case DemandeDoc::TYPE_DIPLOME:
                $tPl = 'frontend/demandedoc/diplome.html.twig';
                break;
            case DemandeDoc::TYPE_CERTIFICAT_DE_SCOLARITE:
                $tPl = 'frontend/demandedoc/certificat_scolarite.html.twig';
                break;
            default:
                break;
        }

        $html = $this->renderView(
            $tPl,
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
     * @IsGranted("ROLE_SCOLARITE")
     * @Route("/extra-note", name="front_scolarite_extranote_list", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\ExtraNotesManager            $notesManager
     * @param \App\Manager\MentionManager               $mentionManager
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     * @param \App\Manager\EtudiantManager              $studentManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function extraNoteList(Request $request, ExtraNotesManager $notesManager, MentionManager $mentionManager, AnneeUniversitaireService $anneeUniversitaireService, EtudiantManager $studentManager)
    {
        $selectedM   = $request->get('m', 0);
        $collegeYear = $anneeUniversitaireService->getCurrent();
        $mentions    = $mentionManager->getByInscriptionAndCollegeYear($collegeYear);
        $students    = $studentManager->getByMentionAndCollegeYear($mentionManager->getReference($selectedM), $collegeYear);
        $notes       = $notesManager->getByStudentsAndCollegeYear($students, $collegeYear);

        return $this->render(
            'frontend/scolarite/extranote-list.html.twig',
            [
                'm'        => (int) $selectedM,
                'mentions' => $mentions,
                'notes'    => $notes
            ]
        );
    }

    /**
     * @IsGranted("ROLE_SCOLARITE")
     * @Route("/extra-note/{id}/ajout", name="front_scolarite_extranote_new", methods={"GET", "POST"})
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

            return $this->redirectToRoute('front_scolarite_extranote_list', ['m' => $mention->getId()]);
        }

        return $this->render(
            'frontend/scolarite/extranote-new.html.twig',
            [
                'form'  => $form->createView(),
                'notes' => $notes
            ]
        );
    }

    /**
     * @IsGranted("ROLE_SCOLARITE")
     * @Route("/frais/new/", name="front_scolarite_frais_new", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\SemestreManager              $semestreManager
     * @param \App\Manager\MentionManager               $mentionManager
     * @param \App\Manager\niveauManager                $niveauManager
     * @param \App\Manager\parcoursManager                $parcoursManager
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function fraisNew(
        Request                     $request, 
        FraisScolariteManager       $fraisScolariteManager,
        FraisScolariteRepository    $fraisScolariteRepo,
        EtudiantRepository          $etudiantRepo,
        EcolageService              $ecoService,
        AnneeUniversitaireService   $anneeUniversitaireService,
        SemestreManager             $semManager)
    {
        $fraisScolarite = $fraisScolariteManager->createObject();
        $form = $this->createForm(
            FraisScolariteType::class,
            $fraisScolarite,
            [
                'fraisScolarite'     => $fraisScolarite,
                'em'       => $this->getDoctrine()->getManager()
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $fraisScolarite->setAuthor($this->getUser());
            $payementRefFile = $form->get('paymentRefPath')->getData();
            $directory     = $this->getParameter('students_ecolage_scan');
            $uploader      = new \App\Services\FileUploader($directory);
            $today         = new \DateTime();
            $fileDirectory = UtilFunctionService::seoFriendlyUrl($fraisScolarite->getEtudiant()->getLastName()) . "-" . $today->getTimestamp(
                );
            $uploadDir = $directory . '/' . $fileDirectory;
            
            // Upload file
            if ($payementRefFile) {
                if(!file_exists($uploadDir)){
                    $filesystem = new Filesystem();
                    $filesystem->mkdir($uploadDir);    
                    try {
                        
                    } catch (IOExceptionInterface $exception) {
                        echo "An error occurred while creating your directory at ".$exception->getPath();
                    }
                }
                $payementFileDisplay = $uploader->upload($payementRefFile, $directory, $fileDirectory, false);
                $fraisScolarite->setPaymentRefPath($fileDirectory . "/" . $payementFileDisplay["filename"]);
            }
            $currentStatus = FraisScolarite::STATUS_SRS_VALIDATED;
            $fraisScolarite->setStatus($currentStatus);

            $fraisScolariteManager->savePerEtudiant($fraisScolarite, $ecoService);                
            //history
            $ecolageHistory = new PaiementHistory();
            $ecolageHistory->setResourceName(PaiementHistory::ECOLAGE_RESOURCE);
            $ecolageHistory->setStatut($currentStatus);
            $ecolageHistory->setResourceId($fraisScolarite->getId());
            $ecolageHistory->setValidator($this->getUser());
            $ecolageHistory->setCreatedAt(new \DateTime());
            $ecolageHistory->setUpdatedAt(new \DateTime());
            $ecolageHistory->setComment("");
            $ecolageHistory->setMontant($fraisScolarite->getMontant());
            $em = $this->getDoctrine()->getManager();
            $em->persist($ecolageHistory);       
            $em->flush();

            return $this->redirectToRoute('front_scolarite_frais_index');
        }

        return $this->render(
            'frontend/scolarite/frais-scolarite/new.html.twig',
            [
                'form'  => $form->createView(),
                'fraisScolarite' => $fraisScolarite
            ]
        );
    
    }

    /**
     * @Route("/scolarite/frais/semestre-options", name="front_scolarite_frais_semestre_ajax_options", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\MentionManager               $mentionManager
     * @param \App\Manager\ConcoursManager              $concoursManager
     * @param \App\Manager\ConcoursEmploiDuTempsManager $manager
     *
     * @return string
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function ajaxSemOptions(
        Request $request,
        SemestreManager $semManager
    ) {
        $semestres = [];
        $semestres  = $semManager->loadBy(['niveau' => $request->query->getInt('niveau_id')]);
        return $this->render(
            'frontend/scolarite/frais-scolarite/_semestre_options.html.twig',
            [
                'semestres' => $semestres
            ]
        );
    }

    /**
     * @IsGranted("ROLE_SCOLARITE")
     * @Route("/frais/index", name="front_scolarite_frais_index", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\SemestreManager              $semestreManager
     * @param \App\Manager\MentionManager               $mentionManager
     * @param \App\Manager\niveauManager                $niveauManager
     * @param \App\Manager\parcoursManager                $parcoursManager
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function fraisIndex(
        Request                         $request, 
        CalendrierPaiementManager       $calPaiementManager,
        CalendrierPaiementRepository    $calPaiementRepo,
        SemestreManager                 $semestreManager, 
        MentionManager                  $mentionManager,
        NiveauManager                   $niveauManager, 
        ParcoursManager                 $parcoursManager,
        AnneeUniversitaireService       $anneeUniversitaireService, 
        FraisScolariteManager           $fraisScolariteManager,
        ExportDataService               $exportService,
        PaginatorInterface $paginator,
        ParameterBagInterface $parameter
        )
    {
        $c = $request->get('c', '');
        $selectedS = $request->get('s', 0);
        $selectedM = $request->get('m', 0);
        $selectedN = $request->get('n', 0);
        $selectedP = $request->get('p');
        $export    = $request->get('e');
        $exportAsInt = intval($export);
        //var_dump($export);die;
        $status = $request->get('st', FraisScolarite::STATUS_CREATED); // Par défaut, STATUS_CREATED

        // if ($exportAsInt == 1) {
        //     $status = $request->get('st', FraisScolarite::STATUS_SRS_VALIDATED);
        // }
        
        $currentAnneUniv = $anneeUniversitaireService->getCurrent();
        $selectedCalPaiement = $c ? $calPaiementManager->load($c) : ($export ? null : $calPaiementRepo->findDefault());
        // $dateDebut = date_format($selectedCalPaiement->getDateDebut(), 'Y-m-d');
        $calPaiements = $calPaiementManager->loadAll();
        $semestres  = $semestreManager->loadBy([], ['libelle' => 'ASC']);
        $mentions   = $mentionManager->loadBy([], ['nom' => 'ASC']);
        $niveaux    = $niveauManager->loadBy([], ['libelle' => 'ASC']);
        $parcours   = $parcoursManager->loadBy(
            [
                'mention'   => $selectedM,
                'niveau'    => $selectedN
            ], 
            ['nom' => 'ASC']
        );

        $options = [
            'mention' => $selectedM, 'niveau' => $selectedN, 'parcours' => $selectedP, 'semestre' => $selectedS,
            'parameterBag' => $parameter, 'status' => $status
        ];

        // Filtrer les données en fonction du statut ici
        $fraisScolarites = $fraisScolariteManager->getCurrentPaiement(
            $currentAnneUniv->getId(),
            $selectedCalPaiement,
            $options
        );

        // dd($fraisScolarites);die;

        if ($export) {
            $file = $exportService->getPaiementEcolage($fraisScolarites, $selectedCalPaiement ? $selectedCalPaiement : $calPaiementRepo->findDefault(), $parameter, $status);
            return $this->file($file);
        }

        // Paginer les données
        $page = $request->query->getInt('page', 1);
        $pagination = $paginator->paginate(
            $fraisScolarites, // Vos données à paginer
            $page, // Numéro de la page
            $itemsPerPage=10 // Nombre d'éléments par page
        );

        return $this->render(
            'frontend/scolarite/frais-scolarite/index.html.twig',
            [
                'c'         => $selectedCalPaiement ? $selectedCalPaiement->getId() : 0,
                's'         => $selectedS,
                'm'         => $selectedM,
                'n'         => $selectedN,
                'p'         => $selectedP,
                'st'        => $status,
                'semestres' => $semestres,
                'mentions'  => $mentions,
                'niveaux'   => $niveaux,
                'parcours'  => $parcours,
                'list'      => $fraisScolarites,
                'calPaiements' => $calPaiements,
                'pagination' => $pagination
            ]
        );
    }

    /**
     * @IsGranted("ROLE_SCOLARITE")
     * @Route("/frais/search", name="front_scolarite_frais_search", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\SemestreManager              $semestreManager
     * @param \App\Manager\MentionManager               $mentionManager
     * @param \App\Manager\niveauManager                $niveauManager
     * @param \App\Manager\parcoursManager                $parcoursManager
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function fraisSearch(
        Request                         $request, 
        CalendrierPaiementManager       $calPaiementManager,
        CalendrierPaiementRepository    $calPaiementRepo,
        SemestreManager                 $semestreManager, 
        MentionManager                  $mentionManager,
        NiveauManager                   $niveauManager, 
        ParcoursManager                 $parcoursManager,
        AnneeUniversitaireService       $anneeUniversitaireService, 
        FraisScolariteManager           $fraisScolariteManager,
        ExportDataService               $exportService,
        PaginatorInterface              $paginator,
        ParameterBagInterface           $parameter
        )
    {
        $qString = $request->get('q', '');
        $status = $request->get('st', FraisScolarite::STATUS_CREATED); // Par défaut, STATUS_CREATED

        $currentAnneUniv = $anneeUniversitaireService->getCurrent();
        
        // Filtrer les données en fonction du statut ici
        $fraisScolarites = $fraisScolariteManager->searchByName(
            $currentAnneUniv->getId(),
            $qString
        );

        // dd($fraisScolarites);die;

        return $this->render(
            'frontend/scolarite/frais-scolarite/result-search.html.twig',
            [
                'st'        => $status,
                'list'      => $fraisScolarites
            ]
        );
    }

    /**
     * @IsGranted("ROLE_SCOLARITE")
     * @Route("/frais/show/{id}", name="front_scolarite_frais_show", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\etudiant                      $etudiant
     * @param \App\Manager\FraisScolariteManager        $fraisScolarite
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function fraisShow(
        Request $request, 
        FraisScolarite              $fraisScolarite,
        EcolageService              $ecoService,
        FraisScolariteRepository    $fraisScolariteRepo,
        FraisScolariteManager       $fraisScolariteManager,
        AnneeUniversitaireService   $anneeUniversitaireService)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(
            FraisScolariteType::class,
            $fraisScolarite,
            [
                'fraisScolarite'     => $fraisScolarite,
                'em'       => $em
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $request->request->get(('frais_scolarite'), array());
            // $collegeYear = $anneeUniversitaireService->getCurrent();          
            // $fraisScolarite->setAnneeUniversitaire($collegeYear);
            // $fraisScolarite->setStatus(FraisScolarite::STATUS_SRS_VALIDATED);
            // $fraisScolarite->setAuthor($this->getUser());
            // $ecolageRepo = $this->getDoctrine()->getRepository(Ecolage::class);
            // $ecolageFilters = ['mention' => $fraisScolarite->getMention(), 'niveau' => $fraisScolarite->getNiveau(), 'semestre' => $fraisScolarite->getSemestre()];
            // if($parcours = $fraisScolarite->getParcours())
            //     $ecolageFilters['parcours'] = $parcours;
            // $classEcolage = $ecolageRepo->findOneBy($ecolageFilters);
            // //getLastEcolage
            // $lastEcolage = $fraisScolariteRepo->getEtudiantLastPaiement($fraisScolarite->getEtudiant(), $fraisScolarite->getSemestre(), $collegeYear, $fraisScolarite);
            // $fraisScolariteManager->setReste($fraisScolarite, $lastEcolage, $classEcolage->getMontant());
            // $fraisScolariteManager->save($fraisScolarite);            
            $currentStatus = FraisScolarite::STATUS_SRS_VALIDATED;
            $fraisScolarite->setStatus($currentStatus);
            //history
            $ecolageHistory = new PaiementHistory();
            $ecolageHistory->setResourceName(PaiementHistory::ECOLAGE_RESOURCE);
            $ecolageHistory->setStatut($currentStatus);
            $ecolageHistory->setResourceId($fraisScolarite->getId());
            $ecolageHistory->setValidator($this->getUser());
            $ecolageHistory->setCreatedAt(new \DateTime());
            $ecolageHistory->setUpdatedAt(new \DateTime());
            $ecolageHistory->setComment($formData['comment']);
            $ecolageHistory->setMontant($fraisScolarite->getMontant());
            $em->persist($ecolageHistory);
            $fraisScolariteManager->savePerEtudiant($fraisScolarite, $ecoService);   

            return $this->redirectToRoute('front_scolarite_frais_index');
        }

        return $this->render(
            'frontend/scolarite/frais-scolarite/show.html.twig',
            [
                'form'  => $form->createView(),
                'fraisScolarite' => $fraisScolarite
            ]
        );
    }

    /**
     * @IsGranted({"ROLE_SCOLARITE"})
     * @Route("/frais/update", name="front_scolarite_frais_update", methods={"POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\PrestationManager            $prestationManager
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     * @param \App\Manager\MentionManager               $mentionManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function fraisUpdate(
        Request                     $request,
        FraisScolariteManager       $fraisScolManager
    ) {
        $ecolageIds = $request->get('ecolage', array());
        $entityManager = $this->getDoctrine()->getManager();
        foreach($ecolageIds as $id) {
            $currFraisScol = $fraisScolManager->load($id);
            $currFraisNextStatut = $currFraisScol->getStatus() == FraisScolarite::STATUS_CREATED ? 
                FraisScolarite::STATUS_SRS_VALIDATED :
                FraisScolarite::STATUS_CREATED;
            $currFraisScol->setStatus($currFraisNextStatut);
            $entityManager->persist($currFraisScol);
       
            $ecolageHistory = new PaiementHistory();
            $ecolageHistory->setResourceName(PaiementHistory::ECOLAGE_RESOURCE);
            $ecolageHistory->setStatut($currFraisNextStatut);
            $ecolageHistory->setResourceId($currFraisScol->getId());
            $ecolageHistory->setValidator($this->getUser());
            $ecolageHistory->setCreatedAt(new \DateTime());
            $ecolageHistory->setUpdatedAt(new \DateTime());
            $ecolageHistory->setMontant($currFraisScol->getMontant());
            $entityManager->persist($ecolageHistory);
        };

        $entityManager->flush();
        
        return new JsonResponse(array('statut' => '200'));
    }

    /**
     * @IsGranted({"ROLE_SCOLARITE"})
     * @Route("/frais/cancel/{id}", name="front_scolarite_frais_cancel", methods={"POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\PrestationManager            $prestationManager
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     * @param \App\Manager\MentionManager               $mentionManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function fraisCancel(
        Request                     $request,
        FraisScolarite              $fraisScolarite,
        FraisScolariteManager       $fraisScolManager
    ) {
        $formData = $request->request->get(('frais_scolarite'), array());
        $entityManager = $this->getDoctrine()->getManager();
        $currFraisNextStatut = FraisScolarite::STATUS_SRS_REFUSED;
        $fraisScolarite->setStatus($currFraisNextStatut);
        $previousReste = $fraisScolarite->getReste();
        $fraisScolarite->setReste($fraisScolarite->getMontant() + $previousReste);
        $entityManager->persist($fraisScolarite);
   
        $ecolageHistory = new PaiementHistory();
        $ecolageHistory->setResourceName(PaiementHistory::ECOLAGE_RESOURCE);
        $ecolageHistory->setStatut($currFraisNextStatut);
        $ecolageHistory->setResourceId($fraisScolarite->getId());
        $ecolageHistory->setValidator($this->getUser());
        $ecolageHistory->setCreatedAt(new \DateTime());
        $ecolageHistory->setUpdatedAt(new \DateTime());
        $ecolageHistory->setComment($formData['comment']);
        $ecolageHistory->setMontant($fraisScolarite->getMontant());
        $entityManager->persist($ecolageHistory);
        
        $entityManager->flush();
        
        return $this->redirectToRoute('front_scolarite_frais_index');
    }

    /**
     * @IsGranted("ROLE_SCOLARITE")
     * @Route("/gestion/examen/index", name="front_scolarite_gestion_examen_index", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\SemestreManager              $semestreManager
     * @param \App\Manager\MentionManager               $mentionManager
     * @param \App\Manager\niveauManager                $niveauManager
     * @param \App\Manager\parcoursManager                $parcoursManager
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function gestionExamenIndex(
        Request $request, 
        SemestreManager $semestreManager, 
        MentionManager $mentionManager,
        NiveauManager $niveauManager, 
        ParcoursManager $parcoursManager,
        AnneeUniversitaireService $anneeUniversitaireService, 
        FraisScolariteManager $fraisScolariteManager)
    {
        $selectedS = $request->get('s', 0);
        $selectedM = $request->get('m', 0);
        $selectedN = $request->get('n', 0);
        $selectedP = $request->get('p');
        $currentAnneUniv = $anneeUniversitaireService->getCurrent();

        $semestres  = $semestreManager->loadBy([], ['libelle' => 'ASC']);
        $mentions   = $mentionManager->loadBy([], ['nom' => 'ASC']);
        $niveaux    = $niveauManager->loadBy([], ['libelle' => 'ASC']);
        $parcours   = $parcoursManager->loadBy(
            [
                'mention'   => $selectedM,
                'niveau'    => $selectedN
            ], 
            ['nom' => 'ASC']
        );
        $fraisScolarites = $fraisScolariteManager->getListPerClass(
            $selectedS,
            $selectedM, 
            $selectedN, 
            $selectedP,
            $currentAnneUniv->getId()
        );
        // dump($fraisScolarites);die;
        return $this->render(
            'frontend/scolarite/gestion-examen/index.html.twig',
            [
                's'         => $selectedS,
                'm'         => $selectedM,
                'n'         => $selectedN,
                'p'         => $selectedP,
                'semestres' => $semestres,
                'mentions'  => $mentions,
                'niveaux'   => $niveaux,
                'parcours'  => $parcours,
                'fraisScolarites' => $fraisScolarites
            ]
        );
    
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

    //


     /**
     * @Route("/etudiants", name="front_super_scolarite_student_index", methods={"GET"})
     * @param \App\Manager\etudiantManager $studentManager
     * @param \App\Services\AnneeUniversitaireService     $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @Security("is_granted('ROLE_SSRS') or is_granted('ROLE_SCOLARITE')")
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
            'frontend/scolarite/etudiant/list_etudiant.html.twig',
            $params
        );
    }


    // ajouter nouveau etudiant

    /**
     * @Route("/etudiant/new", name="front_super_scolarité_student_new", methods={"GET", "POST"})
     * @param \App\Manager\etudiantManager $studentManager
     * @param \App\Services\AnneeUniversitaireService     $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @IsGranted("ROLE_SSRS")
     */
    public function ajouterEtudiant(
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
        $immatriculation = $request->get('immatricule');
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
            if (empty($immatriculation)) {
                // Générez automatiquement l'immatriculation comme vous le faites déjà
                $etudiantService->setMatricule($student);
            } else {
                // Utilisez la valeur saisie pour l'immatriculation
                $student->setImmatricule($immatriculation);
            }
            $em->persist($student);
            $em->flush();

            return $this->redirectToRoute('front_super_scolarite_student_index');
        }

        $params = [
                'mentions'         => $mentions,
                'niveaux'          => $niveaux,
                'parcours'         => $parcours,
                'form' => $form->createView()
            ];
        $params = array_merge($params, self::$commonParams);
        return $this->render(
            'frontend/scolarite/etudiant/formulaire_ajout_etudiant.html.twig',
            $params
        );

    }
        
     /**
     * @Route("/etudiant/parcours", name="front_super_scolarite_ajax_parcours", methods={"GET"})
     * @param \App\Manager\ParcoursManager $parcoursManager
     * @param \App\Services\AnneeUniversitaireService     $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @IsGranted("ROLE_SSRS")
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
            'frontend/scolarite/etudiant/_parcours_options.html.twig',
            [
                'parcours' => $parcours
            ]
        );
    }


}
