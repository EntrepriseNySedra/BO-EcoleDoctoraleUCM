<?php
/**
 * Description of EtudiantService.php.
 *
 * @package App\Services
 * @author  Rachid
 */

namespace App\Services;

use App\Entity\AnneeUniversitaire;
use App\Entity\Civility;
use App\Entity\Etudiant;
use App\Manager\AnneeUniversitaireManager;
use App\Services\AnneeUniversitaireService;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use \Psr\Container\ContainerInterface;

class EtudiantService
{

	/**
	 * @var \Symfony\Component\HttpKernel\KernelInterface
	 */
	private $kernel;

	/**
	 * @var \Symfony\Component\DependencyInjection\ContainerInterface
	 */
	private $container;

    /**
     * Doctrine
     * */
    private $doctrine;

    /**
     * Entity manager
     * */
    private $em;
	

	/**
	 * constructor.
	 *
	 * @param KernelInterface $kernel
	 */
	public function __construct(KernelInterface $kernel)
	{
		$this->kernel    = $kernel;
		$this->container = $this->kernel->getContainer();
        $this->doctrine  = $this->container->get('doctrine');
        $this->em  = $this->doctrine->getManager();
	}


	/**
	 * @return array
	 */
	public function setMatricule(&$candidature)
	{
		$anneeUnivManager   = new AnneeUniversitaireManager($this->em, AnneeUniversitaire::class);
        $anneeUnivService   = new AnneeUniversitaireService($anneeUnivManager);

        $anneeUnivManager   = new AnneeUniversitaireManager($this->em, AnneeUniversitaire::class);


        $currentAnneUniv    = $anneeUnivService->getCurrent();
        $etudiantRepo = $this->doctrine->getRepository(Etudiant::class);
        $LastStudent = $etudiantRepo->findLastRegistered($candidature->getMention());

        $this->_setCandidateMatricule($candidature, $LastStudent);
	}

	/**
	 * @return array
	 */
	public function setMatriculeBack(&$candidature)
	{
		$anneeUnivManager   = new AnneeUniversitaireManager($this->em, AnneeUniversitaire::class);
        $anneeUnivService   = new AnneeUniversitaireService($anneeUnivManager);
        $currentAnneUniv    = $anneeUnivService->getCurrent();
        $candidatRepo = $this->doctrine->getRepository(ConcoursCandidature::class);
        $filterOptions = [
        	'mention' => $candidature->getMention(), 'anneeUniversitaire' => $currentAnneUniv
        ];
        if($candidature->getParcours())
        	$filterOptions['parcours'] = $candidature->getParcours();
        $AllStudent = $candidatRepo->findBy($filterOptions);
        $this->_setCandidateMatricule($candidature, count($AllStudent));
	}

	/**
	 * Get candidate mention prefix
	 * */
	private function _getCandidateMatriculePrefix($candidate) {
		$matPrefix = $candidate->getMention()->getDiminutif();
    	// if($candidate->getParcours())
    	// 	$matPrefix = $candidate->getParcours()->getDiminutif();

    	return substr($matPrefix, 0, 2);
	}

	/**
	 * Set matricule
	 * */
	private function _setCandidateMatricule(&$candidate, $lastStudent) {
		$anneeUnivManager   = new AnneeUniversitaireManager($this->em, AnneeUniversitaire::class);
        $anneeUnivService   = new AnneeUniversitaireService($anneeUnivManager);
        $currentAnneUniv    = $anneeUnivService->getCurrent();
        $anneeUnivDiminutif = substr($currentAnneUniv->getAnnee(), 2) - 1;
		$etudiantRepo = $this->doctrine->getRepository(Etudiant::class);
		$lastStudentImm = $lastStudent ? substr($lastStudent->getImmatricule(), 5) : 6000;
		$studentGender = "F";
        if(strtolower(trim(Civility::MONSIEUR) ) === strtolower(trim($candidate->getCivility()->getLibelle())))
        	$studentGender = "H";
		$currentImmNumber = $lastStudentImm + 1;
		$diminutif = $this->_getCandidateMatriculePrefix($candidate);
        do {
	    	$matricule = $diminutif . $anneeUnivDiminutif . $studentGender . $currentImmNumber;
	    	$getDuplicateMat = $etudiantRepo->findBy(['immatricule' => $matricule], []);
	    	$currentImmNumber++;
        } while(count($getDuplicateMat) > 0);
        $candidate->setImmatricule($matricule);
	}

	/**
	 * Set matricule
	 * */
	private function _setCandidateMatriculeBack(&$candidate, $mentionCandidatesNum) {
		$matNumlength = 9;
		$prefix = $this->_getCandidateMatriculePrefix($candidate);
        do {
       		$matriculeNumber = "";
        	$countCandidateLen=strlen($mentionCandidatesNum);
	        for($index=$countCandidateLen; $countCandidateLen<$matNumlength; $countCandidateLen++) {
	        	$matriculeNumber .= "0";
	        }
	    	$matriculeNumber = $prefix . $matriculeNumber . $mentionCandidatesNum;
	    	$candidatesFromMention = $etudiantRepo->findBy(
	    		[
	    			'immatricule' => $matriculeNumber, 'anneeUniversitaire' => $currentAnneUniv
	    		], []);
	    	$mentionCandidatesNum++;
        } while(count($candidatesFromMention) > 0);
        $candidate->setImmatricule($matriculeNumber);
	}

	/**
	 * @return array
	 */
	public function setMatriculeOld(&$student)
	{
		$matNumlength = 9;
		$matriculeNumber = "";
		$lastStudentId = 1;
        $etudiantRepo = $this->doctrine->getRepository(Etudiant::class);
        $lastStudent = $etudiantRepo->findOneBy([], ['id' => 'DESC']);
        if($lastStudent)
        	$lastStudentId = $lastStudent->getId();
        $lastIdLen=strlen($lastStudentId);
        for($index=$lastIdLen; $lastIdLen<$matNumlength; $lastIdLen++) {
        	$matriculeNumber .= "0";
        }
        $matriculeNumber .= $lastStudentId + 1;
    	$matPrefix = $student->getMention()->getDiminutif();
    	if($student->getParcours())
    		$matPrefix = $student->getParcours()->getDiminutif();
    	$matriculeNumber = $matPrefix . $matriculeNumber;
    	$student->setImmatricule($matriculeNumber);
	}

}
