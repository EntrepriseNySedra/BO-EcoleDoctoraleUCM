<?php

namespace App\Controller\Frontend;

use App\Entity\CalendrierExamen;
use App\Entity\CalendrierSoutenance;
use App\Entity\CalendrierSoutenanceHistorique;
use App\Entity\Concours;
use App\Entity\ConcoursCandidature;
use App\Entity\EmploiDuTemps;
use App\Entity\ExtraNote;
use App\Entity\FichePresenceEnseignant;
use App\Entity\Mention;
use App\Entity\Salles;
use App\Entity\User;
use App\Entity\Enseignant;
use App\Entity\EnseignantMatiere;
use App\Entity\DemandeDoc;
use App\Entity\Matiere;
use App\Form\ConcoursNotesFormType;
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
use App\Manager\AnneeUniversitaireManager;
use App\Manager\CalendrierExamenHistoriqueManager;
use App\Manager\CalendrierExamenManager;
use App\Manager\CalendrierSoutenanceHistoriqueManager;
use App\Manager\CalendrierSoutenanceManager;
use App\Manager\ConcoursConfigManager;
use App\Manager\ConcoursManager;
use App\Manager\ConcoursCandidatureManager;
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
use App\Manager\SallesManager;
use App\Manager\SemestreManager;
use App\Manager\UserManager;
use App\Manager\DepartementManager;
use App\Manager\ProfilManager;
use App\Manager\UniteEnseignementsManager;
use App\Manager\EtudiantManager;
use App\Manager\ParcoursManager;
use App\Manager\DemandeDocManager;
use App\Repository\EmploiDuTempsRepository;
use App\Repository\EnseignantRepository;
use App\Repository\EtudiantRepository;
use App\Repository\MatiereRepository;

use App\Services\AnneeUniversitaireService;
use App\Services\NotesService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Container\ContainerInterface;

use Ramsey\Uuid\Uuid;


use Dompdf\Dompdf;
use Dompdf\Options;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;

/**
 * Description of DoyenController.php.
 *
 * @package App\Controller\Frontend
 * @Route("/doyen")
 */
class DoyenController extends AbstractController
{

    /** @var array $commonParams */
    static $commonParams = [
        'workspaceTitle'      => 'Doyen',
        'edtPath'             => 'front_doyen_gestion_emploi_du_temps',
        'noteIndex'           => 'front_doyen_enseignant',
        'noteEdit'            => 'front_doyen_manage_notes',
        'calendarIndex'       => 'front_doyen_examen_calendar_list',
        'thesisCalendarIndex' => 'front_doyen_thesis_calendar_list',
        'extraNoteIndex'      => 'front_doyen_extranote_list',
        'extraNoteEdit'       => 'front_doyen_extranote_edit',
        'resultConcoursPath'  => 'front_doyen_concours_result',
        'validateResultConcoursPath'  => 'front_doyen_validate_concours_result',
        'concoursCandidatNotesPath'  => 'front_doyen_concours_candidate_notes',
        'validateExamenNotePath'  => 'front_doyen_examen_note'

    ];

    /**
     * @Route("/gestion-emploi-temps", name="front_doyen_gestion_emploi_du_temps")
     * @IsGranted("ROLE_DOYEN")
     * @param \App\Manager\EmploiDuTempsManager       $emploiDuTempsManager
     * @param \App\Services\AnneeUniversitaireService $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function edtList(
        EmploiDuTempsManager $emploiDuTempsManager,
        AnneeUniversitaireService $anneeUniversitaireService
    ) {
        /** @var User $user */
        $user          = $this->getUser();
        $emploiDuTemps = $emploiDuTempsManager->getByMentionAndOrCollegeYear(
            $anneeUniversitaireService->getCurrent(), $user->getMention()
        );

        $params = [
            'emploiDuTemps' => $emploiDuTemps
        ];

        $params = array_merge($params, self::$commonParams);

        return $this->render(
            'frontend/chef-mention/edtList.html.twig',
            $params
        );
    }

    /**
     * @Route("/notes/{id}", name="front_doyen_notes")
     * @IsGranted("ROLE_DOYEN")
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
        $mentions = [];

        /** @var Mention $mention */
        foreach ($this->getUser()->getFaculte()->getMentions() as $mention) {
            $mentions[] = $mention->getId();
        }

        $teachersMention = $enseignantManager->getByMentions($mentions);

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
     * @Route("/notes", name="front_doyen_enseignant")
     * @IsGranted("ROLE_DOYEN")
     * @param \App\Manager\EnseignantManager $enseignantManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function enseignants(EnseignantManager $enseignantManager)
    {
        $mentions = [];

        /** @var Mention $mention */
        foreach ($this->getUser()->getFaculte()->getMentions() as $mention) {
            $mentions[] = $mention->getId();
        }

        $teachersMention = $enseignantManager->getByMentions($mentions);

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
     * @IsGranted("ROLE_DOYEN")
     * @Route("/notes/{teacher_id}/{matiere_id}/add", name="front_doyen_manage_notes",  methods={"GET", "POST"})
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

            return $this->redirectToRoute('front_doyen_notes', ['id' => $enseignant->getId()]);
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
     * @Route("/calendrier-examen", name="front_doyen_examen_calendar_list", methods={"GET"})
     * @IsGranted("ROLE_DOYEN")
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
     * @Route("/calendrier-soutenances", name="front_doyen_thesis_calendar_list", methods={"GET"})
     * @IsGranted("ROLE_DOYEN")
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
     * @IsGranted("ROLE_DOYEN")
     * @Route("/extra-note", name="front_doyen_extranote_list", methods={"GET"})
     * @param \App\Manager\NiveauManager $niveaumanager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function extraNoteList(NiveauManager $niveaumanager)
    {
        $mentions = $this->getUser()->getFaculte()->getMentions();
        $niveaux  = $niveaumanager->loadBy([], ['libelle' => 'ASC']);

        $params = [
            'mentions' => $mentions,
            'niveaux'  => $niveaux,
        ];
        $params = array_merge($params, self::$commonParams);

        return $this->render(
            'frontend/recteur/notes/extranote-list.html.twig',
            $params
        );
    }

    /**
     * @IsGranted("ROLE_DOYEN")
     * @Route("/extra-note/{mention_id}/{note_id}/edit", name="front_doyen_extranote_edit", methods={"GET", "POST"})
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
                'status'  => ExtraNote::$doyenStatusList
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

            return $this->redirectToRoute('front_doyen_extranote_list', ['m' => $mention->getId()]);
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
     * @IsGranted("ROLE_DOYEN")
     * @Route("/concours/result/index", name="front_doyen_concours_result", methods={"GET", "POST"})
     * @param \App\Manager\ConcoursManager              $concoursManager
     * @param \App\Manager\ConcoursNotesManager         $notesManager
     * @param \App\Serive\AnneeUniversitaireService     $anneeUniversitaireService
     * @param \App\Manager\ParcoursManager              $parcoursManager
     * @param \App\Manager\ConcoursConfigManager $concoursConfManager
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function ShowConcoursResult(
        Request $request,
        MentionManager $mentionManager,
        ConcoursManager $concoursManager,
        ParcoursManager $parcoursManager,
        ConcoursNotesManager $notesManager,
        ConcoursConfigManager $concoursConfManager
    ) {
        $user = $this->getUser();
        $selectedM       = $request->get('m');
        $selectedC       = $request->get('c');
        $selectedP       = $request->get('p');  
        $concoursConf = $concoursConfManager->loadOneBy([]);
        $currentAnneUniv = $concoursConf->getAnneeUniversitaire();

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

        $mentions = $user->getFaculte()->getMentions();
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
                Concours::STATUS_VALIDATED_RECTEUR,
                NULL,
                NULL,
                ConcoursCandidature::RESULT_ADMITTED
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

        return $this->render('frontend/doyen/concours/result-list.html.twig', $params);
    }

    /**
     * @IsGranted("ROLE_DOYEN")
     * @Route("/concours/result/validate/{id}", name="front_doyen_validate_concours_result", methods={"POST"})
     * @param \App\Entity\Concours                      $concours
     * @param \App\Manager\ConcoursManager              $concoursManager
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function validateConcoursResult(Concours $concours, ConcoursManager $concoursManager, Request $request)
    {      
        $concours->setResultStatut(Concours::STATUS_VALIDATED_DOYEN);
        $concoursManager->save($concours);

        return $this->redirectToRoute('front_doyen_concours_result');
    }

    /**
     * @Route("/concours/notes/{id}", name="front_doyen_concours_candidate_notes", methods={"GET", "POST"})
     * @IsGranted("ROLE_DOYEN")
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
            'frontend/doyen/concours/notes.html.twig',
            $params
        );
    }

    /**
     * @IsGranted("ROLE_DOYEN")
     * @Route("/examen/note/index", name="front_doyen_examen_note", methods={"GET", "POST"})
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
        // $mention    = $user->getMention();
        $selectedM = $request->get('m', 0);
        $selectedN = $request->get('n', 0);
        $selectedP = $request->get('p', 0);
        $currentAnneUniv = $anneeUniversitaireService->getCurrent();

        $mentions   = $user->getFaculte()->getMentions();
        $niveaux    = $niveauManager->loadBy([], ['libelle' => 'ASC']);
        $parcours   = $parcoursManager->loadBy(
            [
                'mention'   => $selectedM,
                'niveau'    => $selectedN
            ], 
            ['nom' => 'ASC']
        );

        $resultats        = [];
        $selectedConcours = '';
        $form = '';

        $notes = $notesManager->getClassNotes(
            $selectedM, 
            $selectedN, 
            $selectedP,
            $currentAnneUniv->getId()
        );
        $resultats = $noteService->getClassExamenNote($notes);

        $urlParams = [
                'n'         => $selectedN,
                'p'         => $selectedP,
                'm'         => $selectedM,
                'mentions'  => $mentions,
                'niveaux'   => $niveaux,
                'parcours'  => $parcours,
                'resultats' => $resultats
            ];

        $params = array_merge($urlParams, self::$commonParams);

        // dump($resultats);die;
        return $this->render(
            'frontend/doyen/examen/result-list.html.twig',
            $params
        );
    }

}