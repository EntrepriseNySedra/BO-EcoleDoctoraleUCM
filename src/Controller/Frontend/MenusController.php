<?php

namespace App\Controller\Frontend;

use App\Entity\Article;
use App\Entity\Actualite;
use App\Entity\Rubrique;
use App\Entity\Document;
use App\Entity\Mention;
use App\Repository\ArticleRepository;
use App\Repository\ActualiteRepository;
use App\Repository\RubriqueRepository;
use App\Repository\DocumentRepository;
use App\Repository\MentionRepository;
use App\Repository\ParcoursRepository;
use App\Repository\NiveauRepository;
use App\Repository\OpportuniteRepository;
use App\Form\ArticleType;
use App\Manager\ArticleManager;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Services\MenusService;
use Doctrine\Common\Collections\Criteria;

class MenusController extends AbstractController
{
    const CAMPUS = "CAMPUS";
    const HOME = "ACCUEIL";
    const RECHERCHE = "RECHERCHE";
    const HISE = "HISE";
    const LIBRARY = "BIBLIOTHEQUE";
    
    /**
     * @Route("/menus", name="menus")
     * @param \App\Manager\ArticleManager       $articleManager
     * @param \App\Manager\ActualiteManager     $actualiteManager
     * @param \App\Repository\MentionRepository $mentionRepository
     * @param \App\Repository\RubriqueRepository $rubriqueRepository
     * @param \App\Repository\RubriqueRepository $rubriqueRepository
     * @param \App\Repository\ActualiteRepository $actualiteRepository
     * @param \App\Repository\ArticleRepository $articleRepository
     * @param \App\Services\MenusService $menusService
     */
    public function index(
            MentionRepository $mentionRepository,
            ParcoursRepository $parcoursRepository,
            ArticleRepository $articleRepository,
            ActualiteRepository $actualiteRepository,
            RubriqueRepository $rubriqueRepository,
            OpportuniteRepository $opportuniteRepository,
            MenusService $menusService,
            LoggerInterface $logger
            ) : Response
    {
            $oRepository = $this->container->get('doctrine');
            $toSubRubrique = [];
            //sub rubrique formation
            $mentionsRepository = $oRepository->getRepository(Mention::class);
            
            $mentions = $mentionRepository->findALl();
         
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
        
            //Menu actualite
            $tActuSubRubrique = $actualiteRepository->getLastActuality();
            $tActuSubRubrique = $actualiteRepository->getLastActuality();
            $tActuSubRubrique = $actualiteRepository->getLastActuality();
            //opportunite
            $tOpportuniteSubRubrique= $opportuniteRepository->getAllOpportunite();
            //dd($tOpportuniteSubRubrique);die;

            $toSubRubrique['formation'] = $itemsMentions;
            $toSubRubrique['actus'] = $tActuSubRubrique;
            $toSubRubrique['opportunite'] = $tOpportuniteSubRubrique;
            //dd($toSubRubrique['opportunite']);die;
            $toSubRubrique['campus'] = $tCampusSubRubrique;
            $toSubRubrique['home'] = $tHomeSubRubrique;
            $toSubRubrique['recherche'] = $tRechercheSubRubrique;
            $toSubRubrique['hise'] = $tHiseSubRubrique;
            $toSubRubrique['library'] = $tBiblioSubRubrique;
           
            return $this->render('frontend/layout/menu.html.twig', [
                'toSubMenus'  => $toSubRubrique
            ]);
    }
}
