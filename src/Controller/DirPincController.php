<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Dompdf\Dompdf;
use Dompdf\Options;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
//use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use Symfony\Component\HttpFoundation\File\File;

//use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\KernelInterface;

use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

use Psr\Log\LoggerInterface;



class DirPincController extends AbstractController
{
    /**
     * @Route("/dir/pinc", name="app_dir_pinc")
     */
    public function index(): Response
    {
        return $this->render('frontend/pinc/index.html.twig', [
            'controller_name' => 'DirPincController',
        ]);
    }

    /**
     * @Route("/pinc", name="index")
     * @IsGranted("ROLE_DIR_PINC")
     */
    public function classes(
        Request $request,
        MatiereManager $matiereManager,
        CoursRepository $coursRepository,
        PaginatorInterface $paginator,
        UniteEnseignementsManager $ueManager,
        MatiereRepository $matiereRepository
    ){        
        $matieres = $matiereManager->findAllMatiere();
        $profs = $matiereManager->findAllEnseignant();
        $mentions = $matiereManager->findAllMentions();
        //dd($mentions);die;
        /*$pagination = $paginator->paginate(
            $matieres, // Utilisation du tableau
            $request->query->getInt('page', 1), // Numéro de la page
            10 // Nombre d'éléments par page
        );
        */
        //dd($matieres);die;
        $uniteEns       = $ueManager->loadBy([], ['libelle' => 'ASC']);
        $defaultEnseignant = "Enseignant non trouvé"; // Déclaration en dehors de la boucle foreach

        $result = [];
        foreach ($matieres as $matiere) {
            $resultKey = $matiere->getUniteEnseignements()->getId();
            
            if (!isset($result[$resultKey])) {
                $result[$resultKey] = [
                    "UE" => $matiere->getUniteEnseignements()->getLibelle(),
                    "MATIERES" => [],
                ];
            }

            $matiereId = $matiere->getId();
            $enseignant = $matiere->getEnseignant();
            $defaultEnseignant = "Enseignant non trouvé";

            // Récupération de l'enseignant associé à la matière
            $matiereEnseignant = $defaultEnseignant;
            if ($enseignant !== null) {
                try {
                    if ($enseignant->getId()) {
                        $matiereEnseignant = $enseignant->getFirstName() . " " . $enseignant->getLastName();
                    }
                } catch (\Exception $e) {
                    // Gestion de l'exception liée à la récupération de l'enseignant
                }
            }

            // Ajout de la matière avec son enseignant dans la structure $result
            $result[$resultKey]["MATIERES"][$matiereId] = [
                "NOM" => $matiere->getNom(),
                "ENSEIGNANT" => $matiereEnseignant,
                "COURS" => [], // Initialisation du tableau des cours pour cette matière
            ];

            // Récupération des cours pour cette matière
            foreach ($matiere->getCours() as $cours) {
                $coursId = $cours->getId();
                // Vous pouvez obtenir d'autres détails du cours selon vos besoins

                // Ajout des cours avec leur enseignant associé dans la structure $result
                $result[$resultKey]["MATIERES"][$matiereId]["COURS"][$coursId] = [
                    "LIBELLE" => $cours->getLibelle(),
                    // ... autres détails du cours ...
                    "ENSEIGNANT" => $matiereEnseignant, // Même enseignant pour tous les cours de la matière
                ];
            }
        }

          //dd($totalItems);die;
        //dd($pagination);die;
        //var_dump($result);
        //'pagination' => $pagination,
        return $this->render(
            'frontend/pinc/index.html.twig',
            [
                'uniteEns' => $uniteEns,
                'mentions' => $mentions,
                'profs' => $profs,
                'result'   => $result
            ]
        );
    }

    /**
     * @Route("/find_by_prof", name="find_by_prof")
     * @IsGranted("ROLE_DIR_PINC")
    */
    public function recherche(
        Request $request, 
        MatiereManager $matiereManager,
        CoursRepository $coursRepository,
        PaginatorInterface $paginator,
        UniteEnseignementsManager $ueManager,
        MatiereRepository $matiereRepository
    ){
        $nomProf = $request->query->get('enseignant');
        //dd($nomProf);die;
        // Récupérer toutes les matières
        $matieres = $matiereManager->findMatiereByEnseignantName($nomProf);
        //dd($matieres);die;
        $profs = $matiereManager->findAllEnseignant();
        $mentions = $matiereManager->findAllMentions();
        //dd($profs);die;


        /*$pagination = $paginator->paginate(
            $matieres, // Utilisation du tableau
            $request->query->getInt('page', 1), // Numéro de la page
            10 // Nombre d'éléments par page
        );*/

        //dd($matieres);die;
        $uniteEns       = $ueManager->loadBy([], ['libelle' => 'ASC']);
        $defaultEnseignant = "Enseignant non trouvé"; // Déclaration en dehors de la boucle foreach

        $result = [];
        foreach ($matieres as $matiere) {
            $resultKey = $matiere->getUniteEnseignements()->getId();
            
            if (!isset($result[$resultKey])) {
                $result[$resultKey] = [
                    "UE" => $matiere->getUniteEnseignements()->getLibelle(),
                    "MATIERES" => [],
                ];
            }

            $matiereId = $matiere->getId();
            $enseignant = $matiere->getEnseignant();
            $defaultEnseignant = "Enseignant non trouvé";

            // Récupération de l'enseignant associé à la matière
            $matiereEnseignant = $defaultEnseignant;
            if ($enseignant !== null) {
                try {
                    if ($enseignant->getId()) {
                        $matiereEnseignant = $enseignant->getFirstName() . " " . $enseignant->getLastName();
                    }
                } catch (\Exception $e) {
                    // Gestion de l'exception liée à la récupération de l'enseignant
                }
            }

            // Ajout de la matière avec son enseignant dans la structure $result
            $result[$resultKey]["MATIERES"][$matiereId] = [
                "NOM" => $matiere->getNom(),
                "ENSEIGNANT" => $matiereEnseignant,
                "COURS" => [], // Initialisation du tableau des cours pour cette matière
            ];

            // Récupération des cours pour cette matière
            foreach ($matiere->getCours() as $cours) {
                $coursId = $cours->getId();
                // Vous pouvez obtenir d'autres détails du cours selon vos besoins

                // Ajout des cours avec leur enseignant associé dans la structure $result
                $result[$resultKey]["MATIERES"][$matiereId]["COURS"][$coursId] = [
                    "LIBELLE" => $cours->getLibelle(),
                    // ... autres détails du cours ...
                    "ENSEIGNANT" => $matiereEnseignant, // Même enseignant pour tous les cours de la matière
                ];
            }
        }

          //dd($totalItems);die;
        //dd($pagination);die;
        //var_dump($result);
        return $this->render(
            'frontend/pinc/index.html.twig',
            [
                'uniteEns' => $uniteEns,
                'profs' => $profs,
                'mentions' => $mentions,
                'result'   => $result
            ]
        );
       
    }


    /**
     * @Route("/find_by_mention", name="find_by_mention")
     * @IsGranted("ROLE_DIR_PINC")
    */
    public function rechercheByMention(
        Request $request, 
        MatiereManager $matiereManager,
        CoursRepository $coursRepository,
        PaginatorInterface $paginator,
        UniteEnseignementsManager $ueManager,
        MatiereRepository $matiereRepository
    ){
        $mentionselecteted = $request->query->get('mention');
        //dd($nomProf);die;
        // Récupérer toutes les matières
        $matieres = $matiereManager->findMatiereByMention($mentionselecteted);
        //dd($matieres);die;
        $profs = $matiereManager->findAllEnseignant();
        $mentions = $matiereManager->findAllMentions();
        
        //dd($profs);die;


        /*$pagination = $paginator->paginate(
            $matieres, // Utilisation du tableau
            $request->query->getInt('page', 1), // Numéro de la page
            10 // Nombre d'éléments par page
        );*/

        //dd($matieres);die;
        $uniteEns       = $ueManager->loadBy([], ['libelle' => 'ASC']);
        $defaultEnseignant = "Enseignant non trouvé"; // Déclaration en dehors de la boucle foreach

        $result = [];
        foreach ($matieres as $matiere) {
            $resultKey = $matiere->getUniteEnseignements()->getId();
            
            if (!isset($result[$resultKey])) {
                $result[$resultKey] = [
                    "UE" => $matiere->getUniteEnseignements()->getLibelle(),
                    "MATIERES" => [],
                ];
            }

            $matiereId = $matiere->getId();
            $enseignant = $matiere->getEnseignant();
            $defaultEnseignant = "Enseignant non trouvé";

            // Récupération de l'enseignant associé à la matière
            $matiereEnseignant = $defaultEnseignant;
            if ($enseignant !== null) {
                try {
                    if ($enseignant->getId()) {
                        $matiereEnseignant = $enseignant->getFirstName() . " " . $enseignant->getLastName();
                    }
                } catch (\Exception $e) {
                    // Gestion de l'exception liée à la récupération de l'enseignant
                }
            }

            // Ajout de la matière avec son enseignant dans la structure $result
            $result[$resultKey]["MATIERES"][$matiereId] = [
                "NOM" => $matiere->getNom(),
                "ENSEIGNANT" => $matiereEnseignant,
                "COURS" => [], // Initialisation du tableau des cours pour cette matière
            ];

            // Récupération des cours pour cette matière
            foreach ($matiere->getCours() as $cours) {
                $coursId = $cours->getId();
                // Vous pouvez obtenir d'autres détails du cours selon vos besoins

                // Ajout des cours avec leur enseignant associé dans la structure $result
                $result[$resultKey]["MATIERES"][$matiereId]["COURS"][$coursId] = [
                    "LIBELLE" => $cours->getLibelle(),
                    // ... autres détails du cours ...
                    "ENSEIGNANT" => $matiereEnseignant, // Même enseignant pour tous les cours de la matière
                ];
            }
        }

          //dd($totalItems);die;
        //dd($pagination);die;
        //var_dump($result);
        return $this->render(
            'frontend/pinc/index.html.twig',
            [
                'uniteEns' => $uniteEns,
                'profs' => $profs,
                'mentions' => $mentions,
                'result'   => $result
            ]
        );
       
    }


    /**
     * @Route("/mes-cours", name="front_student_classes")
     * @IsGranted("ROLE_DIR_PINC")
     */
    /*public function classes(
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
    }*/
}
