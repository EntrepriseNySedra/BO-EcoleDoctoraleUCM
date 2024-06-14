<?php

namespace App\Controller\Frontend;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Rubrique;
use App\Entity\Article;
use App\Repository\RubriqueRepository;
use App\Repository\ArticleRepository;
use App\Repository\ActualiteRepository;
use App\Services\MenusService;

class CampusController extends AbstractController
{
    /**
     * Campus homepage
     * @Route("campus", name="Campus")
     */
    public function index(ActualiteRepository $actualiteRepository, MenusService $menusService) : Response
    {
        $toActuList           = $actualiteRepository->getLastActuality();
        return $this->render(
            'frontend/campus/index.html.twig',
            [
                'derniereActus'   => $toActuList,
            ]
        );
    }

    /**
     * List function
     * 
     * @Route("/campus/{slug}", name="list_article_campus")
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
        $toArticleCampus      = $articleRepository->findBy(array ('resourceUuid' => $rubrique->getUuid()));
        
        $toSubMenus = $menusService->getMenus();
        return $this->render(
            'frontend/campus/list.html.twig',
            [
                'rubrique'        => $rubrique,
                'derniereActus'   => $toActuList,
                'toArticle'       => $toArticleCampus
            ]
        );
    }

    /**
     * Detail function
     * 
     * @Route("/campus/detail/{slug}", name="detail_article_campus")
     * @param \App\Entity\Article                       $article
     * @param \App\Repository\RubriqueRepository        $rubriqueRepo
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Services\MenusService $menusService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showArticle(Article $article, Request $request, ActualiteRepository $actualiteRepository, MenusService $menusService, RubriqueRepository $rubriqueRepo) : Response
    {
        $rubrique = $rubriqueRepo->findOneBy(['uuid' => $article->getResourceUuid()]);
        $toActuList           = $actualiteRepository->getLastActuality();        
        $toSubMenus = $menusService->getMenus();
        return $this->render(
            'frontend/campus/details.html.twig',
            [
                'rubrique'        => $rubrique,
                'article'         => $article,
                'derniereActus'   => $toActuList,
                'toSubMenus'      => $toSubMenus
            ]
        );
    }
}