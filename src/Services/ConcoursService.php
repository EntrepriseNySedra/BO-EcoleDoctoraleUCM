<?php
/**
 * Description of ConcoursService.php.
 *
 * @package App\Services
 * @author  Rachid
 */

namespace App\Services;

use App\Entity\AnneeUniversitaire;
use App\Entity\ConcoursConfig;
use App\Manager\AnneeUniversitaireManager;
use App\Manager\ConcoursConfigManager;
use App\Services\AnneeUniversitaireService;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use \Psr\Container\ContainerInterface;

use App\Entity\ConcoursCandidature;

class ConcoursService
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
		// $anneeUnivManager   = new AnneeUniversitaireManager($this->em, AnneeUniversitaire::class);
        // $anneeUnivService   = new AnneeUniversitaireService($anneeUnivManager);
        $concoursConfManager = new ConcoursConfigManager($this->em, ConcoursConfig::class);
        $concoursConf = $concoursConfManager->loadOneBy([]);
        $concoursAnneeUniv = $concoursConf->getAnneeUniversitaire();
        // $currentAnneUniv    = $anneeUnivService->getCurrent();
        $candidatRepo = $this->doctrine->getRepository(ConcoursCandidature::class);
        $candidatesFromMention = $candidatRepo->findBy(['mention' => $candidature->getMention(), 'anneeUniversitaire' => $concoursAnneeUniv], ['id' => 'DESC']);
        $countMentionCadidate = count($candidatesFromMention);
        $this->_setCandidateMatricule($candidature, $countMentionCadidate);
	}

	/**
	 * Get candidate mention prefix
	 * */
	private function _getCandidateMatriculePrefix($candidate) {
		$matPrefix = $candidate->getMention()->getDiminutif();
    	// if($candidate->getParcours())
    	// 	$matPrefix = $candidate->getParcours()->getDiminutif();

    	return $matPrefix;
	}

	/**
	 * Set matricule
	 * */
	private function _setCandidateMatricule(&$candidate, $mentionCandidatesNum) {
		$anneeUnivManager   = new AnneeUniversitaireManager($this->em, AnneeUniversitaire::class);
        $anneeUnivService   = new AnneeUniversitaireService($anneeUnivManager);
        $currentAnneUniv    = $anneeUnivService->getCurrent();
		$candidatRepo = $this->doctrine->getRepository(ConcoursCandidature::class);
		$matNumlength = 4;
		$prefix = $this->_getCandidateMatriculePrefix($candidate);
        do {
       		$matriculeNumber = "";
        	$countCandidateLen=strlen($mentionCandidatesNum);
	        for($index=$countCandidateLen; $countCandidateLen<$matNumlength; $countCandidateLen++) {
	        	$matriculeNumber .= "0";
	        }
	    	$matriculeNumber = $prefix . $matriculeNumber . $mentionCandidatesNum;
	    	$candidatesFromMention = $candidatRepo->findBy(
	    		[
	    			'immatricule' => $matriculeNumber, 'anneeUniversitaire' => $currentAnneUniv
	    		], []);
	    	// dump($candidatesFromMention);
    		// dump($matriculeNumber);
	    	$mentionCandidatesNum++;

        } while(count($candidatesFromMention) > 0);
        $candidate->setImmatricule($matriculeNumber);
	}

}
