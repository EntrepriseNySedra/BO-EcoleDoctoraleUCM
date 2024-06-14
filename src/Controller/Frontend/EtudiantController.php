<?php

namespace App\Controller\Frontend;

use App\Form\ConcoursCandidatureFormType;
use App\Form\DemandeType;
use App\Form\EtudiantDocumentType;
use App\Form\EtudiantInfoFormType;
use App\Form\FirstInscriptionFormType;
use App\Form\FraisScolariteType;
use App\Form\InscriptionFormType;

use App\Entity\Cours;
use App\Entity\CoursMedia;
use App\Entity\ConcoursCandidature;
use App\Entity\CalendrierUniversitaire;
use App\Entity\DemandeDoc;
use App\Entity\Ecolage;
use App\Entity\Etudiant;
use App\Entity\EtudiantDocument;
use App\Entity\FraisScolarite;
use App\Entity\Inscription;
use App\Entity\PaiementHistory;

use App\Manager\ConcoursCandidatureManager;
use App\Manager\ConcoursManager;
use App\Manager\ConcoursNotesManager;
use App\Manager\DemandeDocManager;
use App\Manager\EcolageManager;
use App\Manager\InscriptionManager;
use App\Manager\SemestreManager;
use App\Manager\UniteEnseignementsManager;
use App\Manager\CoursManager;
use App\Manager\CoursMediaManager;
use App\Manager\ExamensManager;
use App\Manager\MatiereManager;
use App\Manager\AbsencesManager;
use App\Manager\EtudiantManager;
use App\Manager\FraisScolariteManager;
use App\Manager\NotesManager;
use App\Manager\EmploiDuTempsManager;
use App\Manager\ParcoursManager;
use App\Manager\CalendrierUniversitaireManager;
use App\Manager\SallesManager;
use App\Manager\UserManager;

use App\Repository\EtudiantRepository;
use App\Repository\EtudiantDocumentRepository;
use App\Repository\EmploiDuTempsRepository;
use App\Repository\FraisScolariteRepository;
use App\Repository\CoursRepository;
use App\Repository\MatiereRepository;
use App\Repository\DemandeDocRepository;


use App\Services\AnneeUniversitaireService;
use App\Services\EcolageService;
use App\Services\EtudiantService;
use App\Services\Mailer;
use App\Services\NotesService;
use App\Services\UtilFunctionService;

use Dompdf\Dompdf;
use Dompdf\Options;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\KernelInterface;

use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

use Psr\Log\LoggerInterface;

/**
 * Class EtudiantController
 * @Route("/etudiant")
 *
 * @package App\Controller\Frontend
 */
class EtudiantController extends EtudiantBaseController
{

    private $ecolageDenied = false;

    /**
     * @Route("/ecolage/denied", name="front_student_ecolage_denied")
     */
    public function ecolageDenied()
    {
        return $this->render(
            'frontend/etudiant/ecolage-denied.html.twig'
        );
    }

    /**
     * @Route("/identification", name="front_student_login")
     */
    public function login()
    {
        return $this->render(
            'frontend/etudiant/login.html.twig'
        );
    }

    /**
     * @Route("/mon-compte", name="front_student_me")
     * @IsGranted("ROLE_ETUDIANT")
     */
    public function me(
        Request $request,

        EtudiantManager $etudiantManager,
        InscriptionManager $inscriptionManager,
        AnneeUniversitaireService $anneeUniversitaireService,
        EcolageService              $ecoService,
        UserPasswordEncoderInterface $passwordEncoder,
        EtudiantService        $etudiantService
    )
    {
        $entityManager = $this->getDoctrine()->getManager();
        $userId = $this->getUser()->getId();
        $student = $etudiantManager->loadOneBy(['user' => $userId]);
    
        if (!$student) {
            throw $this->createNotFoundException('Student not found for the current user.');
        }

        if ($check = $this->checkInscription($student, $inscriptionManager, $anneeUniversitaireService, $ecoService)) {
            return $check;
        }

        $form = $this->createForm(
            EtudiantInfoFormType::class,
            $student,
            [
                'em' => $entityManager,
            ]
            
        );
        $oldStudentInfo = clone($student);
        // dd($oldStudentInfo);
        $cinNum = $form->get('cinNum')->getData();
        //var_dump($cinNum);die;
        if ($cinNum !== null) {
            $student->setCinNum($cinNum);
        }
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            // dd($oldStudentInfo);

            $login = $form->get('login')->getData();
            $cinNum = $form->get('cinNum')->getData();
          
            // Check if $cinNum is null and handle accordingly
            if ($cinNum !== null) {
                $student->setCinNum($cinNum);
            }
            if (!empty($login)) {
                $student->getUser()->setLogin($login);
            }

            $clearPassword = $form->get('password')->getData();
            if (!empty($clearPassword)) {
                $password = $passwordEncoder->encodePassword(
                    $student->getUser(),
                    $clearPassword
                );
                $student->getUser()->setPassword($password);
            }
            $student->setMention($oldStudentInfo->getMention());
            $student->setNiveau($oldStudentInfo->getNiveau());
            $student->setParcours($oldStudentInfo->getParcours());
            if(!$student->getImmatricule()) {
                $etudiantService->setMatricule($student);                
            }
            $entityManager->persist($student->getUser());
            $entityManager->persist($student);
            $entityManager->flush();

            return $this->redirectToRoute('front_student_classes');
        }

        return $this->render(
            'frontend/etudiant/mon-compte.html.twig',
            [
                'student' => $student,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/mon-modif_information", name="modif_information")
     * @IsGranted("ROLE_ETUDIANT")
     */
   /* public function updateEtudiant(
        Request $request,

        EtudiantManager $etudiantManager,
        InscriptionManager $inscriptionManager,
        AnneeUniversitaireService $anneeUniversitaireService,
        EcolageService              $ecoService,
        EtudiantEntity $etudiantentity,
        UserPasswordEncoderInterface $passwordEncoder
    )
    {

        /*$student = $this->getUser(); 
        

        $form = $this->createForm(FirstInscriptionFormType::class, $student);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            dd($entityManager);die;
            $entityManager->flush();

            return $this->redirectToRoute('front_student_classes');
        }


        $studentData = $this->getUser(); // Replace with your method to fetch student data

        $etudiantentity->setFirstName($studentData['firstName']); // Replace with actual property and key
        dd($etudiantentity);die;


        $form = $this->createForm(FirstInscriptionFormType::class, $student);

        // Handle form submission
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Persist the changes to the database
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            // Redirect the user to a success page or wherever appropriate
            return $this->redirectToRoute('front_student_classes');
        }

        // Render the form template
        return $this->render('your_template/mon_compte.html.twig', [
            'form' => $form->createView(),
        ]);
    }*/

    /**
     * @Route("/mes-cours", name="front_student_classes")
     * @IsGranted("ROLE_ETUDIANT")
     */
    public function classes(
        Request $request,
        MatiereManager $matiereManager,
        CoursRepository $coursRepository,
        MatiereRepository $matiereRepository,
        CoursMediaManager $coursMediaManager,
        UniteEnseignementsManager $ueManager,
        EtudiantManager $etudiantManager,
        InscriptionManager $inscriptionManager,
        AnneeUniversitaireService $anneeUniversitaireService,
        EcolageService $ecoService
    ) {        
        $userId         = $this->getUser()->getId();
        $student        = $etudiantManager->loadOneBy(['user' => $userId]);        
        if ($student && $check=$this->checkInscription($student, $inscriptionManager, $anneeUniversitaireService, $ecoService)) {
            return $check;
        }
        if (!$student) { // not registered
            return $this->redirectToRoute('front_student_first_inscription');
        }
        $studentNiveau  = $student->getNiveau();
        $studentMention = $student->getMention();
        $studentParcours= $student->getParcours();
        $uniteEns       = $ueManager->loadBy([], ['libelle' => 'ASC']);
        $currentAnneUniv = $anneeUniversitaireService->getCurrent();
        $matieres       = $matiereRepository->findAllByUserLevel($studentNiveau, $studentMention, $currentAnneUniv, $studentParcours);
        
        $result = [];
        foreach ($matieres as $matiere) {
            $resultItem = [];
            $resultKey  = $matiere->getUniteEnseignements()->getId();
            if (!array_key_exists($resultKey, $result)) {
                $result[$resultKey] = [];
            }
            $result[$resultKey]["UE"]        = $matiere->getUniteEnseignements()->getLibelle();
            $result[$resultKey]["MATIERE"][] = [$matiere->getId(), $matiere->getNom()];
            foreach ($matiere->getCours() as $cours) {
                $courId                                    = $cours->getId();
                $matiereId                                 = $matiere->getId();
                $result[$resultKey]["COURS"][$matiereId][] = [
                    $cours->getId(),
                    $cours->getLibelle(),
                    $cours->getCoursMedia()
                ];
                if ($matiere->getEnseignant()) {
                    $matiereEnseignant = $matiere->getEnseignant()->getFirstName() . " " . $matiere->getEnseignant(
                        )->getLastName();
                } else {
                    $matiereEnseignant = "";
                }
                $result[$resultKey]["ENSEIGNANT"][$matiereId] = $matiereEnseignant;
            }
        }

        //dd($result);die;

        return $this->render(
            'frontend/etudiant/classes.html.twig',
            [
                'uniteEns' => $uniteEns,
                'result'   => $result
            ]
        );
    }

    /**
     * Show cours function
     * @IsGranted("ROLE_ETUDIANT")
     * @Route("/matiere/cours/{id}/show", name="front_student_show_cours",  methods={"GET", "POST"})
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
        EtudiantDocumentRepository $edtRepo,
        EtudiantManager $etudiantManager,
        AnneeUniversitaireService $anneeUniversitaireService
    ) {
        $student = $etudiantManager->loadOneBy(['user' => $this->getUser()]);

        $courDocuments= $edtRepo->findBy(['cours'=> $cours , 'etudiant'=> $student]);
        //dd($user);
        //dd($courDocuments);
        return $this->render(
            'frontend/etudiant/showCours.html.twig',
            [
                'cours'   => $cours,
                'coursdoc'  => $courDocuments
            ]
        );
    }

    /**
     * @param \App\Entity\Etudiant            $student
     * @param \App\Manager\InscriptionManager $inscriptionManager
     * @param \App\Service\AnneeUniversitaireService $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function checkInscription($student, InscriptionManager $inscriptionManager, AnneeUniversitaireService $anneeUniversitaireService, EcolageService $ecoService)
    {
        $anneeUniversitaire = $anneeUniversitaireService->getCurrent();
        /** @var Inscription $inscription */
        $inscriptionStatus = $inscriptionManager->getByStudentIdAndCollegeYear($student->getId(), $anneeUniversitaire->getId());
        if (!$inscriptionStatus || $inscriptionStatus != Inscription::STATUS_VALIDATED) {
            return $this->redirectToRoute('app_logout');
        }
        $ecoService->updateEtudiantStatus([$student->getId()]);
        if($student->getStatus() == Etudiant::STATUS_DENIED_ECOLAGE) {
            return $this->redirectToRoute('front_student_ecolage_denied');
        }

        return null;
    }

    /**
     * @Route("/nouvelle-inscription", name="front_student_first_inscription", methods={"GET", "POST"})
     * @IsGranted("ROLE_ETUDIANT")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Services\EtudiantService             $etudiantService
     * @param \App\Manager\ConcoursNotesManager         $concoursNoteManager
     * @param \App\Manager\ConcoursCandidatureManager   $concoursCandidatureManager
     * @param \App\Manager\EtudiantManager              $etudiantManager
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     * @param \App\Manager\UserManager                  $userManager
     * @param \App\Manager\ConcoursNotesManager         $notesManager
     * @param \App\Manager\ConcoursManager              $concoursManager
     * @param \App\Services\Mailer                      $mailer
     * @param \Psr\Log\LoggerInterface                  $logger
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function inscriptionCycle1(
        Request $request,
        EtudiantService        $etudiantService,
        ConcoursNotesManager $concoursNoteManager,
        ConcoursCandidatureManager $concoursCandidatureManager,
        EtudiantManager $etudiantManager,
        AnneeUniversitaireService $anneeUniversitaireService,
        UserManager $userManager,
        ConcoursNotesManager $notesManager,
        ConcoursManager $concoursManager,
        EcolageService $ecoService,
        FraisScolariteManager $fsManager,
        Mailer $mailer,
        LoggerInterface $logger
    ) {
        $user = $this->getUser();
        $userId  = $user->getId();
        $anneeUniversitaire = $anneeUniversitaireService->getCurrent();
        $student = $etudiantManager->loadOneBy(['user' => $userId]);
        if ($student) {
            return $this->redirectToRoute('front_student_classes');
        }
        /** @var \App\Entity\ConcoursCandidature $candidate */
        $candidate  = $concoursCandidatureManager->loadOneBy(['email' => $user->getEmail(), 'anneeUniversitaire' => $anneeUniversitaire, 'resultat' => ConcoursCandidature::RESULT_ADMITTED]);

        $mention    = $candidate->getMention();
        $niveau     = $candidate->getNiveau();
        $candParcours   = $candidate->getParcours();
        $mentions[] = $mention;
        $niveaux[]  = $niveau;
        $parcours = [];
        if($candParcours)
            $parcours[] = $candParcours;

        /** @var \App\Entity\ConcoursNotes $concoursNote */
        // $concoursNote = $concoursNoteManager->loadOneBy(['concoursCandidature' => $candidate]);
        // if (!$concoursNote) {
        //     return $this->redirectToRoute('app_logout');
        // }
        // $concours = $concoursNote->getConcours();
        // $results = $notesManager->getCandidateResultByConcours($candidate->getId(), $concours, $mention);
        // if (!$concours || count($results) < 1) {
        //     return $this->redirectToRoute('app_logout');
        // }
        /** @var \App\Entity\Etudiant $student */
        $student = $etudiantManager->createObject();
        $form    = $this->createForm(FirstInscriptionFormType::class, $student,
            [
                'em' => $this->getDoctrine()->getManager(),
                'candidate' => $candidate
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $cv                 = $form->get('cv')->getData();
            $lettreMotivation   = $form->get('lettreMotivation')->getData();
            $lettrePresentation = $form->get('lettrePresentation')->getData();
            $photo1             = $form->get('photo1')->getData();
            $photo2             = $form->get('photo2')->getData();
            $certMedical        = $form->get('certMedical')->getData();
            $acteNaissance      = $form->get('acteNaissance')->getData();
            $baccFile           = $form->get('baccFile')->getData();
            $cinFile            = $form->get('cinFile')->getData();
            $autreDocFichier    = $form->get('autreDocFichier')->getData();

            $payementRefFile = $form->get('payementRefPath')->getData();
            $studentDirectory     = $this->getParameter('students_directory');
            $studentUploader      = new \App\Services\FileUploader($studentDirectory);
            $today         = new \DateTime();
            $insFileDirectory = 'inscription/' .  $anneeUniversitaire->getAnnee(). '/' . $mention . '/' . $niveau->getCode() . '/' . UtilFunctionService::seoFriendlyUrl($candidate->getFirstName() . "-" . $candidate->getLastName(
                ));
            $insUploadDir = $studentDirectory . '/' . $insFileDirectory;            

            // Upload file
            if ($cv) {
                $cvDisplay = $uploader->upload($cv, $insUploadDir, $insFileDirectory, false);
                $student->setCv($insFileDirectory . "/" . $cvDisplay["filename"]);
            }
            if ($lettreMotivation) {
                $lettreMotivationDisplay = $uploader->upload($lettreMotivation, $insUploadDir, $insFileDirectory, false);
                $student->setLettreMotivation($insFileDirectory . "/" . $lettreMotivationDisplay["filename"]);
            }
            if ($lettrePresentation) {
                $lettrePresentationDisplay = $uploader->upload($lettrePresentation, $insUploadDir, $insFileDirectory, false);
                $student->setLettrePresentation($insFileDirectory . "/" . $lettrePresentationDisplay["filename"]);
            }
            if ($photo1) {
                $photo1Display = $uploader->upload($photo1, $insUploadDir, $insFileDirectory, false);
                $student->setPhoto1($insFileDirectory . "/" . $photo1Display["filename"]);
            }
            // if ($photo2) {
            //     $photo2Display = $uploader->upload($photo2, $directory, $fileDirectory);
            //     $student->setPhoto2($fileDirectory . "/" . $photo2Display["filename"]);
            // }
            if ($certMedical) {
                $certMedicalDisplay = $uploader->upload($certMedical, $insUploadDir, $insFileDirectory, false);
                $student->setCertMedical($insFileDirectory . "/" . $certMedicalDisplay["filename"]);
            }
            if ($acteNaissance) {
                $acteNaissanceDisplay = $uploader->upload($acteNaissance, $insUploadDir, $insFileDirectory, false);
                $student->setActeNaissance($insFileDirectory . "/" . $acteNaissanceDisplay["filename"]);
            }
            if ($baccFile) {
                $baccFileDisplay = $uploader->upload($baccFile, $insUploadDir, $insFileDirectory, false);
                $student->setBaccFile($insFileDirectory . "/" . $baccFileDisplay["filename"]);
            }
            if ($cinFile) {
                $cinFileDisplay = $uploader->upload($cinFile, $insUploadDir, $insFileDirectory, false);
                $student->setCinFile($insFileDirectory . "/" . $cinFileDisplay["filename"]);
            }
            if ($autreDocFichier) {
                $autreDocFichierDisplay = $uploader->upload($autreDocFichier, $insUploadDir, $insFileDirectory, false);
                $student->setAutreDocFichier($insFileDirectory . "/" . $autreDocFichierDisplay["filename"]);
            }

            $student->setUser($user);
            $student->setStatus(Etudiant::STATUS_ACTIVE);
            // $student->setMention($mention);
            // $student->setNiveau($niveau);
            // $student->setParcours($candParcours);
            $etudiantService->setMatricule($student);
            $em->persist($student);
            $etudiantManager->save($student);
            $inscription = new Inscription();
            $inscription->setAnneeUniversitaire($anneeUniversitaire);
            $inscription->setEtudiant($student);
            $inscription->setMention($mention);
            $inscription->setNiveau($niveau);            
            $inscription->setPayementRef($form->get('payementRef')->getData());
            $inscription->setPayementRefDate($form->get('payementRefDate')->getData());
            //Set frais scolarite
            $fraisScolarite = $fsManager->createObject();
            $fraisScolarite->setEtudiant($student);
            $fraisScolarite->setMention($student->getMention());
            $fraisScolarite->setNiveau($inscription->getNiveau());
            if($candParcours) {
                $inscription->setParcours($candParcours);
                $fraisScolarite->setParcours($candParcours);
            }
            $fraisScolarite->setMontant($form->get('montant')->getData());
            $fraisScolarite->setModePaiement($form->get('mode_paiement')->getData());
            $fraisScolarite->setReference($form->get('payementRef')->getData());                      
            $fraisScolarite->setAnneeUniversitaire($anneeUniversitaire);
            

            $fraisScolarite->setStatus(FraisScolarite::STATUS_CREATED);


            $fraisScolarite->setAuthor($this->getUser());
            $fraisScolarite->setRemitter($student->getFirstName());

            $semestre = $inscription->getNiveau()->getSemestres()[0];
            $fraisScolarite->setSemestre($semestre);
            $fsDirectory     = $this->getParameter('students_ecolage_scan');
            $fsUploader      = new \App\Services\FileUploader($fsDirectory);
            $today         = new \DateTime();
            $fsFileDirectory = $anneeUniversitaire->getAnnee(). '/' . $mention . '/' . $niveau->getCode() . '/' . UtilFunctionService::seoFriendlyUrl($student->getFirstName() . "-" . $student->getLastName());
            $fsUploadDir = $fsDirectory . '/' . $fsFileDirectory;  
            $fraisScolarite->setDatePaiement($form->get('payementRefDate')->getData());


            
            $fraisScolarite->setStatus(FraisScolarite::STATUS_CREATED);


            // Upload file

          
            if ($payementRefFile) {
                $fileSystem = new Filesystem();
                if(!file_exists($insUploadDir)){                    
                    $fileSystem->mkdir($insUploadDir);    
                    try {
                        
                    } catch (IOExceptionInterface $exception) {
                        echo "An error occurred while creating your directory at ".$exception->getPath();
                    }
                }
                if(!file_exists($fsUploadDir)){
                    $fileSystem->mkdir($fsUploadDir);    
                    try {
                        
                    } catch (IOExceptionInterface $exception) {
                        echo "An error occurred while creating your directory at ".$exception->getPath();
                    }
                }
                $payementFileDisplay = $studentUploader->upload($payementRefFile, $insUploadDir, $insFileDirectory, false);
                $inscription->setPayementRefPath($insFileDirectory . "/" . $payementFileDisplay["filename"]);
                $fsFile = $insUploadDir  . "/" . $payementFileDisplay["filename"];
                $fsExtention = pathinfo($fsFile, PATHINFO_EXTENSION);
                $newFilename = uniqid() . '-' . $today->getTimestamp() . $fsExtention;
                $fileSystem->copy($insUploadDir  . "/" . $payementFileDisplay["filename"], $fsUploadDir . "/" . $newFilename);
                $fraisScolarite->setPaymentRefPath($fsFileDirectory . "/" . $newFilename);
            }
            $em->persist($fraisScolarite);
            $inscription->setFraisScolarite($fraisScolarite);
            $em->persist($inscription);
            $fsManager->savePerEtudiant($fraisScolarite, $ecoService);

            // scolarite
            $scolarites = $userManager->getByRoleCode('ROLE_SCOLARITE');
            /** @var \App\Entity\User $scolarite */
            // try {
                
            //     $siteConfig   = $this->getParameter('site');
            //     $mailerConfig = $this->getParameter('mailer');

            //     $params = [
            //         "sender"      => $mailerConfig['smtp_username'],
            //         "pwd"         => $mailerConfig['smtp_password'],
            //         "sendTo"      => array_map(
            //                             function($item){
            //                                 return $item['email'];
            //                             }, $scolarites
            //                         ),
            //         "subject"     => 'Confirmation candidature',
            //         "senderName"  => $siteConfig['name'],
            //         "senderEmail" => $siteConfig['contact_email'],
            //     ];

            //     $html = $this->renderView(
            //         "frontend/etudiant/email-inscription.html.twig",
            //         [
            //             'student'   => $student,
            //             'site'     => $siteConfig
            //         ]
            //     );

            //     // Send email
            //     $mailer->sendMail($params, $html);
            // } catch (\Throwable $e) {
            //     $logger->error('[ConcoursController::register] error=' . $e->getMessage());
            // }

            return $this->redirectToRoute('front_student_confirm_inscription');
        }

        return $this->render(
            'frontend/etudiant/premiere-inscription.html.twig',
            [
                'mentions'         => $mentions,
                'email'            => $user->getEmail(),
                'niveaux'          => $niveaux,
                'parcours'         => $parcours,
                'registrationForm' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/re-inscription", name="front_student_re_inscription", methods={"GET", "POST"})
     * @IsGranted("ROLE_ETUDIANT")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\ConcoursCandidatureManager   $concoursCandidatureManager
     * @param \App\Manager\EtudiantManager              $etudiantManager
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     * @param \App\Manager\UserManager                  $userManager
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function reInscription(
        Request $request,
        ConcoursCandidatureManager $concoursCandidatureManager,
        EtudiantManager $etudiantManager,
        AnneeUniversitaireService $anneeUniversitaireService,
        UserManager $userManager,
        InscriptionManager $inscriptionManager,
        FraisScolariteManager $fsManager,
        EcolageService $ecoService,
        ParcoursManager $parcManager,
        LoggerInterface $logger,
        Mailer $mailer,
        EtudiantService        $etudiantService
    )
    {
        $user   = $this->getUser();
        $userId = $user->getId();
        /** @var Etudiant $student */
        $student = $etudiantManager->loadOneBy(['user' => $userId]);
        if (!$student) {
            return $this->redirectToRoute('app_logout');
        }
        // déjà inscrit
        /** @var \App\Entity\AnneeUniversitaire|null $anneeUniversitaire */
        $anneeUniversitaire = $anneeUniversitaireService->getCurrent();
        $inscriptionStatus = $inscriptionManager->getByStudentIdAndCollegeYear($student->getId(), $anneeUniversitaire->getId());
        if ($inscriptionStatus === Inscription::STATUS_VALIDATED) {
            //return $this->redirectToRoute('front_student_classes');
        }
        
        /** @var Inscription $inscription */
        $inscription = $inscriptionManager->createObject();
        $form        = $this->createForm(
            InscriptionFormType::class, 
            $inscription,
            [
                'student' => $student
            ]
        );
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted() && $form->isValid()) {
            $extraFormData = $request->get('inscription');
            $payementRefFile = $form->get('payementRefPath')->getData();
            $studentDirectory     = $this->getParameter('students_directory');
            $studentUploader      = new \App\Services\FileUploader($studentDirectory);
            $today         = new \DateTime();
            $insFileDirectory = 'inscription/' .  $anneeUniversitaire->getAnnee(). '/' . $student->getMention() . '/' . $inscription->getNiveau()->getCode() . '/' . UtilFunctionService::seoFriendlyUrl($student->getFirstName() . "-" . $student->getLastName(
                ));       

            $insUploadDir = $studentDirectory . '/' . $insFileDirectory;            
            $inscription->setAnneeUniversitaire($anneeUniversitaire);
            $inscription->setEtudiant($student);
            $inscription->setPayementRef($form->get('payementRef')->getData());
            $inscription->setPayementRefDate($form->get('payementRefDate')->getData());
            //Set frais scolarite
            $fraisScolarite = $fsManager->createObject();
            $fraisScolarite->setEtudiant($student);

            $fraisScolarite->setMention($inscription->getMention());
            $fraisScolarite->setNiveau($inscription->getNiveau());
            $fraisScolarite->setParcours($inscription->getParcours());


            if($parcoursId = $extraFormData['parcours']) {
                $inscription->setParcours($parcManager->load($parcoursId));
                $fraisScolarite->setParcours($parcManager->load($parcoursId));
            }
            $fraisScolarite->setMontant($form->get('montant')->getData());
            $fraisScolarite->setModePaiement($form->get('mode_paiement')->getData());
            $fraisScolarite->setReference($form->get('payementRef')->getData());                      
            $fraisScolarite->setAnneeUniversitaire($anneeUniversitaire);
            $fraisScolarite->setStatus(FraisScolarite::STATUS_CREATED);
            $fraisScolarite->setAuthor($this->getUser());
            $fraisScolarite->setRemitter($student->getFirstName());

            $semestre = $inscription->getNiveau()->getSemestres()[0];
            $fraisScolarite->setSemestre($semestre);
            $fsDirectory     = $this->getParameter('students_ecolage_scan');
            $fsUploader      = new \App\Services\FileUploader($fsDirectory);
            $today         = new \DateTime();
            $fsFileDirectory = $anneeUniversitaire->getAnnee(). '/' . $student->getMention() . '/' . $inscription->getNiveau()->getCode() . '/' . UtilFunctionService::seoFriendlyUrl($student->getFirstName() . "-" . $student->getLastName());
            $fsUploadDir = $fsDirectory . '/' . $fsFileDirectory;  
            $fraisScolarite->setDatePaiement($form->get('payementRefDate')->getData());
            $fraisScolarite->setStatus(FraisScolarite::STATUS_CREATED);
            // Upload file
            if ($payementRefFile) {
                $fileSystem = new Filesystem();
                if(!file_exists($insUploadDir)){                    
                    $fileSystem->mkdir($insUploadDir);    
                    try {
                        
                    } catch (IOExceptionInterface $exception) {
                        echo "An error occurred while creating your directory at ".$exception->getPath();
                    }
                }
                if(!file_exists($fsUploadDir)){
                    $fileSystem->mkdir($fsUploadDir);    
                    try {
                        
                    } catch (IOExceptionInterface $exception) {
                        echo "An error occurred while creating your directory at ".$exception->getPath();
                    }
                }
                $payementFileDisplay = $studentUploader->upload($payementRefFile, $insUploadDir, $insFileDirectory, false);
                $inscription->setPayementRefPath($insFileDirectory . "/" . $payementFileDisplay["filename"]);
                $fsFile = $insUploadDir  . "/" . $payementFileDisplay["filename"];
                $fsExtention = pathinfo($fsFile, PATHINFO_EXTENSION);
                $newFilename = uniqid() . '-' . $today->getTimestamp() . $fsExtention;
                $fileSystem->copy($insUploadDir  . "/" . $payementFileDisplay["filename"], $fsUploadDir . "/" . $newFilename);
                $fraisScolarite->setPaymentRefPath($fsFileDirectory . "/" . $newFilename);
            }
            $inscription->setFraisScolarite($fraisScolarite);
            $em->persist($inscription);
            //update etuddiant ecolage status        

            $student->setNiveau($inscription->getNiveau());
            $student->setMention($inscription->getMention());
            $student->setParcours($inscription->getParcours());

            $student->setStatus(Etudiant::STATUS_ACTIVE);

            if(!$student->getImmatricule()) {
                $etudiantService->setMatricule($student);
            }

            $em->persist($student);
            $fsManager->savePerEtudiant($fraisScolarite, $ecoService);
            // scolarite
            $scolarites = $userManager->getByRoleCode('ROLE_SCOLARITE');
            /** @var \App\Entity\User $scolarite */
            /* foreach ($scolarites as $scolarite) {
                try {                
                    $siteConfig   = $this->getParameter('site');
                    $mailerConfig = $this->getParameter('mailer');
                    $params = [
                        "sender"      => $mailerConfig['smtp_username'],
                        "pwd"         => $mailerConfig['smtp_password'],
                        "sendTo"      => $student->getEmail(),
                        "subject"     => 'Confirmation candidature',
                        "senderName"  => $siteConfig['name'],
                        "senderEmail" => $siteConfig['contact_email'],
                    ];
                    $html = $this->renderView(
                        "frontend/etudiant/email-inscription.html.twig",
                        [
                            'student' => $student,
                            'scolarite'  => $scolarite['first_name'] . ' ' . $scolarite['last_name'],
                            'site'     => $siteConfig
                        ]
                    );
                    // Send email
                    $mailer->sendMail($params, $html);

                } catch (\Throwable $e) {
                    $logger->error('[EtudiantController::reinscription] error=' . $e->getMessage());
                }
            }
 */
            return $this->redirectToRoute('front_student_confirm_inscription');
        }

        return $this->render(
            'frontend/etudiant/re-inscription.html.twig',
            [
                'student'               => $student,
                'inscriptionStatus'     => $inscriptionStatus,
                'anneeUniversitaires'   => [ $anneeUniversitaire ],
                'registrationForm'      => $form->createView()
            ]
        );
    }

    /**
     * @Route("/confirmation-inscription", name="front_student_confirm_inscription", methods={"GET"})
     * @IsGranted("ROLE_ETUDIANT")
     * @param AnneeUniversitaireService $anneeUniversitaireService
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function confirmationInscription(Request $request)
    {
        return $this->render('frontend/etudiant/confirmation-inscription.html.twig');
    }

    /**
     * @Route("/mes-absences", name="front_student_absences")
     * @IsGranted("ROLE_ETUDIANT")
     */
    public function absences(
        Request $request,
        AbsencesManager $absencesManager,
        EtudiantManager $etudiantManager,
        InscriptionManager $inscriptionManager,
        AnneeUniversitaireService $anneeUniversitaireService,
        EcolageService $ecoService
    ) {
        $this->denyAccessUnlessGranted('ROLE_ETUDIANT');
        $userId   = $this->getUser()->getId();
        $etudiant = $etudiantManager->loadOneBy(['user' => $userId]);
        $currentAnneUniv = $anneeUniversitaireService->getCurrent();
        if ($etudiant && $check = $this->checkInscription($etudiant, $inscriptionManager, $anneeUniversitaireService, $ecoService)) {
            return $check;
        }
        $absences = $absencesManager->loadBy(['etudiant' => $etudiant->getId(), 'anneeUniversitaire' => $currentAnneUniv], ['created_at' => 'ASC']);

        return $this->render(
            'frontend/etudiant/absences.html.twig',
            [
                'absences' => $absences,
            ]
        );
    }

    /**
     * @Route("/mon-emploi-temps", name="front_student_emploi_du_temps")
     * @IsGranted("ROLE_ETUDIANT")
     */
    public function edt(
        Request $request,
        EmploiDuTempsManager $emploiDuTempsManager,
        SallesManager $sallesManager,
        EmploiDuTempsRepository $emploiDuTempsRepository,
        EtudiantManager $etudiantManager,
        InscriptionManager $inscriptionManager,
        AnneeUniversitaireService   $anneeUniversitaireService,
        EcolageService $ecoService
    ) {
        $d = $request->get('d', date('Y-m-d'));
        $userId   = $this->getUser()->getId();
        $etudiant = $etudiantManager->loadOneBy(['user' => $userId]);
        $currentAnneUniv = $anneeUniversitaireService->getCurrent();
        if ($etudiant && $check = $this->checkInscription($etudiant, $inscriptionManager, $anneeUniversitaireService, $ecoService)) {
            return $check;
        }

        $lastDay = date('Y-m-d', strtotime("last day of this month"));

        $l            = $this->weekOfMonth($lastDay);
        $yearCurrent  = $l['y'];
        $monthCurrent = $l['m'];
        $nbWeek       = $l['w'];
        $last_day     = $l['last_day'];
        $date_i       = $l['date'];

        $semaine       = [];
        $emploiDuTemps = [];

        for ($i = 0; $i < count($date_i); $i ++) {
            $date_ = sprintf('%02d', $date_i[$i]);
            if ($i == 0) {
                $semaine[] = '01-' . $date_;
            } else {
                $endDate   = sprintf('%02d', (int) $date_i[$i - 1] + 1);
                $semaine[] = $endDate . '-' . $date_;
            }
        }
        $semaine[] = $date_ . '-' . $last_day;

        $j           = 0;
        $currentDay  = "";
        $currentWeek = "";

        foreach ($semaine as $s) {
            $date_explode = explode('-', $s);
            if (date('d') >= $date_explode[0] && date('d') <= $date_explode[1]) {
                $semaine     = $s;
                $currentWeek = $emploiDuTempsRepository->findEdt(
                    $yearCurrent . "-" . $monthCurrent . "-" . $date_explode[0],
                    $yearCurrent . "-" . $monthCurrent . "-" . $date_explode[1]
                );
            }
            $j ++;
        }

        // $currentDay = $emploiDuTempsRepository->findEdt(date('Y-m-d'), date('Y-m-d'));
        $edtDay = [];
        $edtWeek = [];
        
        $edtDay = $emploiDuTempsManager->getByCurrentDay(
            $etudiant->getNiveau()->getId(), 
            $etudiant->getMention()->getId(), 
            $etudiant->getParcours() ? $etudiant->getParcours()->getId() : null,
            $currentAnneUniv->getId(),
            $d
        );

        $edtWeek = $emploiDuTempsManager->getByCurrentWeek(
            $etudiant->getNiveau()->getId(), 
            $etudiant->getMention()->getId(), 
             $etudiant->getParcours() ? $etudiant->getParcours()->getId() : null,
            $currentAnneUniv->getId(),
            $d);

        return $this->render(
            'frontend/etudiant/edt.html.twig',
            [
                'd'             => $d,
                'semaine'       => $semaine,
                'currentWeek'   => $edtWeek,
                'edtDay'        => $edtDay,
            ]
        );
    }

    /**
     * @Route("/calendrier-universitaire", name="front_calendrier_universitaire")
     * @IsGranted("ROLE_ETUDIANT")
     */
    public function calendrier(
        Request $request,
        CalendrierUniversitaireManager $calendrierUniversitaireManager,
        SallesManager $sallesManager,
        EmploiDuTempsRepository $emploiDuTempsRepository,
        EtudiantManager $etudiantManager,
        InscriptionManager $inscriptionManager,
        AnneeUniversitaireService $anneeUniversitaireService,
        EcolageService $ecoService
    ) {
        $userId   = $this->getUser()->getId();
        $etudiant = $etudiantManager->loadOneBy(['user' => $userId]);

        if ($etudiant && $check = $this->checkInscription($etudiant, $inscriptionManager, $anneeUniversitaireService, $ecoService)) {
            return $check;
        }
        $calendriers = $calendrierUniversitaireManager->loadAll();

        return $this->render(
            'frontend/etudiant/calendrier.html.twig',
            [
                'calendriers' => $calendriers,
            ]
        );
    }

    /**
     * Detal Calendrier univ function
     * @Route("/calendrier-universitaire/detail/{id}", name="front_calendrier_universitaire_detail", methods={"GET"})
     *
     * @param \App\Entity\CalendrierUniversitaire       $calendrierUniv
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function absenceDetail(CalendrierUniversitaire $calendrierUniv, EtudiantManager $etudiantManager, InscriptionManager $inscriptionManager,
        AnneeUniversitaireService $anneeUniversitaireService, Request $request, EcolageService $ecoService)
    {
        $userId   = $this->getUser()->getId();
        $etudiant = $etudiantManager->loadOneBy(['user' => $userId]);
        if ($etudiant && $check = $this->checkInscription($etudiant, $inscriptionManager, $anneeUniversitaireService, $ecoService)) {
            return $check;
        }
        return $this->render(
            'frontend/etudiant/calendrier-detail.html.twig',
            [
                'calendrierUniv' => $calendrierUniv,
            ]
        );
    }

    /**
     * @Route("/mes-notes", name="front_student_notes")
     * @IsGranted("ROLE_ETUDIANT")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\EtudiantManager              $etudiantManager
     * @param \App\Manager\NotesManager                 $notesManager
     * @param \App\Services\NotesService                $notesService
     * @param \App\Manager\MatiereManager               $matiereManager
     * @param \App\Manager\SemestreManager              $semestreManager
     * @param \App\Manager\InscriptionManager           $inscriptionManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function notes(
        Request $request,
        EtudiantManager $etudiantManager,
        NotesManager $notesManager,
        NotesService $notesService,
        MatiereManager $matiereManager,
        SemestreManager $semestreManager,
        InscriptionManager $inscriptionManager,
        AnneeUniversitaireService $anneeUniversitaireService,
        EcolageService $ecoService
    )
    {
        $userId      = $this->getUser()->getId();
        $student     = $etudiantManager->loadOneBy(['user' => $userId]);

        if (!$student) {
            return $this->redirectToRoute('front_student_first_inscription');
        }

        if ($student && $check = $this->checkInscription($student, $inscriptionManager, $anneeUniversitaireService, $ecoService)) {
            return $check;
        }
        $creditsByUE = $matiereManager->getCreditByUE();
        // dump($creditsByUE);die;
        // current semester
        $currentSemester = $semestreManager->getCurrentSemester($student->getNiveau()->getId());
        // dump($currentSemester);die;
        // all semester
        $semesters = $semestreManager->loadBy(['niveau' => $student->getNiveau()->getId()], ['startDate' => 'ASC']);
        $defaultSemester = 0;
        if (!empty($semesters[0])) {
            $defaultSemester = $semesters[0]->getId();
        }

        // selected semester
        $selectedS = empty($request->get('s')) ?
            (($currentSemester) ? $currentSemester->getId() : $defaultSemester) : $request->get('s');
        // dump($semesters);die;
        // notes
        $notes = $notesManager->getByStudentAndSemesterId($student, (int) $selectedS);

        $notesUE    = $notesService->getNotesUE($notes);
        $moyennesUE = $notesService->getMoyennesUE($notesUE);
        $moyenneG   = $notesService->getMoyenneG($moyennesUE);

        return $this->render(
            'frontend/etudiant/notes.html.twig',
            [
                's'          => (int) $selectedS,
                'semesters'  => $semesters,
                'notes'      => $notes,
                'notesUE'    => $notesUE,
                'credits'    => $creditsByUE,
                'moyennesUE' => $moyennesUE,
                'moyenneG'   => $moyenneG,
            ]
        );
    }

    /**
     * @Route("/download/{id}", name="front_download_classes", methods={"GET"})
     * @param \App\Entity\CoursMedia $coursMedia
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download(CoursMedia $coursMedia)
    {
        $uploadDir = $this->getParameter('cours_directory');
        // $file = new File($uploadDir . '/' . $coursMedia->getCours()->getId() . '/' . $coursMedia->getName() . '.' . $coursMedia->getType());
//        $file = new File($uploadDir . '/' . $coursMedia->getCours()->getId() . '/' . $coursMedia->getPath());
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
        
        if(!$file)
            return $this->redirectToRoute('front_student_classes');

        return $this->file($file);
    }

    /**
     * Returns the number of week in a month for the specified date.
     *
     * @param string $date
     *
     * @return int
     */
    function weekOfMonth($date)
    {
        // estract date parts
        list($y, $m, $d) = explode('-', date('Y-m-d', strtotime($date)));

        // current week, min 1
        $w  = 1;
        $it = [];

        // for each day since the start of the month
        for ($i = 1; $i < $d; ++ $i) {
            // if that day was a sunday and is not the first day of month
            if ($i > 1 && date('w', strtotime("$y-$m-$i")) == 0) {
                $it[] = $i;
                // increment current week
                ++ $w;
            }
            $a[] = $w;
        }

        $data = [
            'y'        => $y,
            'm'        => $m,
            'w'        => $w,
            'last_day' => $d,
            'date'     => $it
        ];

        return $data;
    }

    /**
     * @Route("/demande-doc", name="front_student_requestdoc")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\DemandeDocManager            $demandeDocManager
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameter
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function demandeDoc(Request $request,
                               DemandeDocManager $demandeDocManager,
                               EtudiantManager $etudiantManager,
                               InscriptionManager $inscriptionManager,
                               ParameterBagInterface $parameter,
                               AnneeUniversitaireService   $anneeUniversitaireService,
                               EcolageService $ecoService)
    {
        $userId      = $this->getUser()->getId();
        $student     = $etudiantManager->loadOneBy(['user' => $userId]);

        if ($student && $check = $this->checkInscription($student, $inscriptionManager, $anneeUniversitaireService, $ecoService)) {
            return $check;
        }
        $currentuser    = $this->getUser();
        // create demande instance
        /** @var DemandeDoc $demandeDoc */
        $demandeDoc = $demandeDocManager->createObject();
        // form
        $form = $this->createForm(
            DemandeType::class,
            $demandeDoc
        );

        // handle form submit
        $form->handleRequest($request);
        // check form validity if submitted
        if ($form->isSubmitted() && $form->isValid()) {

            $etudiant = $etudiantManager->loadOneBy(array("user" => $currentuser->getId()));
            $demandeDoc->setStatut("CREATED");
            $demandeDoc->setEtudiant($etudiant);

            $pieceIdentity     = $form->get('identityPiece')->getData();
            $depotAttestation = $form->get('depotAttestation')->getData();
            $directory     = $parameter->get('piece_demande_diplome_directory');
            $uploader      = new \App\Services\FileUploader($directory);
            $today         = new \DateTime();
            $fileDirectory = $student->getImmatricule() . "-" . $today->getTimestamp();

            // Upload file
            if ($pieceIdentity) {
                $pieceIdentityFileDisplay = $uploader->upload($pieceIdentity, $directory, $fileDirectory);
                $demandeDoc->setIdentityPiece($fileDirectory . "/" . $pieceIdentityFileDisplay['filename']);
            }

            if ($depotAttestation) {
                $attestationFileDisplay = $uploader->upload($depotAttestation, $directory, $fileDirectory);
                $demandeDoc->setDepotAttestation($fileDirectory . "/" . $attestationFileDisplay['filename']);
            }
            $demandeDoc->setAnneeUniversitaire($anneeUniversitaireService->getCurrent());
            $demandeDocManager->save($demandeDoc);
            $this->addFlash('infos', 'Votre demande a été bien prise en compte');

            return $this->redirectToRoute('front_student_requestdoc');
        }

        return $this->render(
            'frontend/etudiant/demande-doc.html.twig',
            [
                'etudiant' => $student,
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @IsGranted("ROLE_ETUDIANT")
     * @Route("/frais/index", name="front_student_frais_index", methods={"GET", "POST"})
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
        InscriptionManager              $inscriptionManager,
        EtudiantManager                 $etudiantManager,
        AnneeUniversitaireService       $anneeUniversitaireService, 
        FraisScolariteRepository        $fraisScolariteRepo,
        EcolageService                  $ecoService,
        EcolageManager                  $ecoManager)
    {

        $student = $etudiantManager->loadOneBy(['user' => $this->getUser()]);
        $currentAnneUniv = $anneeUniversitaireService->getCurrent();   
        $filters = [
            'mention' => $student->getMention(),
            'niveau'  => $student->getNiveau()
        ];
        if($student->getParcours())
            $filters['parcours'] = $student->getParcours();
        $ecolageList = $ecoManager->loadBy($filters, []);
        // dump($ecolageList);die;
        $totalClassEcolage = array_sum(array_map(   
            function($item) {
                return $item->getMontant();
        },$ecolageList));
        if ($student && $check = $this->checkInscription($student, $inscriptionManager, $anneeUniversitaireService, $ecoService)) {
            return $check;
        }
        $fraisScolarites = $fraisScolariteRepo->getEtudiantPaiement($student, $currentAnneUniv);
        $totalPaidEcolage = array_sum(array_map(   
            function($item) {
                return $item->getMontant();
        },$fraisScolarites));

        return $this->render(
            'frontend/etudiant/frais-scolarite/index.html.twig',
            [
                'student'  => $student,
                'list'      => $fraisScolarites,
                'ecolageList' => $ecolageList,
                'totalClassEcolage' => $totalClassEcolage,
                'totalPaidEcolage' => $totalPaidEcolage
            ]
        );
    
    }

    /**
     * @IsGranted("ROLE_ETUDIANT")
     * @Route("/frais/new/", name="front_student_frais_new", methods={"GET", "POST"})
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
        InscriptionManager          $inscriptionManager,
        FraisScolariteManager       $fraisScolariteManager,
        FraisScolariteRepository    $fraisScolariteRepo,
        EtudiantManager             $etudiantManager,
        AnneeUniversitaireService   $anneeUniversitaireService,
        EcolageService              $ecoService)
    {
        $currentEtudiant = $etudiantManager->loadOneBy(['user' => $this->getUser()]);        
        if ($currentEtudiant && $check = $this->checkInscription($currentEtudiant, $inscriptionManager, $anneeUniversitaireService, $ecoService)) {
            return $check;
        }
        $fraisScolarite = $fraisScolariteManager->createObject();
        $form = $this->createForm(
            FraisScolariteType::class,
            $fraisScolarite,
            [
                'fraisScolarite'     => $fraisScolarite,
                'em'       => $this->getDoctrine()->getManager(),
                'etudiant' => $currentEtudiant
            ]
        );
        $form->handleRequest($request);
        // $fsSubmited = $form->getData();
        // $fsSubmited->setMention(null);
        // dd($form->getData());
        // dd($form->getErrors(true));
        
        if ($form->isSubmitted() && $form->isValid()) {
            $fraisScolarite->setEtudiant($currentEtudiant);
            $collegeYear = $anneeUniversitaireService->getCurrent();          
            $fraisScolarite->setAnneeUniversitaire($collegeYear);
            $fraisScolarite->setStatus(FraisScolarite::STATUS_CREATED);
            $fraisScolarite->setAuthor($this->getUser());
            $ecolageRepo = $this->getDoctrine()->getRepository(Ecolage::class);
            $ecolageFilters = ['mention' => $fraisScolarite->getMention(), 'niveau' => $fraisScolarite->getNiveau(), 'semestre' => $fraisScolarite->getSemestre()];
            if($parcours = $fraisScolarite->getParcours())
                $ecolageFilters['parcours'] = $parcours;
            $classEcolage = $ecolageRepo->findOneBy($ecolageFilters);
            $payementRefFile = $form->get('paymentRefPath')->getData();
            // dump($form);die;
            $directory     = $this->getParameter('students_ecolage_scan');
            $uploader      = new \App\Services\FileUploader($directory);
            $today         = new \DateTime();
            $fileDirectory = UtilFunctionService::seoFriendlyUrl($currentEtudiant->getLastName()) . "-" . $today->getTimestamp(
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
            $fraisScolarite->setStatus(FraisScolarite::STATUS_CREATED);
            $fraisScolariteManager->savePerEtudiant($fraisScolarite, $ecoService);

            $ecolageHistory = new PaiementHistory();
            $ecolageHistory->setResourceName(PaiementHistory::ECOLAGE_RESOURCE);
            $ecolageHistory->setStatut(FraisScolarite::STATUS_CREATED);
            $ecolageHistory->setResourceId($fraisScolarite->getId());
            $ecolageHistory->setValidator($this->getUser());
            $ecolageHistory->setCreatedAt(new \DateTime());
            $ecolageHistory->setUpdatedAt(new \DateTime());
            $ecolageHistory->setComment("");
            $ecolageHistory->setMontant($fraisScolarite->getMontant());

            $em = $this->getDoctrine()->getManager();
            $em->persist($ecolageHistory);       
            $em->flush();

            // $this->persist($fraisScolarite);
            // $this->flush();

            return $this->redirectToRoute('front_student_frais_index');
        }

        return $this->render(
            'frontend/etudiant/frais-scolarite/new.html.twig',
            [
                'form'              => $form->createView(),
                'fraisScolarite'    => $fraisScolarite,
                'etudiant'          => $currentEtudiant
            ]
        );
    
    }

    /**
     * @IsGranted("ROLE_ETUDIANT")
     * @Route("/frais/{id}/edit", name="front_student_frais_edit", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function fraisEdit(
        Request                     $request, 
        InscriptionManager          $inscriptionManager,
        fraisScolarite              $fraisScolarite,
        FraisScolariteManager       $fraisScolariteManager,
        FraisScolariteRepository    $fraisScolariteRepo,
        EtudiantManager             $etudiantManager,
        AnneeUniversitaireService   $anneeUniversitaireService,
        EcolageService              $ecoService)
    {
        $currentEtudiant = $etudiantManager->loadOneBy(['user' => $this->getUser()]);
        if ($currentEtudiant && $check = $this->checkInscription($currentEtudiant, $inscriptionManager, $anneeUniversitaireService, $ecoService)) {
            return $check;
        }
        $form = $this->createForm(
            FraisScolariteType::class,
            $fraisScolarite,
            [
                'fraisScolarite'     => $fraisScolarite,
                'em'       => $this->getDoctrine()->getManager(),
                'etudiant' => $currentEtudiant
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $fraisScolarite->setEtudiant($currentEtudiant);
            $collegeYear = $anneeUniversitaireService->getCurrent();

            $fraisScolarite->setStatus(FraisScolarite::STATUS_CREATED);
            $fraisScolariteManager->savePerEtudiant($fraisScolarite, $ecoService);
            // $fraisScolariteManager->save($fraisScolarite);
            return $this->redirectToRoute('front_student_frais_index');
        }

        return $this->render(
            'frontend/etudiant/frais-scolarite/edit.html.twig',
            [
                'form'              => $form->createView(),
                'etudiant'          => $currentEtudiant,
                'fraisScolarite'    => $fraisScolarite
            ]
        );    
    }

    /**
     * @IsGranted("ROLE_ETUDIANT")
     * @Route("/download/ecolage/{id}/scan", name="front_student_download_scan_eco", methods={"GET"})
     * @param \App\Entity\ConcoursCandidature $concoursCandidature
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadPaymentRef(FraisScolarite $fraisScolarite)
    {
        $uploadDir = $this->getParameter('students_ecolage_scan');
        $file = new File($uploadDir . '/' . $fraisScolarite->getPaymentRefPath());

        return $this->file($file);
    }

    /**
     * @Route("/inscription/parcours-options", name="front_etudiant_parcours_options", methods={"GET"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\MentionManager               $mentionManager
     * @param \App\Manager\ConcoursManager              $concoursManager
     * @param \App\Manager\ParcoursManager              $parcoursManager
     * @param \App\Manager\ConcoursConfigManager        $concoursConfManager
     *
     * @return string
     */
    public function ajaxGetParcoursOptions(
        Request $request,        
        ParcoursManager $parcoursManager
        
    ) {
        $selectedM = $request->get('m');
        $selectedN = $request->get('n');
        
        $parcours = $parcoursManager->loadBy(["mention" => $selectedM,"niveau" => $selectedN]);
           
        return $this->render(
            'frontend/etudiant/inscription/_parcours_options.html.twig',
            [
                'parcours' => $parcours
            ]
        );
    }

    /**
     * @IsGranted("ROLE_ETUDIANT")
     * @Route("/cours/{id}/document/new", name="front_student_cours_new_document", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Services\AnneeUniversitaireService   $anneeUniversitaireService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function coursDocNew(
        Request                     $request, 
        Cours                       $cours,
        CoursManager                $coursManager,
        EtudiantManager             $etudiantManager,
        InscriptionManager          $inscriptionManager,
        EcolageService              $ecoService,
        ParameterBagInterface       $parameter,
        AnneeUniversitaireService   $anneeUniversitaireService,
        EtudiantDocumentRepository  $etudiantRepo)
    {
        $currentEtudiant = $etudiantManager->loadOneBy(['user' => $this->getUser()]);
        if ($currentEtudiant && $check = $this->checkInscription($currentEtudiant, $inscriptionManager, $anneeUniversitaireService, $ecoService)) {
            return $check;
        }
        $studentDoc = new EtudiantDocument();
        $form = $this->createForm(
            EtudiantDocumentType::class,
            $studentDoc
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $directory     = $parameter->get('students_cours');
            $uploader      = new \App\Services\FileUploader($directory);
            $fileDirectory = $currentEtudiant->getId() . "/" . $cours->getId();
            // Upload file
            $pathFile        = $form->get('path')->getData();
            if ($pathFile) {
                $pathFile = $uploader->upload($pathFile, $directory, $fileDirectory);
                $studentDoc->setPath($fileDirectory . "/" . $pathFile["filename"]);
            }
            $studentDoc->setEtudiant($currentEtudiant);
            $studentDoc->setCours($cours);

            $etudiantRepo->add($studentDoc, true);
            return $this->redirectToRoute('front_student_classes');
        }

        return $this->render(
            'frontend/etudiant/cours/new-doc.html.twig',
            [
                'form'              => $form->createView(),
                'etudiant'          => $currentEtudiant
            ]
        );    
    }

    /**
     * @Route("/download/doc/{id}", name="front_download_cours_doc", methods={"GET"})
     * @param \App\Entity\EtudiantDocument $etudiantDoc
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadCoursDoc(EtudiantDocument $etudiantDoc)
    {
        $uploadDir = $this->getParameter('students_cours');        
        $file = new File($uploadDir . '/' . $etudiantDoc->getPath());

        return $this->file($file);
    }
}