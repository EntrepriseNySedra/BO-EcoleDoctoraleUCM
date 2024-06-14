<?php
/**
 * Description of EcolageService.php.
 *
 * @package App\Services
 * @author  Rachid
 */

namespace App\Services;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use \Psr\Container\ContainerInterface;

use App\Entity\AnneeUniversitaire;
use App\Entity\Actualite;
use App\Entity\Ecolage;
use App\Entity\Etudiant;
use App\Entity\FraisScolarite;
use App\Entity\Parcours;
use App\Entity\Rubrique;

use App\Manager\AnneeUniversitaireManager;
use App\Repository\MentionRepository;
use App\Services\AnneeUniversitaireService;

class EcolageService
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

    public $classEcolage = [0,0];
    private $semestre1Value;
    private $semestre2Value;

	

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
	public function updateEtudiantStatus($etudiantIds=array())
	{
		$anneeUnivManager   = new AnneeUniversitaireManager($this->em, AnneeUniversitaire::class);
        $anneeUnivService   = new AnneeUniversitaireService($anneeUnivManager);
        $currentAnneUniv    = $anneeUnivService->getCurrent();
        $fraisScolRepo = $this->doctrine->getRepository(FraisScolarite::class);
        $ecolageRepo = $this->doctrine->getRepository(Ecolage::class);
        $etudiantRepo = $this->doctrine->getRepository(Etudiant::class);
        $dataList = $fraisScolRepo->getPerEtudiantGoupBySem($etudiantIds, $currentAnneUniv->getId());
    	foreach($dataList as $item){
    		$filters = [
    				'mention' => $item['mention'],
    				'niveau' => $item['niveau'],
    				'semestre' => $item['semestre']
    		];
    		$etudiant = $etudiantRepo->find($item['etudiantId']);
    		if($parcours=$item['parcours'])
    			$filters['parcours'] = $parcours;
    		$currEcolage = $ecolageRepo->findOneBy($filters, []);
    		
    		//if etudiant semestre ecolage inf class ecolage and date limit is out, denied etudiant
    		if($currEcolage->getLimitDate() <= new \DateTime() && $item['montant'] < $currEcolage->getMontant()) {
    			$etudiant->setStatus(Etudiant::STATUS_DENIED_ECOLAGE);
    			$this->em->persist($etudiant);
    		} else {
    			$etudiant->setStatus(Etudiant::STATUS_ACTIVE);
    			$this->em->persist($etudiant);
    		}

    		$this->em->flush();
    	}
       
        return [];
	}

	public function getSemetre1Value()
	{
		return $this->classEcolage[0];
	}

	public function getSemetre2Value()
	{
		return $this->classEcolage[1];
	}

}
