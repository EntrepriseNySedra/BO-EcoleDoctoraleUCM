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

class HiseController extends AbstractController
{    

    /**
     * List function
     * 
     * @Route("/hise/{slug}", name="hise_article_list")
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
        $toArticleHise      = $articleRepository->findBy(array ('resourceUuid' => $rubrique->getUuid()));
        $toSubMenus = $menusService->getMenus();
        
        if(count($toArticleHise) === 1)
        {
            return $this->render(
                'frontend/hise/details_article.html.twig',
                [
                    'article'       => $toArticleHise[0],
                    'derniereActus' => $toActuList
                ]
            );
        }
        
        
        return $this->render(
            'frontend/hise/list.html.twig',
            [
                'rubrique'        => $rubrique,
                'derniereActus'   => $toActuList,
                'toArticle'       => $toArticleHise
            ]
        );
    }

    /**
     * Detail function
     * 
     * @Route("/hise/article/{slug}", name="hise_details_article")
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
            'frontend/hise/details_article.html.twig',
            [
                'article'       => $article,
                'derniereActus' => $derniereActus
            ]
        );
    }
}
