<?php

namespace App\Controller\Frontend;

use App\Entity\Mention;
use App\Repository\DepartementRepository;
use App\Repository\MentionRepository;
use App\Repository\ParcoursRepository;
use App\Repository\NiveauRepository;
use App\Repository\ActualiteRepository;
use App\Repository\RubriqueRepository;
use App\Manager\ConcoursConfigManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Services\MenusService;

class FormationController extends AbstractController
{
    /**
     * Get Mention function
     *
     * @Route("/formation", name="home_formation")
     * @param \App\Repository\MentionRepository         $repo
     * @param \App\Repository\ActualiteRepository $actualiteRepository
     * @param \App\Repository\ParcoursRepository $parcoursRepository
     * @param \App\Repository\RubriqueRepository $rubriqueRepository
     * @param \App\Services\MenusService $menusService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(
            DepartementRepository $departementRepo,
            MentionRepository $repo,
            ParcoursRepository $parcoursRepository,
            ActualiteRepository $actualiteRepository,
            RubriqueRepository $rubriqueRepository,
            MenusService $menusService
            ) : Response
    {
        $oFormation = $rubriqueRepository->findOneBy(['code' => 'FORMATION']);
        $departements = $departementRepo->findBy([], ['ordre' => 'ASC']);
        
        $mentions = $repo->findby(array('active' => 1));
        $results = [];

        foreach($departements as $depart ) {
            $results[$depart->getNom()] = $depart->getMentions();
        }

        $actualites  = $actualiteRepository->findBy(array ('active' => 1),array ('createdAt' => 'DESC'));
        $derniereActus = $actualiteRepository->getLastActuality();
        $parcours = $parcoursRepository->findBy(array ('active' => 1),array ('createdAt' => 'DESC'));
        
        $toSubMenus = $menusService->getMenus();
                
        return $this->render(
            'frontend/formation/formation.html.twig', [
                'results'       => $results,
                'oFormation'    => $oFormation,
                'mentions'      => $toSubMenus['formation'],
                'parcours'      => $parcours,
                'actualites'    => $actualites,
                'derniereActus' => $derniereActus,
                'toSubMenus'    => $toSubMenus,
            ]
        );
    }

    /**
     * Detail function
     * 
     * @Route("/formation/{slug}", name="details_formation")
     * @param \App\Entity\Mention                       $mention
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Services\MenusService $menusService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(Mention $mention, Request $request,
            MentionRepository $repo,
            ParcoursRepository $parcoursRepository,
            ActualiteRepository $actualiteRepository,
            RubriqueRepository $rubriqueRepository,
            ConcoursConfigManager $concoursManager,
            MenusService $menusService
            ) : Response
    {
        $derniereActus = $actualiteRepository->getLastActuality();
        $allmentions = $this->prepareMentions($repo, $parcoursRepository, $actualiteRepository, $rubriqueRepository);
        $toSubMenus = $menusService->getMenus();
        $isConcoursActive = $concoursManager->isActive();

        return $this->render(
            'frontend/formation/details.html.twig',
            [
                'mention'       => $mention,
                'concours'      => $isConcoursActive,
                'mentions'      => $allmentions,
                'derniereActus' => $derniereActus,
                'toSubMenus'    => $toSubMenus
            ]
        );
    }
    
    public function prepareMentions(
            MentionRepository $repo,
            ParcoursRepository $parcoursRepository,
            ActualiteRepository $actualiteRepository,
            RubriqueRepository $rubriqueRepository
            )
    {
        $mentions = $repo->findBy(array('active' => 1));
        $rubriques = $rubriqueRepository->findBy(array ('active' => 1),array ('createdAt' => 'DESC'));
        $actualites  = $actualiteRepository->findBy(array ('active' => 1),array ('createdAt' => 'DESC'));
        $derniereActus = $actualiteRepository->getLastActuality();
        $parcours = $parcoursRepository->findBy(array ('active' => 1),array ('createdAt' => 'DESC'));
        
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

        return $itemsMentions;
    }
}
