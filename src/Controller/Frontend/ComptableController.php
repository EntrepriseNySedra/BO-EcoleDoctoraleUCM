<?php

namespace App\Controller\Frontend;

use App\Entity\EmploiDuTemps;
use App\Entity\Enseignant;
use App\Entity\FraisScolarite;
use App\Entity\PaiementHistory;
use App\Entity\Prestation;
use App\Entity\User;

use App\Manager\AnneeUniversitaireManager;
use App\Manager\CalendrierExamenManager;
use App\Manager\CalendrierPaiementManager;
use App\Manager\EmploiDuTempsManager;
use App\Manager\FraisScolariteManager;
use App\Manager\InscriptionManager;
use App\Manager\MentionManager;
use App\Manager\NiveauManager;
use App\Manager\ParcoursManager;
use App\Manager\PrestationManager;

use App\Repository\CalendrierPaiementRepository;
use App\Repository\EnseignantRepository;

use App\Services\AnneeUniversitaireService;
use App\Services\EcolageService;
use App\Services\ExportDataService;
use App\Services\ExportEcolageService;
use App\Services\ExportPrestationService;
use App\Services\ExportVacationService;
use App\Services\WorkflowStatutService;
use App\Services\ExportSurveillanceService;
use App\Services\WorkflowEcolageStatutService;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
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
use Symfony\Component\HttpFoundation\File\File;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Description of ComptableController.php.
 *
 * @package App\Controller\Frontend
 * @Route("/finance")
 */
class ComptableController extends AbstractController
{
    /**
     * @Route("/vacations/list", name="front_comptable_presence_enseignant_index", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request   $request
     * @param \App\Manager\CalendrierPaiementManager      $calPaiementManager
     * @param \App\Manager\EmploiDuTempsManager           $edtManager
     * @param \App\Services\AnneeUniversitaireService     $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @IsGranted({"ROLE_COMPTABLE", "ROLE_RF"})
     */
    public function fichePresenceEnseignantList(
        Request                         $request, 
        EmploiDuTempsManager            $edtManager,
        CalendrierPaiementManager       $calPaiementManager,
        CalendrierPaiementRepository    $calPaiementRepo,
        AnneeUniversitaireService       $anneeUniversitaireService,
        WorkflowStatutService           $workflowStatService,
        MentionManager                  $mentionManager,
        PaginatorInterface                  $paginator)
    {
        $user = $this->getUser();
        $c = $request->get('c', '');
        $m = $request->get('m', '');
        $page = $request->query->getInt('page', 1);
        $selectedCalPaiement = $c? $calPaiementManager->load($c) : $calPaiementRepo->findDefault();
        $calPaiements = $calPaiementManager->loadAll();
        $currentAnneUniv    = $anneeUniversitaireService->getCurrent();
        $mentions = $mentionManager->loadAll();
        $profilListStatut = $workflowStatService->getStatutForListByProfil($user->getRoles());
        $profilNextStatut = $workflowStatService->getResourceNextStatut($profilListStatut);
        $profilPrevStatut = $workflowStatService->getEdtPreviousStatut($profilListStatut);

        $fPresenceEnseignants = $edtManager->getCurrentVacation(
            $currentAnneUniv->getId(),
            $m,
            $selectedCalPaiement
        );
        $fPresenceEnseignants = $paginator->paginate(
            $fPresenceEnseignants,
            $page,
            20
        );
        // dump($fPresenceEnseignants);die;
        return $this->render(
            'frontend/comptable/presence-enseignant-list.html.twig',
            [
                'c'                     => $selectedCalPaiement ? $selectedCalPaiement->getId() : 0,
                'm'                     => $m,
                'calPaiements'          => $calPaiements,
                'mentions'              => $mentions,
                'fPresenceEnseignants'  => $fPresenceEnseignants,
                'profilListStatut'  => $profilListStatut,
                'profilNextStatut'  => $profilNextStatut,
                'profilPrevStatut'  => $profilPrevStatut
            ]
        );
    }

    /**
     * @Route("/vacations/export", name="front_comptable_export_vacation", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request   $request
     * @param \App\Manager\CalendrierPaiementManager      $calPaiementManager
     * @param \App\Manager\EmploiDuTempsManager           $edtManager
     * @param \App\Services\AnneeUniversitaireService     $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @IsGranted({"ROLE_COMPTABLE", "ROLE_RF"})
     */
    public function vacationExport(
        Request                         $request, 
        EmploiDuTempsManager            $edtManager,
        CalendrierPaiementManager       $calPaiementManager,
        AnneeUniversitaireService       $anneeUniversitaireService,
        WorkflowStatutService           $workflowStatService,
        ParameterBagInterface           $parameter,
        ExportVacationService           $exportService,
        MentionManager                   $mentionManager)
    {
        $user             = $this->getUser();
        $currentUserRoles = $user->getRoles();
        // $userDisplayStatut = $workflowStatService->getStatutForListByProfil($currentUserRoles);
        $c = $request->get('c', '');
        $m = $request->get('m', '');       
        $selectedCalPaiement = $c? $calPaiementManager->load($c) : "";
        $mentions = $mentionManager->loadAll();
        $calPaiements = $calPaiementManager->loadAll();
        $currentAnneUniv    = $anneeUniversitaireService->getCurrent();
        $fPresenceEnseignants = $edtManager->getExportableVacation(
            $currentAnneUniv->getId(),
            $selectedCalPaiement,
            null,
            $m
        );

        // dump($fPresenceEnseignants);die;

        $result = [];
        foreach($fPresenceEnseignants as $item) {
            if(!array_key_exists($item['mention'], $result)) {
                $itemMention = $result[$item['mention']] = [];
            }

            if(!array_key_exists($item['niveau'], $itemMention)) {
               $itemEnseignant = $itemMention[$item['niveau']] = [];
            }

            if(!array_key_exists($item['ensId'], $itemEnseignant)) {
               $itemEnsData = $itemEnseignant[$item['ensId']] = [];
            }

            $itemMention[] = $itemMention;
            $result[$item['mention']][$item['niveau']][$item['ensId']][] = $item;
        }
        $file = $exportService->getVacationDetails($result, $selectedCalPaiement, $parameter);       

        return $this->file($file);
    }

    /**
     * @Route("/vacations/compta-export", name="front_comptable_export_vacation_compta", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request   $request
     * @param \App\Manager\CalendrierPaiementManager      $calPaiementManager
     * @param \App\Manager\EmploiDuTempsManager           $edtManager
     * @param \App\Services\AnneeUniversitaireService     $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @IsGranted({"ROLE_COMPTABLE", "ROLE_RF"})
     */
    public function vacationComptaExport(
        Request                         $request, 
        EmploiDuTempsManager            $edtManager,
        CalendrierPaiementManager       $calPaiementManager,
        AnneeUniversitaireService       $anneeUniversitaireService,
        WorkflowStatutService           $workflowStatService,
        ParameterBagInterface           $parameter,
        ExportVacationService           $exportService)
    {
        $user             = $this->getUser();
        $c = $request->get('c', '');
        $np = $request->get('np', 1);
        $m = $request->get('m', '');
        $selectedCalPaiement = $c? $calPaiementManager->load($c) : "";
        $calPaiements = $calPaiementManager->loadAll();
        $currentAnneUniv    = $anneeUniversitaireService->getCurrent();
        $list = $edtManager->getExportableVacationCompta(
            $currentAnneUniv->getId(),
            $selectedCalPaiement,
            null,
            $m
        );        
        $file = $exportService->getVacationCompta($list, $selectedCalPaiement, $parameter, intval($np));       

        return $this->file($file);
    }

    /**
     * @Route("/vacations/banque-export", name="front_comptable_export_vacation_bank", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request   $request
     * @param \App\Manager\CalendrierPaiementManager      $calPaiementManager
     * @param \App\Manager\EmploiDuTempsManager           $edtManager
     * @param \App\Services\AnneeUniversitaireService     $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @IsGranted({"ROLE_COMPTABLE", "ROLE_RF"})
     */
    public function vacationBankExport(
        Request                         $request, 
        EmploiDuTempsManager            $edtManager,
        CalendrierPaiementManager       $calPaiementManager,
        AnneeUniversitaireService       $anneeUniversitaireService,
        WorkflowStatutService           $workflowStatService,
        ParameterBagInterface           $parameter,
        ExportVacationService           $exportService)
    {
        $user             = $this->getUser();
        $c = $request->get('c', '');
        $np = $request->get('np', 1);
        $m = $request->get('m', '');
        $selectedCalPaiement = $c? $calPaiementManager->load($c) : "";
        $calPaiements = $calPaiementManager->loadAll();
        $currentAnneUniv    = $anneeUniversitaireService->getCurrent();
        $list = $edtManager->getExportableVacationPerEns(
            $currentAnneUniv->getId(),
            $selectedCalPaiement,
            null,
            $m
        );        
        $file = $exportService->getVacationBank($list, $selectedCalPaiement, $parameter, intval($np));       

        return $this->file($file);
    }

    /**
     * @Route("/vacations/opavi-export", name="front_comptable_export_vacation_opavi", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request   $request
     * @param \App\Manager\CalendrierPaiementManager      $calPaiementManager
     * @param \App\Manager\EmploiDuTempsManager           $edtManager
     * @param \App\Services\AnneeUniversitaireService     $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @IsGranted({"ROLE_COMPTABLE", "ROLE_RF"})
     */
    public function vacationOpaviExport(
        Request                         $request, 
        EmploiDuTempsManager            $edtManager,
        CalendrierPaiementManager       $calPaiementManager,
        AnneeUniversitaireService       $anneeUniversitaireService,
        WorkflowStatutService           $workflowStatService,
        ParameterBagInterface           $parameter,
        ExportVacationService           $exportService)
    {
        $user             = $this->getUser();
        $c = $request->get('c', '');
        $np = $request->get('np', 1);
        $m = $request->get('m', '');
        $selectedCalPaiement = $c? $calPaiementManager->load($c) : "";
        $calPaiements = $calPaiementManager->loadAll();
        $currentAnneUniv    = $anneeUniversitaireService->getCurrent();
        $list = $edtManager->getExportableVacationPerEns(
            $currentAnneUniv->getId(),
            $selectedCalPaiement,
            null,
            $m
        );        
        $file = $exportService->getVacationOpavi($list, $selectedCalPaiement, $parameter, intval($np));       

        return $this->file($file);
    }

    /**
     * @Route("/vacations/enseignant/{id}", name="front_comptable_vacation_enseignant_index", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Enseignant                    $enseignant
     * @param \App\Manager\CalendrierPaiementManager    $calPaiementManager
     * @param \App\Manager\EmploiDuTempsManager         $edtManager
     * @param \App\Service\AnneeUniversitaireService    $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @IsGranted({"ROLE_COMPTABLE", "ROLE_RF"})
     */
    public function vacationEnseignantList(
        Request                         $request, 
        Enseignant                      $enseignant,
        EmploiDuTempsManager            $edtManager,
        CalendrierPaiementManager       $calPaiementManager,
        AnneeUniversitaireService       $anneeUniversitaireService,
        WorkflowStatutService           $workflowStatService,
        PaginatorInterface                  $paginator)
    {
        $user = $this->getUser();
        $c = $request->get('c', '');
        $m = $request->get('m', '');
        $page = $request->query->getInt('page', 1);
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
        // $fPresenceEnseignants = $paginator->paginate(
        //     $fPresenceEnseignants,
        //     $page,
        //     6
        // );

        return $this->render(
            'frontend/comptable/vacation/enseignant-index.html.twig',
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
     * @Route("/vacation/enseignant/{id}/edit", name="front_comptable_vacation_enseignant_edit", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Enseignant                      $enseignant
     * @param \App\Manager\CalendrierPaiementManager      $calPaiementManager
     * @param \App\Manager\EmploiDuTempsManager           $edtManager
     * @param \App\Services\AnneeUniversitaireService     $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @IsGranted({"ROLE_COMPTABLE", "ROLE_RF"})
     */
    public function vacationEnseignantEdit (
        Request                         $request, 
        EmploiDuTemps                   $edt,
        EmploiDuTempsManager            $edtManager,
        CalendrierPaiementManager       $calPaiementManager,
        ParameterBagInterface $parameter,
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

            return $this->redirectToRoute('front_comptable_presence_enseignant_index');
        }
        
        return $this->render(
            'frontend/comptable/vacation/enseignant-edit.html.twig',
            [
                'c'             => $c,
                'edt'           => $edt,
                'edtNextStatut' => $edtNextStatut,
                'edtPreviousStatut' => $edtPreviousStatut
            ]
        );
    }

    /**
     * @IsGranted({"ROLE_COMPTABLE", "ROLE_RF"})
     * @Route("/validate/vacations/", name="front_finance_vacation_validation", methods={"POST"})
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
     * @IsGranted({"ROLE_COMPTABLE", "ROLE_RF"})
     * @Route("/prestations", name="front_finance_prestation_index", methods={"GET"})
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

        // dump($prestations);die;

        return $this->render(
            'frontend/comptable/prestation/index.html.twig',
            [
                'c'            => $selectedCalPaiement ? $selectedCalPaiement->getId() : 0,
                'calPaiements' => $calPaiements,
                'list'         => $prestations
            ]
        );
    }

    /**
     * @IsGranted({"ROLE_COMPTABLE", "ROLE_RF"})
     * @Route("/prestations/details/{id}", name="front_finance_prestation_details_index", methods={"GET"})
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
        $userDisplayStatut = $workflowStatService->getStatutForListByProfil($currentUserRoles);
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
            null,
            $user->getId()
        );

        return $this->render(
            'frontend/comptable/prestation/resume-index.html.twig',
            [
                'c'            => $c,
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
     * @Route("/prestations/{id}/validatable/", name="front_finance_valid_prestation_index", methods={"GET"})
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
        PrestationManager           $prestationManager,
        CalendrierPaiementManager   $calPaiementManager,
        CalendrierPaiementRepository   $calPaiementRepo,
        AnneeUniversitaireService   $anneeUniversitaireService,
        ParameterBagInterface       $parameter,
        WorkflowStatutService       $workflowStatService
    ) {
        // dump($parameter->get('version'));die;
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

        return $this->render(
            'frontend/comptable/prestation/validatable-index.html.twig',
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
     * @IsGranted({"ROLE_COMPTABLE", "ROLE_RF"})
     * @Route("/validate/prestations/", name="front_finance_prestation_validation", methods={"POST"})
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
     * @Route("/prestations/export", name="front_comptable_export_presta", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request   $request
     * @param \App\Manager\CalendrierPaiementManager      $calPaiementManager
     * @param \App\Manager\EmploiDuTempsManager           $edtManager
     * @param \App\Services\AnneeUniversitaireService     $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @IsGranted({"ROLE_COMPTABLE", "ROLE_RF"})
     */
    public function prestationExport(
        Request                         $request, 
        EmploiDuTempsManager            $edtManager,
        CalendrierPaiementManager       $calPaiementManager,
        AnneeUniversitaireService       $anneeUniversitaireService,
        WorkflowStatutService           $workflowStatService,
        ParameterBagInterface           $parameter,
        ExportVacationService           $exportService,
        MentionManager                  $mentionManager)
    {
        $user             = $this->getUser();
        $currentUserRoles = $user->getRoles();
        // $userDisplayStatut = $workflowStatService->getStatutForListByProfil($currentUserRoles);
        $c = $request->get('c', '');
        $m = $request->get('m', '');
        $selectedCalPaiement = $c? $calPaiementManager->load($c) : "";
        $calPaiements = $calPaiementManager->loadAll();
        $mentions = $mentionManager->loadAll();
        $currentAnneUniv    = $anneeUniversitaireService->getCurrent();
        $fPresenceEnseignants = $edtManager->getExportableVacation(
            $currentAnneUniv->getId(),
            $selectedCalPaiement,
            null,
            $m
        );

        // dump($fPresenceEnseignants);die;

        $result = [];
        foreach($fPresenceEnseignants as $item) {
            if(!array_key_exists($item['mention'], $result)) {
                $itemMention = $result[$item['mention']] = [];
            }

            if(!array_key_exists($item['niveau'], $itemMention)) {
               $itemEnseignant = $itemMention[$item['niveau']] = [];
            }

            if(!array_key_exists($item['ensId'], $itemEnseignant)) {
               $itemEnsData = $itemEnseignant[$item['ensId']] = [];
            }

            $itemMention[] = $itemMention;
            $result[$item['mention']][$item['niveau']][$item['ensId']][] = $item;
        }
        $file = $exportService->getVacationDetails($result, $selectedCalPaiement, $parameter);       

        return $this->file($file);
    }

    /**
     * @Route("/prestation/compta-export", name="front_comptable_export_presta_compta", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request   $request
     * @param \App\Manager\CalendrierPaiementManager      $calPaiementManager
     * @param \App\Manager\EmploiDuTempsManager           $edtManager
     * @param \App\Services\AnneeUniversitaireService     $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @IsGranted({"ROLE_COMPTABLE", "ROLE_RF"})
     */
    public function prestationComptaExport(
        Request                         $request, 
        PrestationManager               $prestaManager,
        CalendrierPaiementManager       $calPaiementManager,
        AnneeUniversitaireService       $anneeUniversitaireService,
        WorkflowStatutService           $workflowStatService,
        ParameterBagInterface           $parameter,
        ExportPrestationService         $exportService)
    {
        $user             = $this->getUser();
        $c = $request->get('c', '');
        $np = $request->get('np', 1);
        $selectedCalPaiement = $c? $calPaiementManager->load($c) : "";
        $calPaiements = $calPaiementManager->loadAll();
        $currentAnneUniv    = $anneeUniversitaireService->getCurrent();
        $list = $prestaManager->getExportableCompta(
            $currentAnneUniv->getId(),
            $selectedCalPaiement,
            null
        );      

        // dump($list);die;

        $file = $exportService->getCompta($list, $selectedCalPaiement, $parameter, intval($np));       

        return $this->file($file);
    }

    /**
     * @Route("/prestations/banque-export", name="front_comptable_export_presta_bank", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request   $request
     * @param \App\Manager\CalendrierPaiementManager      $calPaiementManager
     * @param \App\Manager\EmploiDuTempsManager           $edtManager
     * @param \App\Services\AnneeUniversitaireService     $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @IsGranted({"ROLE_COMPTABLE", "ROLE_RF"})
     */
    public function prestationBankExport(
        Request                         $request, 
        PrestationManager               $prestaManager,
        CalendrierPaiementManager       $calPaiementManager,
        AnneeUniversitaireService       $anneeUniversitaireService,
        WorkflowStatutService           $workflowStatService,
        ParameterBagInterface           $parameter,
        ExportPrestationService         $exportService)
    {
        $user             = $this->getUser();
        $c = $request->get('c', '');
        $np = $request->get('np', 1);
        $selectedCalPaiement = $c? $calPaiementManager->load($c) : "";
        $calPaiements = $calPaiementManager->loadAll();
        $currentAnneUniv    = $anneeUniversitaireService->getCurrent();
        $list = $prestaManager->getExportableCompta(
            $currentAnneUniv->getId(),
            $selectedCalPaiement,
            null
        );        
        $file = $exportService->getBank($list, $selectedCalPaiement, $parameter, intval($np));       

        return $this->file($file);
    }

    /**
     * @Route("/prestations/opavi-export", name="front_comptable_export_presta_opavi", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request   $request
     * @param \App\Manager\CalendrierPaiementManager      $calPaiementManager
     * @param \App\Manager\EmploiDuTempsManager           $edtManager
     * @param \App\Services\AnneeUniversitaireService     $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @IsGranted({"ROLE_COMPTABLE", "ROLE_RF"})
     */
    public function prestationOpaviExport(
        Request                         $request, 
        PrestationManager               $prestaManager,
        CalendrierPaiementManager       $calPaiementManager,
        AnneeUniversitaireService       $anneeUniversitaireService,
        WorkflowStatutService           $workflowStatService,
        ParameterBagInterface           $parameter,
        ExportPrestationService         $exportService)
    {
        $user             = $this->getUser();
        $c = $request->get('c', '');
        $np = $request->get('np', 1);
        $selectedCalPaiement = $c? $calPaiementManager->load($c) : "";
        $calPaiements = $calPaiementManager->loadAll();
        $currentAnneUniv    = $anneeUniversitaireService->getCurrent();
        $list = $prestaManager->getExportablePerUser(
            $currentAnneUniv->getId(),
            $selectedCalPaiement,
            null
        );        
        $file = $exportService->getVacationOpavi($list, $selectedCalPaiement, $parameter, intval($np));       

        return $this->file($file);
    }

    /**
     * @IsGranted({"ROLE_COMPTABLE", "ROLE_RF"})
     * @Route("/surveillances", name="front_finance_surveillance_index", methods={"GET"})
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
        $mentions = $mentionManager->loadAll();
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

        return $this->render(
            'frontend/comptable/surveillance/index.html.twig', $params
        );
    }

    /**
     * @IsGranted({"ROLE_COMPTABLE", "ROLE_RF"})
     * @Route("/surveillance/{id}/details", name="front_finance_surveillance_details", methods={"GET", "POST"})
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

        return $this->render(
            'frontend/comptable/surveillance/details-index.html.twig', $params
        );
    }  

    /**
     * @IsGranted({"ROLE_COMPTABLE", "ROLE_RF"})
     * @Route("/validate/surveillance/", name="front_finance_surveillance_validate", methods={"POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\PrestationManager            $prestationManager
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function validateSurveillance(
        Request                     $request,
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

    /**
     * @Route("/surveillance/compta-export", name="front_comptable_export_surv_compta", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request   $request
     * @param \App\Manager\CalendrierPaiementManager      $calPaiementManager
     * @param \App\Manager\EmploiDuTempsManager           $edtManager
     * @param \App\Services\AnneeUniversitaireService     $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @IsGranted({"ROLE_COMPTABLE", "ROLE_RF"})
     */
    public function surveillanceComptaExport(
        Request                         $request, 
        CalendrierExamenManager         $calExamxamManager,
        CalendrierPaiementManager       $calPaiementManager,
        AnneeUniversitaireService       $anneeUniversitaireService,
        WorkflowStatutService           $workflowStatService,
        ParameterBagInterface           $parameter,
        ExportSurveillanceService       $exportService)
    {
        $user             = $this->getUser();
        $c = $request->get('c', '');
        $np = $request->get('np', 1);
        $m = $request->get('m', 1);
        $selectedCalPaiement = $c? $calPaiementManager->load($c) : "";
        $calPaiements = $calPaiementManager->loadAll();
        $currentAnneUniv    = $anneeUniversitaireService->getCurrent();
        $list = $calExamxamManager->getExportableVacationCompta(
            $currentAnneUniv->getId(),
            $selectedCalPaiement,
            null,
            $m
        );        
        $file = $exportService->getVacationCompta($list, $selectedCalPaiement, $parameter, intval($np));       

        return $this->file($file);
    }

    /**
     * @Route("/surveillance/etat-export", name="front_comptable_export_surv_etat", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request   $request
     * @param \App\Manager\CalendrierPaiementManager      $calPaiementManager
     * @param \App\Manager\EmploiDuTempsManager           $edtManager
     * @param \App\Services\AnneeUniversitaireService     $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @IsGranted({"ROLE_COMPTABLE", "ROLE_RF"})
     */
    public function surveillanceEtatExport(
        Request                         $request, 
        CalendrierExamenManager         $calExamxamManager,
        CalendrierPaiementManager       $calPaiementManager,
        AnneeUniversitaireService       $anneeUniversitaireService,
        WorkflowStatutService           $workflowStatService,
        ParameterBagInterface           $parameter,
        ExportSurveillanceService       $exportService)
    {
        $user             = $this->getUser();
        $c = $request->get('c', '');
        $m = $request->get('m', '');
        $selectedCalPaiement = $c? $calPaiementManager->load($c) : "";
        $calPaiements = $calPaiementManager->loadAll();
        $currentAnneUniv    = $anneeUniversitaireService->getCurrent();
        $list = $calExamxamManager->getEtatPerSurveillant(
            $currentAnneUniv->getId(),
            $selectedCalPaiement,
            WorkflowStatutService::STATUS_RECTEUR_VALIDATED,
            $m
        );        
        $file = $exportService->getEtatPaiement($list, $selectedCalPaiement, $parameter);       

        return $this->file($file);
    }

    /**
     * @IsGranted({"ROLE_COMPTABLE", "ROLE_RF"})
     * @Route("/ecolages", name="front_finance_ecolage_index", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\CalendrierPaiementManager    $calPaiementManager
     * @param \App\Manager\FraisScolariteManager        $fraisScolManager
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     * @param \App\Manager\MentionManager               $mentionManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function ecolageIndex(
        Request                             $request,
        FraisScolariteManager               $fraisScolManager,
        CalendrierPaiementManager           $calPaiementManager,
        CalendrierPaiementRepository        $calPaiementRepo,
        AnneeUniversitaireService           $anneeUniversitaireService,
        WorkflowEcolageStatutService        $workflowEcoStatService,
        ExportEcolageService                $ecolageService,
        ParameterBagInterface               $parameter,
        PaginatorInterface                  $paginator
    ) {
        $user               = $this->getUser();
        $c = $request->get('c', '');
        $actionExport = $request->get('export', '');
        $np = $request->get('np', 1);
        $page = $request->query->getInt('page', 1);

        $selectedCalPaiement = $c? $calPaiementManager->load($c) : $calPaiementRepo->findDefault(); 
        $calPaiements = $calPaiementManager->loadAll();
        $currentAnneUniv    = $anneeUniversitaireService->getCurrent();

        $profilListStatut = $workflowEcoStatService->getStatutForListByProfil($user->getRoles());
        $profilNextStatut = $workflowEcoStatService->getResourceNextStatut($profilListStatut);
        $profilPrevStatut = $workflowEcoStatService->getEdtPreviousStatut($profilListStatut);

        $ecolages = $fraisScolManager->getCurrentPaiement($currentAnneUniv->getId(), $selectedCalPaiement, ['parameterBag' => $parameter, 'status' => FraisScolarite::STATUS_SRS_VALIDATED]);
        if($actionExport) {
            $ecolages = $fraisScolManager->getCurrentPaiement($currentAnneUniv->getId(), $selectedCalPaiement, ['parameterBag' => $parameter, 'status' => FraisScolarite::STATUS_COMPTA_VALIDATED]);
            $etatBankFile = $ecolageService->getEtatBanque($ecolages, $selectedCalPaiement, $parameter, $np);
            return $this->file($etatBankFile);
        }

        $ecolages = $paginator->paginate(
            $ecolages,
            $page,
            20
        );

        //dd(count($ecolages));die;

        $params = [
                'c'            => $selectedCalPaiement ? $selectedCalPaiement->getId() : 0,
                'calPaiements' => $calPaiements,
                'list'         => $ecolages,
                'profilListStatut'      => $profilListStatut,
                'profilNextStatut'      => $profilNextStatut,
                'profilPrevStatut'      => $profilPrevStatut
            ];

        return $this->render(
            'frontend/comptable/ecolage/index.html.twig', $params
        );
    }

    /**
     * @IsGranted({"ROLE_COMPTABLE", "ROLE_RF"})
     * @Route("/frais/update", name="front_finance_frais_update", methods={"POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\FraisScolariteManager        $fraisScolManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function fraisUpdate(
        Request                     $request,
        FraisScolariteManager       $fraisScolManager,
        WorkflowEcolageStatutService $workflowEcoStatService,
        EcolageService              $ecoService
    ) {
        $userRoles                = $this->getUser()->getRoles();
        $profilListStatut = $workflowEcoStatService->getStatutForListByProfil($userRoles);
        $profilNextStatut = $workflowEcoStatService->getResourceNextStatut($profilListStatut);
        $profilPrevStatut = $workflowEcoStatService->getEdtPreviousStatut($profilListStatut);
        $ecolageIds = $request->get('ecolage', array());
        $entityManager = $this->getDoctrine()->getManager();
        $etudiantIds = [];
        foreach($ecolageIds as $id) {
            $currFraisScol = $fraisScolManager->load($id);
            $currFraisNextStatut = $currFraisScol->getStatus() == $profilListStatut ? $profilNextStatut : $profilPrevStatut;
            $currFraisScol->setStatus($currFraisNextStatut);
            $entityManager->persist($currFraisScol);

            //Update Etudiant status
            if(!in_array($edutiandId=$currFraisScol->getEtudiant()->getId(), $etudiantIds)){
                $etudiantIds[] = $edutiandId;
            }           
            $ecolageHistory = new PaiementHistory();
            $ecolageHistory->setResourceName(PaiementHistory::ECOLAGE_RESOURCE);
            $ecolageHistory->setStatut($currFraisNextStatut);
            $ecolageHistory->setResourceId($currFraisScol->getId());
            $ecolageHistory->setMontant($currFraisScol->getMontant());
            $ecolageHistory->setValidator($this->getUser());
            $ecolageHistory->setCreatedAt(new \DateTime());
            $ecolageHistory->setUpdatedAt(new \DateTime());
            $entityManager->persist($ecolageHistory);
        };
        // dump($etudiantIds);die;

        $ecoService->updateEtudiantStatus($etudiantIds);

        $entityManager->flush();
        
        return new JsonResponse(array('statut' => '200'));
    }

    /**
     * @IsGranted({"ROLE_COMPTABLE", "ROLE_RF"})
     * @Route("/frais/archive", name="front_finance_frais_archive", methods={"POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\FraisScolariteManager        $fraisScolManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function fraisArchive(
        Request                     $request,
        FraisScolariteManager       $fraisScolManager,
        WorkflowEcolageStatutService $workflowEcoStatService
    ) {
        $ecolageIds = $request->get('ecolage', array());
        $entityManager = $this->getDoctrine()->getManager();
        foreach($ecolageIds as $id) {
            $currFraisScol = $fraisScolManager->load($id);
            $currFraisNextStatut = WorkflowEcolageStatutService::STATUS_ARCHIVED;
            $currFraisScol->setStatus($currFraisNextStatut);
            $entityManager->persist($currFraisScol);

            $ecolageHistory = new PaiementHistory();
            $ecolageHistory->setResourceName(PaiementHistory::ECOLAGE_RESOURCE);
            $ecolageHistory->setStatut($currFraisNextStatut);
            $ecolageHistory->setResourceId($currFraisScol->getId());
            $ecolageHistory->setValidator($this->getUser());
            $ecolageHistory->setCreatedAt(new \DateTime());
            $ecolageHistory->setUpdatedAt(new \DateTime());
            $entityManager->persist($ecolageHistory);
        };

        $entityManager->flush();
        
        return new JsonResponse(array('statut' => '200'));
    }





    /**
     * @IsGranted({"ROLE_COMPTABLE", "ROLE_RF"})
     * @Route("/frais/search", name="front_finance_frais_search", methods={"GET", "POST"})
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
        AnneeUniversitaireService       $anneeUniversitaireService, 
        FraisScolariteManager           $fraisScolariteManager,
        WorkflowEcolageStatutService $workflowEcoStatService
        )
    {
        $user               = $this->getUser();
        $qString = $request->get('q', '');
        $profilListStatut = $workflowEcoStatService->getStatutForListByProfil($user->getRoles());
        $profilNextStatut = $workflowEcoStatService->getResourceNextStatut($profilListStatut);
        $profilPrevStatut = $workflowEcoStatService->getEdtPreviousStatut($profilListStatut);
        $currentAnneUniv = $anneeUniversitaireService->getCurrent();
        
        // Filtrer les données en fonction du statut ici
        $fraisScolarites = $fraisScolariteManager->searchByName(
            $currentAnneUniv->getId(),
            $qString
        );

        return $this->render(
            'frontend/comptable/ecolage/result-search.html.twig',
            [
                'profilListStatut'      => $profilListStatut,
                'profilNextStatut'      => $profilNextStatut,
                'profilPrevStatut'      => $profilPrevStatut,
                'list'      => $fraisScolarites
            ]
        );
    }

    /**
     * @IsGranted({"ROLE_COMPTABLE", "ROLE_RF"})
     * @Route("/vente", name="front_finance_vente_index", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\CalendrierPaiementManager    $calPaiementManager
     * @param \App\Manager\FraisScolariteManager        $fraisScolManager
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     * @param \App\Manager\MentionManager               $mentionManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function vente(
        Request                     $request,
        FraisScolariteManager       $fraisScolManager,
        AnneeUniversitaireService   $anneeUniversitaireService,
        MentionManager              $mentionManager,
        NiveauManager               $niveauManager,
        ParcoursManager             $parcoursManager,
        ExportEcolageService        $ecolageService,
        ParameterBagInterface       $parameter
    ) {
        $actionExport = $request->get('export', '');
        $m = $request->get('m', '');
        $n = $request->get('n', '');
        $p = $request->get('p', '');
        $np = $request->get('np', 1);

        $currentAnneUniv    = $anneeUniversitaireService->getCurrent();
        $ecolages = $fraisScolManager->getCurrentVente($currentAnneUniv->getId(),[
            'mention'   => $m,
            'niveau'    => $n,
            'parcours'  => $p
        ]);

        // dump($ecolages);die; 

        if($actionExport) {
            $etatBankFile = $ecolageService->getEtatVente($ecolages, $currentAnneUniv, $parameter, intval($np));
            return $this->file($etatBankFile);
        }        

        $params = [
            'm'             => $m,
            'n'             => $n,
            'p'             => $p,
            'mentions'      => $mentionManager->loadAll(),
            'niveaux'       => $niveauManager->loadAll(),
            'parcours'      => $parcoursManager->loadBy(['mention' => $m, 'niveau' => $n]),  
            'list'         => $ecolages,
            'anneeUniversitaire' => $currentAnneUniv
        ];

        return $this->render(
            'frontend/comptable/ecolage/vente.html.twig', $params
        );
    }

    /**
     * @IsGranted({"ROLE_COMPTABLE", "ROLE_RF"})
     * @Route("/archives", name="front_finance_archive_index", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\CalendrierPaiementManager    $calPaiementManager
     * @param \App\Manager\FraisScolariteManager        $fraisScolManager
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     * @param \App\Manager\MentionManager               $mentionManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function archiveIndex(
        Request                         $request,
        FraisScolariteManager           $fraisScolManager,
        AnneeUniversitaireManager       $anneeUniversitaireManager,
        AnneeUniversitaireService       $anneeUniversitaireService
    ) {
        $a = $request->get('a', '');
        $currentAnneUniv    = $a ? $anneeUniversitaireManager->load($a) : $anneeUniversitaireService->getCurrent();
        $anneeUnivList      = $anneeUniversitaireManager->loadBy([], ['libelle' => 'DESC']); 
        $ecolages = $fraisScolManager->loadBy(
            [
                'annee_universitaire' => $currentAnneUniv,
                'status'              => WorkflowEcolageStatutService::STATUS_ARCHIVED
            ], 
            ['date_paiement' => 'DESC']
        );

        $params = [
                'a'   => $currentAnneUniv ? $currentAnneUniv->getId() : 0,
                'anneeUnivList'     => $anneeUnivList,
                'list'              => $ecolages,
            ];

        return $this->render(
            'frontend/comptable/ecolage/archive.html.twig', $params
        );
    }


    /**
     * @IsGranted({"ROLE_COMPTABLE", "ROLE_RF"})
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
            'frontend/comptable/etudiant/inscrits.html.twig',
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
     * @Route("/enseignant/{id}/fiche-paie", name="front_finance_fiche_paie")
     * @IsGranted({"ROLE_COMPTABLE", "ROLE_RF"})
     */
    public function downlodFichePaie (
        Request                         $request,
        Enseignant                      $enseignant,
        EnseignantRepository            $enseignantRepo,
        EmploiDuTempsManager            $edtManager,
        CalendrierPaiementManager       $calPaiementManager,
        CalendrierPaiementRepository    $calPaiementRepo,
        AnneeUniversitaireService       $anneeUniversitaireService
    ) {
        $c                      = $request->get('c', '');
        $selectedCalPaiement    = $c? $calPaiementManager->load($c) : $calPaiementRepo->findDefault();
        $currentAnneUniv        = $anneeUniversitaireService->getCurrent();


        // In this case, we want to write the file in the public directory
        $uploadDir = $this->getParameter('enseignants_vacation_directory');
        $subDirectory = $uploadDir . '/' . $currentAnneUniv->getAnnee(). '/' . $enseignant->getId();
        $filename    = 'fiche-paie-' . $selectedCalPaiement->getLibelle() . '.pdf';
        $pdfFilepath = $subDirectory . '/' . $filename;
        if(!is_dir($subDirectory)){
            try {
                $fileSystem = new Filesystem();
                $fileSystem->mkdir($subDirectory);
            } catch(IOExceptionInterface $exception) {
                echo "An error occurred while creating your directory at ".$exception->getPath();
            }            
        }        
        $vacations   = $edtManager->getEnseignantVacation(
            $enseignant->getId(),
            $currentAnneUniv->getId(),
            null,
            $selectedCalPaiement
        );

        $vacPerMatieres = $edtManager->getEnseignantVacationPerMat($vacations);

        // dd($vacPerMatieres);

        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set(array('isRemoteEnabled' => true));
        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        $tPl = 'frontend/teacher/vacation/fiche-paie.html.twig';
        $html = $this->renderView(
            $tPl,
            [
                'calPaiement'          => $selectedCalPaiement,
                'enseignant'    => $enseignant,
                'vacations'    => $vacPerMatieres,
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

        // Write file to the desired path
        file_put_contents($pdfFilepath, $output);

        return $this->file($pdfFilepath);
    }

}