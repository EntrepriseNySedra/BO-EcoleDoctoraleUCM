<?php

namespace App\Controller\Frontend;

use App\Entity\Article;
use App\Entity\Actualite;
use App\Entity\Opportunite;
use App\Entity\Rubrique;
use App\Entity\Document;
use App\Repository\ArticleRepository;
use App\Repository\ActualiteRepository;
use App\Repository\RubriqueRepository;
use App\Repository\DocumentRepository;
use App\Repository\OpportuniteRepository;
use App\Repository\MentionRepository;
use App\Repository\ParcoursRepository;
use App\Repository\NiveauRepository;
use App\Manager\EvenementManager;
use App\Form\ArticleType;
use App\Manager\ArticleManager;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Services\MenusService;
use Doctrine\Common\Collections\Criteria;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @param \App\Manager\ArticleManager       $articleManager
     * @param \App\Manager\ActualiteManager     $actualiteManager
     * @param \App\Repository\RubriqueRepository $rubriqueRepository
     * @param \App\Repository\ActualiteRepository $actualiteRepository
     * @param \App\Repository\ArticleRepository $articleRepository
     * @param \App\Repository\EvenementRepository $evtRepository
     * @param \App\Services\MenusService $menusService
     */
   /* public function index(
            MentionRepository $repo,
            ParcoursRepository $parcoursRepository,
            ArticleRepository $articleRepository,
            ActualiteRepository $actualiteRepository,
            RubriqueRepository $rubriqueRepository,
            EvenementManager $evtManager,
            MenusService $menusService,
            LoggerInterface $logger
            ) : Response
    {
        $accueil = $rubriqueRepository->findOneBy(array('code' => 'ACCUEIL'));
        $articlesMiddle  = $articleRepository->findBy(array ('active' => 1, 'emplacement' => 'CONTENT-BRICK-CENTER'),array ('createdAt' => 'DESC'), 4, 0);
        $criteria = new Criteria();
        $criteria->where(Criteria::expr()->eq('emplacement', 'CONTENT-BRICK-SATELLITE'));
        $criteria->orderBy(array('createdAt'=> 'DESC'));
        $criteria->setMaxResults(8);
        $articlesSatellite = $articleRepository->matching($criteria);
        $events = $evtManager->getAll();

        return $this->render(
            'frontend/home/index.html.twig',
            [
                'accueil'           => $accueil,
                'articlesMiddle'    => $articlesMiddle,
                'articlesSatellite' => $articlesSatellite,
                'actualites'        => $actualiteRepository->findBy(array ('active' => 1),array ('createdAt' => 'DESC')),
                'derniereActus'     => $actualiteRepository->getLastActuality(),
                'events'            => $events,
            ]
        );
    } */


    /**
     * @Route("/", name="homepage")
     */
    public function index()
    {
        return $this->redirectToRoute('app_login');
    }
    
    /**
     * Detail function
     * 
     * @Route("/home/article/{slug}", name="details_article")
     * @param \App\Entity\Article                       $article
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Services\MenusService $menusService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getDetailArticle(Article $article, Request $request, ActualiteRepository $actualiteRepository) : Response
    {
        $derniereActus = $actualiteRepository->getLastActuality();
        return $this->render(
            'frontend/home/details_article.html.twig',
            [
                'article'       => $article,
                'derniereActus' => $derniereActus
            ]
        );
    }

    /**
     * Detail function
     *
     * @Route("/actualite", name="list_actualite")
     * @param \App\Entity\Actualite                     $actualite
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Services\MenusService $menusService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getListActualite(Request $request,ActualiteRepository $actualiteRepository) : Response
    {
        $derniereActus = $actualiteRepository->getAllActiveActuality();
        return $this->render(
            'frontend/actualite/index.html.twig',
            [
                'derniereActus' => $derniereActus
            ]
        );
    }

    /**
     * Detail function
     * 
     * @Route("/home/actualite/{slug}", name="details_actualite")
     * @param \App\Entity\Actualite                     $actualite
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Services\MenusService $menusService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getDetailActualite(Actualite $actualite, Request $request,
            ParcoursRepository $parcoursRepository,
            ActualiteRepository $actualiteRepository,
            RubriqueRepository $rubriqueRepository,
            ArticleRepository $articleRepository,
            DocumentRepository $documentRepository,
            MenusService $menusService
            ) : Response
    {
        $derniereActus  = $actualiteRepository->getLastActuality();
        $documents      = $documentRepository->findBy(array ('actualite' => $actualite->getId()));
        $documentList = [];
        foreach ($documents as $document) {
            $documentList[] = $document->getId();
        }
        return $this->render(
            'frontend/home/details_actualite.html.twig',
            [
                'actualite'     => $actualite,
                'derniereActus' => $derniereActus,
                'documentList' => $documentList
            ]
        );
    }

    /**
     * Detail function
     * 
     * @Route("/home/opportunite/{slug}", name="details_opportunite")
     * @param \App\Entity\Opportunite                     $actualite
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Services\MenusService $menusService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getDetailOpportunite(Opportunite $opportunite, Request $request,
            ParcoursRepository $parcoursRepository,
            OpportuniteRepository $opportuniteRepository,
            RubriqueRepository $rubriqueRepository,
            ArticleRepository $articleRepository,
            DocumentRepository $documentRepository,
            MenusService $menusService
            ) : Response
    {
        $derniereOpportunite  = $opportuniteRepository->getLastOpportunite();
        /*$documents      = $documentRepository->findBy(array ('opportunite' => $opportunite->getId()));
        $documentList = [];
        foreach ($documents as $document) {
            $documentList[] = $document->getId();
        }*/
        return $this->render(
            'frontend/home/details_opportunite.html.twig',
            [
                'opportunite'     => $opportunite,
                'derniereOpportunite' => $derniereOpportunite,
               // 'documentList' => $documentList
            ]
        );
    }

    
}
