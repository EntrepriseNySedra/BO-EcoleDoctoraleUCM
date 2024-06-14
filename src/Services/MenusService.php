<?php
/**
 * Description of BreadCrumbService.php.
 *
 * @package App\Services
 * @author  Jefferson
 */

namespace App\Services;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use \Psr\Container\ContainerInterface;
use App\Entity\Mention;
use App\Entity\Rubrique;
use App\Entity\Actualite;
use App\Entity\Parcours;
use App\Repository\MentionRepository;

class MenusService
{

	/**
	 * @var \Symfony\Component\HttpKernel\KernelInterface
	 */
	private $kernel;

	/**
	 * @var \Symfony\Component\DependencyInjection\ContainerInterface
	 */
	private $container;
	const CAMPUS = "CAMPUS";
	const HOME = "ACCUEIL";
	const RECHERCHE = "RECHERCHE";
    const HISE = "HISE";
    const LIBRARY = "BIBLIOTHEQUE";

	/**
	 * @var array
	 */
	protected $menus = [];

	/**
	 * constructor.
	 *
	 * @param KernelInterface $kernel
	 */
	public function __construct(KernelInterface $kernel)
	{
		$this->kernel    = $kernel;
		$this->container = $this->kernel->getContainer();
	}

	/**
	 * @return array
	 */
	public function getMenus()
	{
			$oRepository = $this->container->get('doctrine');
			$toSubRubrique = [];
			//sub rubrique formation
            $mentionsRepository = $oRepository->getRepository(Mention::class);
            $mentions = $mentionsRepository->findBy(array('active' => 1));

            // dump($mentions);die;

            $itemsMentions = [];
            foreach ($mentions as $mention) {
                $itemMention['id'] = $mention->getId();
                $itemMention['uuid'] = $mention->getUuid();
                $itemMention['slug'] = $mention->getSlug();
                $itemMention['nom'] = $mention->getNom();
                $itemMention['objectif'] = $mention->getObjectif();
                $itemMention['description'] = $mention->getDescription();
                $itemMention['path'] = $mention->getPath();

                $itemsParcours = [];
                $parcours = $mention->getParcours();
                foreach ($parcours as $parcour){
                    if($mention->getId() == $parcour->getMention()->getId()){
                        $itemParcour = [];
                        $itemParcour['id'] = $parcour->getId();
                        $itemParcour['nom'] = $parcour->getNom();
                        $itemParcour['parcourscool'] = $parcour->getParcourscool();
                        $itemsParcours[] = $itemParcour;
                    }
                }
                $itemMention["parcours"] = $itemsParcours;
                array_push($itemsMentions, $itemMention);
            }

            //Rubrique Repository
            $oRubriqueRepository = $oRepository->getRepository(Rubrique::class);
            //Sub rubrique for campus
            $oRubriqueCampus = $oRubriqueRepository->findOneBy(array('code' => $this::CAMPUS));
            $tCampusSubRubrique = $oRubriqueRepository->findBy(array('parent' => ($oRubriqueCampus ? $oRubriqueCampus->getId() : null)), array('title' => 'ASC'));

            $oRubriqueHome = $oRubriqueRepository->findOneBy(array('code' => $this::HOME));
            $tHomeSubRubrique = $oRubriqueRepository->findBy(array('parent' => ($oRubriqueHome ? $oRubriqueHome->getId() : null)), array('title' => 'ASC'));

            $oRubriqueRecherche = $oRubriqueRepository->findOneBy(array('code' => $this::RECHERCHE));
            $tRechercheSubRubrique = $oRubriqueRepository->findBy(array('parent' => ($oRubriqueRecherche ? $oRubriqueRecherche->getId() : null)), array('title' => 'ASC'));

            $oRubriqueHise = $oRubriqueRepository->findOneBy(array('code' => $this::HISE));
            $tHiseSubRubrique = $oRubriqueRepository->findBy(array('parent' => ($oRubriqueHise ? $oRubriqueHise->getId() : null)), array('title' => 'ASC'));

            $oRubriqueBiblio = $oRubriqueRepository->findOneBy(array('code' => $this::LIBRARY));
            $tBiblioSubRubrique = $oRubriqueRepository->findBy(array('parent' => ($oRubriqueBiblio ? $oRubriqueBiblio->getId() : null)), array('title' => 'ASC'));
            
            $toSubRubrique['formation'] = $itemsMentions;
            $toSubRubrique['campus'] = $tCampusSubRubrique;
            $toSubRubrique['home'] = $tHomeSubRubrique;
            $toSubRubrique['recherche'] = $tRechercheSubRubrique;
            $toSubRubrique['hise'] = $tHiseSubRubrique;
            $toSubRubrique['library'] = $tBiblioSubRubrique;

            return $toSubRubrique;
	}

}
