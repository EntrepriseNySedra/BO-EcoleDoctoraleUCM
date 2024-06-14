<?php

namespace App\Controller\Frontend;

use App\Entity\Article;
use App\Entity\Rubrique;
use App\Repository\ArticleRepository;
use App\Repository\ActualiteRepository;
use App\Repository\RubriqueRepository;
use App\Manager\ArticleManager;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Services\MenusService;
use Doctrine\Common\Collections\Criteria;

class RechercheController extends AbstractController
{    

    /**
     * List function
     * 
     * @Route("/recherche/{slug}", name="recherche_article_list")
     * @param \App\Entity\Rubrique                       $rubrique
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Services\MenusService $menusService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listArticle(Rubrique $rubrique, Request $request, RubriqueRepository $rubriqueRepository, ArticleRepository $articleRepository, ActualiteRepository $actualiteRepository,
            MenusService $menusService
            ) : Response
    {
        $toActuList           = $actualiteRepository->getLastActuality();
        $toArticleRecherche      = $articleRepository->findBy(array ('resourceUuid' => $rubrique->getUuid()));
        $toSubMenus = $menusService->getMenus();
        
        if(count($toArticleRecherche) === 1)
        {
            return $this->render(
                'frontend/recherche/details_article.html.twig',
                [
                    'article'       => $toArticleRecherche[0],
                    'derniereActus' => $toActuList
                ]
            );
        }
        
        
        return $this->render(
            'frontend/recherche/list.html.twig',
            [
                'rubrique'        => $rubrique,
                'derniereActus'   => $toActuList,
                'toArticle'       => $toArticleRecherche
            ]
        );
    }

    /**
     * Detail function
     * 
     * @Route("/recherche/article/{slug}", name="recherche_details_article")
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
            'frontend/recherche/details_article.html.twig',
            [
                'article'       => $article,
                'derniereActus' => $derniereActus
            ]
        );
    }
}
