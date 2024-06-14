<?php

namespace App\Controller\Frontend;

use App\Entity\Cours;
use App\Entity\CoursMedia;
use App\Entity\Matiere;
use App\Manager\AnneeUniversitaireManager;
use App\Repository\CalendrierPaiementRepository;
use App\Repository\EnseignantRepository;
use App\Manager\CalendrierPaiementManager;  
use App\Manager\ConcoursConfigManager;
use App\Manager\CoursManager;
use App\Manager\CoursMediaManager;
use App\Manager\CoursSectionManager;
use App\Manager\NotesManager;
use App\Manager\MatiereManager;
use App\Manager\EmploiDuTempsManager;
use App\Repository\NotesRepository;
use App\Repository\EtudiantRepository;
use App\Repository\EtudiantDocumentRepository;
use App\Form\CoursType;
use App\Form\CoursSectionType;
use App\Form\CoursMediaType;
use App\Form\EnseignantType;
use App\Form\MatiereType;
use App\Form\NoteType;
use App\Services\AnneeUniversitaireService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\Common\Collections\ArrayCollection;
use \ZipArchive;

use Dompdf\Dompdf;
use Dompdf\Options;


/**
 * Description of TeacherController.php.
 *
 * @package App\Controller\Frontend
 * @Route("/enseignant")
 */
class TeacherController extends AbstractController
{
    /**
     * @Route("/", name="frontend_teacher_index")
     * @return \Symfony\Component\HttpFoundation\Response
     * @IsGranted("ROLE_ENSEIGNANT")
     */
    public function index(
        Request             $request,
        EnseignantRepository            $enseignantRepo,
        EmploiDuTempsManager            $edtManager,
        AnneeUniversitaireService       $anneeUniversitaireService
    ) {
        $userId             =   $this->getUser()->getId();
        $enseignant             =   $enseignantRepo->findOneBy(['user' => $userId]);
        $currentAnneUniv        = $anneeUniversitaireService->getCurrent();
        $volumeHoraires = $edtManager->getEnseignantVolumeHoraire($currentAnneUniv->getId(), $enseignant->getId());
        // dd($volumeHoraires);
        $resultSpan = [];
        foreach($volumeHoraires as $item) {
            if(!array_key_exists($item['mention'], $resultSpan)) {
                $resultSpan[$item['mention']] = [];
                $resultSpan[$item['mention']]['size'] = 1;
            } else {
                $resultSpan[$item['mention']]['size'] += 1;
            }
            if(!array_key_exists($item['niveau'], $resultSpan[$item['mention']])) {
                $resultSpan[$item['mention']][$item['niveau']] = [];
                $resultSpan[$item['mention']][$item['niveau']]['size'] = 1;
            } else {
                $resultSpan[$item['mention']][$item['niveau']]['size']++;
            }
        }
        // dd($resultSpan);
        return $this->render(
            'frontend/teacher/index.html.twig',
            [
                'list' => $resultSpan,
                'data' => $volumeHoraires
            ]
        );
    }

    /**
     * @Route("/mon-compte", name="front_teacher_me")
     * @IsGranted("ROLE_ENSEIGNANT")
     */
    public function me(
        Request $request,
        EnseignantRepository $enseignantRepo,
        ParameterBagInterface       $parameter,
        UserPasswordEncoderInterface $passwordEncoder
    )
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user            = $this->getUser();
        $enseignant        = $enseignantRepo->findOneBy(['user' => $user->getId()]);
        $form = $this->createForm(
            EnseignantType::class, 
            $enseignant,
            [
                'user' => $user,
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $login = $form->get('login')->getData();
            if(!empty($login))
                $user->setLogin($login);
            $clearPassword = $form->get('password')->getData();
            if (!empty($clearPassword)) {
                $password = $passwordEncoder->encodePassword(
                    $user,
                    $clearPassword
                );
                $user->setPassword($password);
            }
            $entityManager->persist($user);
            

            $cvFile        = $form->get('pathCv')->getData();
            $diplomeFile   = $form->get('pathDiploma')->getData();
            
            $directory     = $parameter->get('enseignants_directory');
            $uploader      = new \App\Services\FileUploader($directory);
            $today         = new \DateTime();
            $fileDirectory = $enseignant->getLastName() . "-" . $today->getTimestamp();
            $uploadDir = $directory . "/" . $fileDirectory;
            if(!is_dir($uploadDir)){
                try {
                    $fileSystem = new Filesystem();
                    $fileSystem->mkdir($uploadDir);
                } catch(IOExceptionInterface $exception) {
                    echo "An error occurred while creating your directory at ".$exception->getPath();
                }
            }
            // Upload file
            if ($cvFile) {
                $cvFileDisplay = $uploader->upload($cvFile, $directory, $fileDirectory);
                $enseignant->setPathCv($fileDirectory . "/" . $cvFileDisplay["filename"]);
            }
            if ($diplomeFile) {
                $diplomeFileDisplay = $uploader->upload($diplomeFile, $directory, $fileDirectory);
                $enseignant->setPathDiploma($fileDirectory . "/" . $diplomeFileDisplay["filename"]);
            }            
            $entityManager->persist($enseignant);
            $entityManager->flush();
            
            return $this->redirectToRoute('frontend_teacher_index');
        }

        return $this->render(
            'frontend/teacher/mon-compte.html.twig',
            [
                'enseignant'    => $enseignant,
                'form'          => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/matieres", name="front_teacher_matieres")
     * @IsGranted("ROLE_ENSEIGNANT")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Repository\EnseignantRepository      $enseignantRepo
     * @param \App\Manager\CoursManager                 $coursManager
     * @param \App\Services\AnneeUniversitaireManager   $anneeUniversitaireManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function matieres(
        Request                     $request,
        EnseignantRepository        $enseignantRepo,
        MatiereManager              $matiereManager,
        AnneeUniversitaireManager   $anneeUniversitaireManager
    ) {
        $userId            = $this->getUser()->getId();
        $enseignant        = $enseignantRepo->findOneBy(['user' => $userId]);
        $currentAnneeUniv  = $anneeUniversitaireManager->getCurrent();
        $ensMatieres    = $matiereManager->getGroupedByEnseignant($enseignant->getId());

        return $this->render(
            'frontend/teacher/matieres.html.twig',
            [
                'enseignantData'  => $ensMatieres,
                'anneeUniv' => $currentAnneeUniv
            ]
        );
    }

    /**
     * @Route("/matiere/{id}/cours/", name="front_teacher_cours", methods={"GET", "POST"})
     * @IsGranted("ROLE_ENSEIGNANT")
     * @param \App\Entity\Matiere                       $matiere
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Repository\EnseignantRepository      $enseignantRepo
     * @param \App\Manager\CoursManager                 $coursManager
     * @param \App\Services\AnneeUniversitaireManager   $anneeUniversitaireManager
     * @param \Knp\Component\Pager\PaginatorInterface   $paginator
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */

    public function cours(
        Matiere                     $matiere,
        Request                     $request,
        EnseignantRepository        $enseignantRepo,
        CoursManager                $coursManager,
        MatiereManager              $matiereManager,
        AnneeUniversitaireManager   $anneeUniversitaireManager,
        PaginatorInterface $paginator
    ) {
        $userId            = $this->getUser()->getId();
        $enseignant        = $enseignantRepo->findOneBy(['user' => $userId]);
        $currentAnneeUniv  = $anneeUniversitaireManager->getCurrent();
        //$enseignantData    = $coursManager->getByMatiere($matiere->getId(), $currentAnneeUniv['id']);
        //dump($enseignantData);die;
        $cours    = $coursManager->loadBy(
            [
                'matiere' => $matiere, 
                'anneeUniversitaire' => $currentAnneeUniv['id']
            ],
            []
        );
        $uploadDir = $this->getParameter('cours_directory');
        $cours = array_map(
            function(Cours $cours) use ($uploadDir) {                
                foreach($cours->getCoursMedia() as $media){
                    $idFilePath = $uploadDir . '/' . $media->getCours()->getId() . '/' . $media->getId() . '.' . $media->getType();
                    $oldFilePath = $uploadDir . '/' . $media->getCours()->getId() . '/' . $media->getName() . '.' . $media->getType();
                    $newFilePath = $uploadDir . '/' . $media->getCours()->getId() . '/' . $media->getPath();
                    if (!is_file($oldFilePath) && !is_file($newFilePath) && !is_file($idFilePath)) $cours->removeCoursMedium($media);
                }
                return $cours;
            },
            $cours
        );

        $pagination = $paginator->paginate(
            $cours, 
            $request->query->getInt('page', 1), 
            3
        );

        $form = $this->createForm(MatiereType::class, $matiere);
        // $form->handleRequest($request);
        if ($request->isMethod('POST')) {
            $formData = $request->request->get('matiere');
            $syllabus = $formData['syllabus'];
            $matiere->setSyllabus($syllabus);
            $matiereManager->save($matiere);
            return $this->redirectToRoute('front_teacher_cours', ['id' => $matiere->getId()]);
        }

        return $this->render(
            'frontend/teacher/cours.html.twig',
            [
                'matiere' => $matiere,
                // 'enseignantData'  => $enseignantData,
                'cours' => $pagination,
                'anneeUniv' => $currentAnneeUniv,
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/cours/{id}/etudiant/document", name="front_teacher_cours_etudiant_doc", methods={"GET", "POST"})
     * @IsGranted("ROLE_ENSEIGNANT")
     * @param \App\Entity\Matiere                       $matiere
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Repository\EnseignantRepository      $enseignantRepo
     * @param \App\Manager\CoursManager                 $coursManager
     * @param \App\Services\AnneeUniversitaireManager   $anneeUniversitaireManager
     * @param \Knp\Component\Pager\PaginatorInterface   $paginator
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */

    public function etudiantDocument(
        Cours                       $cours,
        Request                     $request,
        CoursManager                $coursManager,
        EtudiantDocumentRepository  $etdDocumentRepo,
        AnneeUniversitaireManager   $anneeUniversitaireManager
    ) {
        $userId            = $this->getUser()->getId();
        $currentAnneeUniv  = $anneeUniversitaireManager->getCurrent();
        $etdDocuments    = $etdDocumentRepo->getPerEtudiant($cours->getId());
        if(count($etdDocuments) < 1) return $this->redirectToRoute('front_teacher_cours', ['id' => $cours->getMatiere()->getId()]);
        $fileDirectory = $this->getParameter('students_cours');
        $docEtdArchive = new \ZipArchive();
        //$zipName = "zipFileName.zip";
        $zipName = $cours->getMatiere()->getNom() . ".zip";
        $docEtdArchive->open($zipName, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        foreach($etdDocuments as $item){
            $file = $fileDirectory . '/' . $item['path'];
            $docEtdArchive->addFromString(basename($file),  file_get_contents($file)); 
        }
        $docEtdArchive->close();
        $response = new Response(file_get_contents($zipName));
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $zipName . '"');
        $response->headers->set('Content-length', filesize($zipName));
        unlink($zipName);

        return $response;
    }

    /**
     * Add new cours function
     * @IsGranted("ROLE_ENSEIGNANT")
     * @Route("/matiere/{id}/cours/add", name="front_teacher_add_cours",  methods={"GET", "POST"})
     *
     * @param \App\Entity\Matiere                       $matiere
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\CoursManager                 $coursManager
     * @param \App\Manager\CoursMediaManager            $coursMediaManager
     * @param \Psr\Container\ContainerInterface         $container
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function newCours(
        Matiere $matiere,
        Request $request,
        CoursManager $coursManager,
        CoursMediaManager $coursMediaManager,
        ContainerInterface $container,
        AnneeUniversitaireService $anneeUniversitaireService
    ) : Response {
        /** @var Cours $cours */
        $cours      = $coursManager->createObject();

        $form = $this->createForm(CoursType::class, $cours);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cours->setCreatedAt(new \DateTime());
            $cours->setUpdatedAt(new \DateTime());
            $cours->setAnneeUniversitaire($anneeUniversitaireService->getCurrent());
            $coursManager->save($cours);

            return $this->redirectToRoute('front_teacher_cours', ['id' => $matiere->getId()]);
        }

        return $this->render(
            'frontend/teacher/addCours.html.twig',
            [
                'matiere'   => $matiere,
                'form'      => $form->createView()
            ]
        );
    }

    /**
     * Show cours function
     * @IsGranted("ROLE_ENSEIGNANT")
     * @Route("/matiere/cours/{id}/show", name="front_teacher_show_cours",  methods={"GET", "POST"})
     *
     * @param \App\Entity\Cours                       $cours
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Psr\Container\ContainerInterface         $container
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function showCours(
        Cours $cours,
        Request $request,
        ContainerInterface $container,
        AnneeUniversitaireService $anneeUniversitaireService
    ) : Response {
        
        return $this->render(
            'frontend/teacher/showCours.html.twig',
            [
                'cours'   => $cours
            ]
        );
    }

    /**
     * Edit cours
     * @IsGranted("ROLE_ENSEIGNANT")
     * @Route("/matiere/{matiere_id}/cours/{cours_id}/edit", name="front_teacher_edit_cours",  methods={"GET", "POST"})
     * @ParamConverter("matiere", options={"mapping": {"matiere_id": "id"}})
     * @ParamConverter("cours", options={"mapping": {"cours_id": "id"}})
     *
     * @param \App\Entity\Cours                         $cours
     * @param \App\Entity\Matiere                       $matiere
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\CoursManager                 $coursManager
     * @param \App\Manager\CoursMediaManager            $coursMediaManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function editCours(
        Cours $cours,
        Matiere $matiere,
        Request $request,
        CoursManager $coursManager
    ) : Response {

        $form = $this->createForm(CoursType::class, $cours);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //dd($form->getData()->getCoursMedia());die;
            $cours->setCreatedAt(new \DateTime());
            $cours->setUpdatedAt(new \DateTime());
            $coursManager->save($cours);

            return $this->redirectToRoute('front_teacher_cours', ['id' => $matiere->getId()]);
        }

        return $this->render(
            'frontend/teacher/editCours.html.twig',
            [
                'matiere' => $matiere,
                'cours'   => $cours,
                'form'    => $form->createView()
            ]
        );
    }

    /**
     * @Route("/matiere/cours/{id}/delete", name="front_teacher_delete_cours")
     */
    public function removeCours(Request $request, Cours $cours, CoursManager $manager,Matiere $matiere)
    {
        if ($this->isCsrfTokenValid('delete' . $cours->getId(), $request->request->get('_token'))) {
            dump($cours->getMatiereId()) ;die;
            $manager->delete($cours);
        }

        return $this->redirectToRoute('front_teacher_cours', ['id' => $matiere->getId()]);
    }

    /**
     * @Route("/notes", name="front_teacher_notes")
     * @IsGranted("ROLE_ENSEIGNANT")
     * @param \App\Repository\EnseignantRepository          $enseignantRepo
     * * @param \App\Services\AnneeUniversitaireService     $anneeUniversitaireService
     */
    public function notes(EnseignantRepository $enseignantRepo, MatiereManager $matiereManager)
    {
        $userId     = $this->getUser()->getId();
        $enseignant = $enseignantRepo->findOneBy(['user' => $userId]);
        $matieres   = $matiereManager->getByEnseignant($enseignant->getId());

        return $this->render(
            'frontend/teacher/notes.html.twig',
            [
                'matieres' => $matieres
            ]
        );
    }

    /**
     * Add new cours function
     * @IsGranted("ROLE_ENSEIGNANT")
     * @Route("/matiere/{id}/cours/{c_id}/add/section", name="front_teacher_add_cours_section",  methods={"GET", "POST"})
     * @ParamConverter("cours", options={"mapping": {"c_id": "id"}})
     * @param \App\Entity\Matiere                       $matiere
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\CoursManager                 $coursManager
     * @param \App\Manager\CoursMediaManager            $coursMediaManager
     * @param \App\Manager\ConcoursConfigManager        $concoursConfManager
     * @param \Psr\Container\ContainerInterface         $container
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function newCoursSection(
        Matiere $matiere,
        Cours $cours,
        Request $request,
        CoursSectionManager $manager,        
        ContainerInterface $container,
        ConcoursConfigManager       $concoursConfManager
    ) : Response {
        $concoursConf = $concoursConfManager->loadOneBy([]);
        $currentAnneUniv= $concoursConf->getAnneeUniversitaire();
        $coursSection = $manager->createObject();
        $form = $this->createForm(CoursSectionType::class, $coursSection);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coursSection->setCours($cours);
            $manager->save($coursSection);
            return $this->redirectToRoute('front_teacher_edit_cours', ['matiere_id' => $matiere->getId(), 'cours_id' => $cours->getId()]);
        }

        return $this->render(
            'frontend/teacher/addCoursSection.html.twig',
            [
                'matiere'   => $matiere,
                'cours'     => $cours,
                'form'      => $form->createView()
            ]
        );
    }

    /**
     * Add new notes function
     * @IsGranted("ROLE_ENSEIGNANT")
     * @Route("/matiere/{id}/notes/add", name="front_teacher_add_notes",  methods={"GET", "POST"})
     *
     * @param \App\Entity\Matiere                       $matiere
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\NotesManager                 $notesManager
     * @param \App\Repository\EnseignantRepository      $enseignantRepo
     * @param \App\Repository\EtudiantRepository        $etudiantRepo
     * @param \App\Manager\AnneeUniversitaireManager    $anneeUniversitaireManager
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function newNnotes(
        Matiere $matiere,
        Request $request,
        NotesManager $notesManager,
        EnseignantRepository $enseignantRepo,
        EtudiantRepository $etudiantRepo,
        AnneeUniversitaireManager $anneeUniversitaireManager,
        AnneeUniversitaireService $anneeUniversitaireService
    ) : Response {
        $userId     = $this->getUser()->getId();
        $enseignant = $enseignantRepo->findOneBy(['user' => $userId]);
        $matiereUE  = $matiere->getUniteEnseignements();
        $anneeUniversitaire         = $anneeUniversitaireService->getCurrent();
        $etudiants  = $notesManager->getByMatiereEnseignant($enseignant->getId(), $matiere->getId(), $anneeUniversitaire->getId());
        $form       = $this->createForm(NoteType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $notes = $request->get('note');
            foreach ($notes['etudiant'] as $etudiantId => $noteVal) {

                //check if etudiant note exist
                $etudiant = $etudiantRepo->find($etudiantId);

                /** @var \App\Entity\Notes $note */
                $note = $notesManager->loadOneBy(
                    [
                        'etudiant' => $etudiant,
                        'matiere'  => $matiere,
                        'anneeUniversitaire' => $anneeUniversitaire
                    ]
                );
                if (!$note) {
                    $note = $notesManager->createObject();
                    $note->setAnneeUniversitaire($anneeUniversitaire);
                }

                if ($noteVal['note'] !== "") {
                    if (
                        !$note->getNote() &&
                        (intval($noteVal['note']) === 0 || $noteVal['note'] > 0)
                    ) {
                        $note->setNote($noteVal['note']);
                        $note->setUser($this->getUser());
                    }
                    if (
                        !$note->getRattrapage() &&
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

            return $this->redirectToRoute('front_teacher_notes');
        }

        return $this->render(
            'frontend/teacher/addNote.html.twig',
            [
                'etudiants' => $etudiants,
                'matiere'   => $matiere,
                'form'      => $form->createView()
            ]
        );
    }

    /**
     * Add new notes function
     * @IsGranted("ROLE_ENSEIGNANT")
     * @Route("/matiere/{id}/notes/show", name="front_teacher_show_notes",  methods={"GET", "POST"})
     *
     * @param \App\Entity\Matiere                       $matiere
     * @param \App\Manager\NotesRepository              $notesRepo
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showNnotes(
        Matiere $matiere,
        Request $request,
        EnseignantRepository $enseignantRepo,
        NotesManager $notesManager,
        AnneeUniversitaireManager $anneeUniversitaireManager,
        AnneeUniversitaireService $anneeUniversitaireService
    ) : Response {
        $userId            = $this->getUser()->getId();
        $enseignant        = $enseignantRepo->findOneBy(['user' => $userId]);
        
        $anneeUniv         = $anneeUniversitaireManager->loadBy([], ['annee' => 'DESC']);
        $currentAnneeUniv  = $anneeUniversitaireService->getCurrent();
        $anneeUnivId       = $request->get('au', $currentAnneeUniv->getId());
        $notes             = $notesManager->getByMatiereEnseignant(
            $enseignant->getId(), $matiere->getId(), $anneeUnivId
        );

        return $this->render(
            'frontend/teacher/showNotes.html.twig',
            [
                'matiere'   => $matiere,
                'notes'     => $notes,
                'anneeUniv' => $anneeUniv,
                'au'        => $anneeUnivId
            ]
        );
    }

    /**
     * @Route("/identification", name="front_teacher_login")
     */
    public function login()
    {
        return $this->render(
            'frontend/common/login.html.twig',
            [
                'entity'     => 'enseignant',
                'espacename' => 'Espace enseignant'
            ]
        );
    }

    /**
     * @Route("/edt", name="front_teacher_edt")
     * @IsGranted("ROLE_ENSEIGNANT")
     */
    public function edt(
            EnseignantRepository $enseignantRepo, 
            MatiereManager $matiereManager,
            EmploiDuTempsManager $emploiDuTempsManager,
            AnneeUniversitaireService $anneeUniversitaireService)
    {
        $userId             =   $this->getUser()->getId();
        $enseignant         =   $enseignantRepo->findOneBy(['user' => $userId]);
        $enseignantMentions =   $enseignant->getEnseignantMentions();
        $currentAnneUniv    =   $anneeUniversitaireService->getCurrent();

        $enseignantMatiere = $matiereManager->loadby(['enseignant' => $enseignant]);
        $edtDay   = [];
        $edtWeek  = [];
        $edtDay = $emploiDuTempsManager->getByMatiereOnCurrentDate($enseignantMatiere, $currentAnneUniv);
        $edtWeek = $emploiDuTempsManager->getByMatiereOnCurrentWeek($enseignantMatiere, $currentAnneUniv);

        return $this->render(
            'frontend/teacher/edt.html.twig',
            [
                'currentWeek' => $edtWeek,
                'edtDay'      => $edtDay
            ]
        );
    }

    /**
     * @Route("/download/{id}", name="teacher_download_classes", methods={"GET"})
     * @param \App\Entity\CoursMedia $coursMedia
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download(CoursMedia $coursMedia)
    {
        $uploadDir = $this->getParameter('cours_directory');
        $file = "";

        $fileIdPath = $uploadDir . '/' . $coursMedia->getCours()->getId() . '/' . $coursMedia->getId() . '.' . $coursMedia->getType();

        $oldFilePath = $uploadDir . '/' . $coursMedia->getCours()->getId() . '/' . $coursMedia->getName() . '.' . $coursMedia->getType();
        
        $newFilePath = $uploadDir . '/' . $coursMedia->getCours()->getId() . '/' . $coursMedia->getPath();

        if(is_file($fileIdPath))
            $file = $fileIdPath;
        else if(is_file($oldFilePath))
            $file = $oldFilePath;
        else if(is_file($newFilePath))
            $file = $newFilePath;

        return $file ? $this->file($file) : "";
    }

    /**
     * @Route("/vacations", name="front_teacher_vacation", methods={"GET"})
     * @IsGranted("ROLE_ENSEIGNANT")
     */
    public function vacation(
            Request                         $request, 
            EnseignantRepository            $enseignantRepo,
            EmploiDuTempsManager            $edtManager,
            CalendrierPaiementManager       $calPaiementManager,
            CalendrierPaiementRepository    $calPaiementRepo,
            AnneeUniversitaireService       $anneeUniversitaireService)
    {
        $c                      = $request->get('c', '');
        $selectedCalPaiement    = $c? $calPaiementManager->load($c) : $calPaiementRepo->findDefault();
        $userId             =   $this->getUser()->getId();
        $enseignant             =   $enseignantRepo->findOneBy(['user' => $userId]);
        $calPaiements           = $calPaiementManager->loadAll();
        $currentAnneUniv        = $anneeUniversitaireService->getCurrent();
        $fPresenceEnseignants   = $edtManager->getEnseignantVacation(
            $enseignant->getId(),
            $currentAnneUniv->getId(),
            null,
            $selectedCalPaiement
        );

        return $this->render(
            'frontend/teacher/vacation/index.html.twig',
            [
                'c'                     => $selectedCalPaiement->getId(),
                'enseignant'            => $enseignant,
                'calPaiements'          => $calPaiements,
                'fPresenceEnseignants'  => $fPresenceEnseignants
            ]
        );
    }

    /**
     * @Route("/fiche-paie", name="front_teacher_fiche_paie")
     * @IsGranted("ROLE_ENSEIGNANT")
     */
    public function downlodFichePaie (
        Request             $request,
        EnseignantRepository            $enseignantRepo,
        EmploiDuTempsManager            $edtManager,
        CalendrierPaiementManager       $calPaiementManager,
        CalendrierPaiementRepository    $calPaiementRepo,
        AnneeUniversitaireService       $anneeUniversitaireService
    ) {
        $c                      = $request->get('c', '');
        $selectedCalPaiement    = $c? $calPaiementManager->load($c) : $calPaiementRepo->findDefault();
        $userId             =   $this->getUser()->getId();
        $enseignant             =   $enseignantRepo->findOneBy(['user' => $userId]);
        $currentAnneUniv        = $anneeUniversitaireService->getCurrent();
        
        $vacations   = $edtManager->getEnseignantVacation(
            $enseignant->getId(),
            $currentAnneUniv->getId(),
            null,
            $selectedCalPaiement
        );
        $vacPerMatieres = $edtManager->getEnseignantVacationPerMat($vacations);
        // In this case, we want to write the file in the public directory
        $uploadDir = $this->getParameter('enseignants_vacation_directory');
        $subDirectory = $uploadDir . '/' . $currentAnneUniv->getAnnee(). '/' . $enseignant->getId();
        if(!is_dir($subDirectory)){
            try {
                $fileSystem = new Filesystem();
                $fileSystem->mkdir($subDirectory);
            } catch(IOExceptionInterface $exception) {
                echo "An error occurred while creating your directory at ".$exception->getPath();
            }            
        }
        $filename    = 'fiche-paie-' . $selectedCalPaiement->getLibelle() . '.pdf';
        $pdfFilepath = $subDirectory . '/' . $filename;

        //dd($vacPerMatieres);

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