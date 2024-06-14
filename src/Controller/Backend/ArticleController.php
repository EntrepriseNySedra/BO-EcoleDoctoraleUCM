<?php

namespace App\Controller\Backend;

use App\Entity\Article;
use App\Entity\Rubrique;
use App\Entity\Document;
use App\Entity\Departement;
use App\Entity\Mention;
use App\Entity\Niveau;
use App\Entity\Secteur;
use App\Repository\ArticleRepository;
use App\Repository\ActualiteRepository;
use App\Repository\RubriqueRepository;
use App\Repository\DocumentRepository;
use App\Repository\DepartementRepository;
use App\Repository\MentionRepository;
use App\Repository\NiveauRepository;
use App\Repository\SecteurRepository;
use App\Form\ArticleType;
use App\Manager\ArticleManager;
use App\Manager\RubriqueManager;
use App\Services\BreadcrumbsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use \Psr\Container\ContainerInterface;
use Cocur\Slugify\Slugify;

class ArticleController extends AbstractController
{
    /**
     * Listing articles
     *
     * @Route("/admin/articles/list", name="admin_articles_list", methods={ "GET" })
     * @param \App\Repository\ArticleRepository $articleRepository
     * @param \App\Services\BreadcrumbsService $breadcrumbsService
     * @param \App\Manager\ArticleManager    $articleManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(BreadcrumbsService $breadcrumbsService, articleManager $articleManager)
    {

        $articles = $articleManager->getAllWithParent();
        $articleEmplacement = array_flip(Article::$emplacementTypeList);
        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Articles', $this->generateUrl('admin_articles_list'))
            ->add('Liste')
        ;

        return $this->render(
            'backend/articles/list.html.twig',
            [
                'articleEmplacement' => $articleEmplacement,
                'articles'    => $articles,
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs()
            ]
        );
    }

    /**
     * Adding page
     * @Route("/admin/articles/add", name="admin_articles_add", methods={ "GET", "POST" })
     *
     * @param \App\Services\BreadcrumbsService          $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\ArticleManager             $manager
     * @param \App\Repository\RubriqueRepository $rubriqueRepository
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function new(BreadcrumbsService $breadcrumbsService, ContainerInterface $container, Request $request, ArticleManager $manager, 
            RubriqueRepository $rubriqueRepository, 
            RubriqueManager $rubriqueManager,
            DepartementRepository $departementRepository,
            MentionRepository $mentionRepository, 
            NiveauRepository $niveauRepository, 
            SecteurRepository $secteurRepository)
    {
        $artcilerRessourceTypeList = Article::$ressourceTypeList;
        $article = new Article();
        $rubriques = $rubriqueManager->getAllStructParentChild();
        $form   = $this->createForm(
            ArticleType::class,
            $article
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();
            
            // Upload file
            if ($file) {
                $directory      = $container->getParameter('article_directory');
                $uploader       = new \App\Services\FileUploader($directory);
                
                $fileDirectory = $article->getUuid();
                $file_dispaly   = $uploader->upload($file,$directory,$fileDirectory);
                $article->setPath($file_dispaly["filename"]);
            }
            $slugify = new Slugify();
            $slug = $slugify->slugify($article->getTitle());
            $article->setSlug($slug);

            $manager->save($article);
            return $this->redirectToRoute('admin_articles_list');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Articles', $this->generateUrl('admin_articles_list'))
            ->add('Ajout')
        ;

        return $this->render('backend/articles/add.html.twig', 
            [
                'ressourceTypeList' => $artcilerRessourceTypeList,
                'article'     => $article,
                'form'        => $form->createView(),
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
                'rubriques'   => $rubriques,
                'departements'=> $departementRepository->findAll(),
                'mentions'    => $mentionRepository->findAll(),
                'niveaux'     => $niveauRepository->findAll()
            ]
        );
    }

    /**
     * @Route("/admin/articles/{id}/edit", name="admin_articles_edit", methods={"GET","POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Article                     $article
     * @param \App\Repository\RubriqueRepository $rubriqueRepository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request, 
            ContainerInterface $container,
            Article $article,
            RubriqueRepository $rubriqueRepository, 
            RubriqueManager $rubriqueManager,
            DepartementRepository $departementRepository,
            MentionRepository $mentionRepository, 
            NiveauRepository $niveauRepository, 
            SecteurRepository $secteurRepository
            ) : Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        $artcilerRessourceTypeList = Article::$ressourceTypeList;
        $rubriques = $rubriqueManager->getAllStructParentChild();

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();
            
            // Upload file
            if ($file) {
                $directory      = $container->getParameter('article_directory');
                $uploader       = new \App\Services\FileUploader($directory);
                
                $fileDirectory = $article->getUuid();
                $file_dispaly   = $uploader->upload($file,$directory,$fileDirectory);
                $article->setPath($file_dispaly["filename"]);
            }
            $slugify = new Slugify();
            $slug = $slugify->slugify($article->getTitle());
            $article->setSlug($slug);

            // dump($article);die;

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_articles_list');
        }

        return $this->render(
            'backend/articles/edit.html.twig',
            [
                'ressourceTypeList' => $artcilerRessourceTypeList,
                'article'   => $article,
                'form'      => $form->createView(),
                'rubriques' => $rubriques,
                'departements'=> $departementRepository->findAll(),
                'mentions'    => $mentionRepository->findAll(),
                'niveaux'     => $niveauRepository->findAll()
            ]
        );
    }

    /**
     * @Route("/admin/articles/{id}", name="admin_articles_delete", methods={"POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Article                     $article
     * @param \App\Repository\DocumentRepository $documentRepository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(Request $request, Article $article, DocumentRepository $documentRepository) : Response
    {
        if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->request->get('_token'))) {
            $document = $documentRepository->findOneBy(array('article'=>$article->getId()));
            if($document){
               $this->addFlash('error', 'Suppression non autorisée'); 
            }
            else{
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($article);
                $entityManager->flush();
                $this->addFlash('success', 'Succès suppression');
            }
        }

        return $this->redirectToRoute('admin_articles_list');
    }
}
