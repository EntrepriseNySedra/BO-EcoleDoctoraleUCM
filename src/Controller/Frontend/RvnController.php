<?php

namespace App\Controller\Frontend;

use App\Entity\Absences;
use App\Entity\Etudiant;
use App\Entity\Matiere;
use App\Entity\Mention;
use App\Entity\Niveau;
use App\Entity\Parcours;
use App\Entity\Semestre;
use App\Form\AbsenceType;

use App\Manager\AbsencesManager;
use App\Manager\MentionManager;
use App\Manager\NiveauManager;
use App\Manager\ParcoursManager;
use App\Manager\SemestreManager;
use App\Manager\UniteEnseignementsManager;

use App\Services\AnneeUniversitaireService;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Container\ContainerInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Description of RvnController.php.
 *
 * @package App\Controller\Frontend
 * @Route("/rvn")
 */
class RvnController extends AbstractController
{
    /**
     * @Route("/absences/list", name="front_rvn_absence_index", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request   $request
     * @param \App\Services\AnneeUniversitaireService     $anneeUniversitaireService
     * @param \App\Manager\AbsencesManager                $absenceManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @IsGranted({"ROLE_RVN"})
     */
    public function absences(
        Request                         $request, 
        MentionManager $mentionManager,
        NiveauManager $niveauManager,
        ParcoursManager $parcoursManager,
        SemestreManager                 $semManager,
        AnneeUniversitaireService       $anneeUniversitaireService,
        AbsencesManager                 $absencesManager)
    {
        $user = $this->getUser();
        $m = $request->get('m', 0);
        $n = $request->get('n', 0);
        $p = $request->get('p');
        $s = $request->get('s', 0);
        $mentions       = $mentionManager->loadBy([], ['nom' => 'ASC']);
        $niveaux        = $niveauManager->loadBy([], ['libelle' => 'ASC']);
        $parcours       = $parcoursManager->loadBy(
            [
                'mention' => $m,
                'niveau'  => $n
            ], 
            ['nom' => 'ASC']);
        $semestres = $semManager->loadBy(['niveau' => $n], ['libelle' => 'ASC']);
        // $options = ['mention' => $m, 'niveau' => $n, 'parcours' => $p];
        $options = ['mention' => $m, 'niveau' => $n, 'parcours' => $p, 'semestre' => $s, 'matiere' => null];
        $currentAnneUniv    = $anneeUniversitaireService->getCurrent();
        $absences = $absencesManager->getPerClass_scol($currentAnneUniv->getId(), $options);
        // dump($absences);die;
        return $this->render(
            'frontend/rvn/absence/index.html.twig',
            [
                'm'                     => $m,
                'n'                     => $n,
                'p'                     => $p,
                's'                     => $s,
                'mentions'              => $mentions,
                'niveaux'               => $niveaux,
                'parcours'              => $parcours,
                'semestres'              => $semestres,
                'list'                  => $absences
            ]
        );
    }

    /**
     * @Route("/absences/etudiant/{id}", name="front_rvn_absence_etudiant_index", methods={"GET", "POST"})
     * @ParamConverter("semestre", options={"mapping": {"semestre_id": "id"}})
     * @param \Symfony\Component\HttpFoundation\Request   $request
     * @param \App\Services\AnneeUniversitaireService     $anneeUniversitaireService
     * @param \App\Manager\AbsencesManager                $absenceManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @IsGranted({"ROLE_RVN"})
     */
    public function absencesEtudiantIndex(
        Request                         $request, 
        Etudiant                        $etudiant,
        MentionManager                  $mentionManager,
        NiveauManager                   $niveauManager,
        ParcoursManager                 $parcoursManager,
        AnneeUniversitaireService       $anneeUniversitaireService,
        AbsencesManager                 $absencesManager)
    {
        $user = $this->getUser();
        $s = $request->get('semestre_id', 0);
        $currentAnneUniv    = $anneeUniversitaireService->getCurrent();
        $absences = $absencesManager->getPerETudiantByMaquette($currentAnneUniv->getId(), $etudiant->getId(), $s);
        // dump($absences);die;
        return $this->render(
            'frontend/rvn/absence/etudiant/index.html.twig',
            [                
                'list'                  => $absences,
                'etudiant'              => $etudiant
            ]
        );
    }

    /**
     * @Route("/absences/etudiant/{id}/details/{matiere_id}", name="front_rvn_absence_etudiant_details", methods={"GET", "POST"})
     * @ParamConverter("matiere", options={"mapping": {"matiere_id": "id"}})
     * @param \Symfony\Component\HttpFoundation\Request   $request
     * @param \App\Services\AnneeUniversitaireService     $anneeUniversitaireService
     * @param \App\Manager\AbsencesManager                $absenceManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @IsGranted({"ROLE_RVN"})
     */
    public function absencesEtudiantDetails(
        Request                         $request, 
        Etudiant                        $etudiant,
        Matiere                         $matiere,
        MentionManager                  $mentionManager,
        NiveauManager                   $niveauManager,
        ParcoursManager                 $parcoursManager,
        AnneeUniversitaireService       $anneeUniversitaireService,
        AbsencesManager                 $absencesManager)
    {
        $user = $this->getUser();
        $currentAnneUniv    = $anneeUniversitaireService->getCurrent();
        $absences = $absencesManager->getPerETudiantByMatiere($currentAnneUniv->getId(), $etudiant->getId(), $matiere->getId());
        
        return $this->render(
            'frontend/rvn/absence/etudiant/details.html.twig',
            [
                'list'                  => $absences,
                'etudiant'              => $etudiant,
                'matiere'               => $matiere
            ]
        );
    }

    /**
     * @Route("/absence/{id}/edit", name="front_rvn_absence_edit", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request   $request
     * @param \App\Services\AnneeUniversitaireService     $anneeUniversitaireService
     * @param \App\Manager\AbsencesManager                $absenceManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @IsGranted({"ROLE_RVN"})
     */
    public function absenceEdit(Request $request, Absences $absence, AbsencesManager $absManager, ParameterBagInterface $parameter, AnneeUniversitaireService $anneeUnivService)
    {
        $user = $this->getUser();
        $form = $this->createForm(
            AbsenceType::class,
            $absence
        );
        $currentAnneUniv = $anneeUnivService->getCurrent();
        $form->handleRequest($request);
        // dump($form->getErrors(true));die;
        if($form->isSubmitted() && $form->isValid()) {
            
            $justificationFile = $form->get('justification')->getData();
            /*$directory     = $parameter->get('students_absence_scan');
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
      
            if ($justificationFile) {
                $justFileDisplay = $uploader->upload($justificationFile, $directory, $fileDirectory, false);
                $absence->setJustification($fileDirectory . "/" . $justFileDisplay["filename"]);
            }*/

            if ($justificationFile !== null) {
                // Si un fichier de justification est présent, traitez-le comme auparavant
                $directory = $parameter->get('students_absence_scan');
                if (!is_dir($directory)) {
                    try {
                        $filesystem = new Filesystem();
                        $filesystem->mkdir($directory);
                    } catch (IOExceptionInterface $exception) {
                        echo "An error occurred while creating your directory at ".$exception->getPath();
                    }
                }
                $uploader = new \App\Services\FileUploader($directory);
                
                $fileDirectory = $currentAnneUniv->getAnnee() . '/' . $absence->getMention()->getDiminutif() . '/' . $absence->getNiveau()->getCode() . '/' . $absence->getEtudiant()->getId();
                
                $justFileDisplay = $uploader->upload($justificationFile, $directory, $fileDirectory, false);
                $absence->setJustification($fileDirectory . "/" . $justFileDisplay["filename"]);
            } else {
                // Si le fichier de justification est null, définissez la justification à partir de la description
                //$absence->setJustification($form->get('description')->getData()); // Utilisation de la description comme justification
                $absence->setJustification("Justifié sans pièce");
                //$absence->setStatus(Absence::STATUS_RVN_VALIDATED_WITHOUT_FILE);
            }

            $edt = $absence->getEmploiDuTemps();
            $emploiDuTempsId = $edt->getId();
            $matiere= $edt->getMatiere();
            $matiereId = $matiere->getId();
            //dd($matiereId);
            $absManager->save($absence);
           
            return $this->redirectToRoute('front_rvn_absence_matiere',['id' => $matiereId]);
        }
        
        return $this->render(
            'frontend/rvn/absence/edit.html.twig',
            [
                'form'                     => $form->createView(),
                'absence'                  => $absence
            ]
        );
    }

    /**
     * @Route("/download/absence/{id}/justification", name="front_rvn_download_absence_justification", methods={"GET"})
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
     * @Route("/maquettes/{id}/{niveau_id}", name="front_rvn_maquettes_index", methods={"GET", "POST"})
     * @ParamConverter("niveau", options={"mapping": {"niveau_id": "id"}})
     * @param \Symfony\Component\HttpFoundation\Request   $request
     * @param \App\Services\AnneeUniversitaireService     $anneeUniversitaireService
     * @param \App\Manager\AbsencesManager                $absenceManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @IsGranted({"ROLE_RVN"})
     */
    public function maquette(
        Request $request, 
        Mention $mention,
        Niveau $niveau,
        AnneeUniversitaireService       $anneeUniversitaireService,
        AbsencesManager $absManager)
    {
        $currentAnneUniv    = $anneeUniversitaireService->getCurrent();
        $parcours = $request->get('parcours_id');
        $semestre = $request->get('semestre_id');
        $maquetteFilter = ['mention' => $mention->getId(), 'niveau' => $niveau->getId(), 'parcours' => $parcours, 'semestre' => $semestre];
        $maquettes = $absManager->getByMaquette($currentAnneUniv->getId(), $maquetteFilter);

        // dd($maquettes);die;

        return $this->render(
            'frontend/rvn/maquettes.html.twig',
            [
                'maquettes'                 => $maquettes,
                'mention'                   => $mention,
                'niveau'                    => $niveau,
                'parcours'                    => $parcours
            ]
        );
    }

    /**
     * @Route("/matiere/{id}", name="front_rvn_absence_matiere", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request   $request
     * @param \App\Services\AnneeUniversitaireService     $anneeUniversitaireService
     * @param \App\Manager\AbsencesManager                $absenceManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @IsGranted({"ROLE_RVN"})
     */
    public function absenceMatiere(
        Request $request, 
        Matiere $matiere,
        AnneeUniversitaireService       $anneeUniversitaireService,
        AbsencesManager $absManager)
    {
        $currentAnneUniv    = $anneeUniversitaireService->getCurrent();        
        $absences = $absManager->getByMatiere($currentAnneUniv->getId(), $matiere->getId());

        // dd($absences);die;

        return $this->render(
            'frontend/rvn/absence/matieres.html.twig',
            [
                'absences'                 => $absences,
                'matiere'                  => $matiere
            ]
        );
    }


}