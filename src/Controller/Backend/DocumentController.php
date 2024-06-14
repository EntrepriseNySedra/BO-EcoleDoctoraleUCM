<?php

namespace App\Controller\Backend;

use App\Entity\Document;
use App\Entity\Actualite;
use App\Entity\Article;
use App\Entity\Media;
use App\Form\DocumentType;
use App\Manager\DocumentManager;
use App\Repository\DocumentRepository;
use App\Repository\ActualiteRepository;
use App\Repository\ArticleRepository;
use App\Repository\MediasRepository;
use App\Services\BreadcrumbsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DocumentController extends AbstractController
{
    /**
     * Listing documents
     *
     * @Route("/admin/documents/list", name="admin_documents_list", methods={ "GET" })
     * @param \App\Repository\DocumentRepository $documentRepository
     * @param \App\Services\BreadcrumbsService   $breadcrumbsService
     * @param \App\Manager\DocumentManager       $documentManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(BreadcrumbsService $breadcrumbsService, DocumentRepository $documentRepository)
    {

        $documents = [];

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Documents', $this->generateUrl('admin_documents_list'))
            ->add('Liste')
        ;
        return $this->render(
            'backend/documents/list.html.twig',
            [
                'documents'  => $documentRepository->findAll(),
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Adding page
     * @Route("/admin/documents/add", name="admin_documents_add", methods={ "GET", "POST" })
     *
     * @param \App\Services\BreadcrumbsService          $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\DocumentManager              $manager
     * @param \App\Repository\ActualiteRepository       $actualiteRepository
     * @param \App\Repository\ArticleRepository         $articleRepository
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function new(BreadcrumbsService $breadcrumbsService, Request $request, DocumentManager $manager
            , ActualiteRepository $actualiteRepository
            , ArticleRepository $articleRepository
            )
    {
        $document = new Document();
        $form   = $this->createForm(
            DocumentType::class,
            $document
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->save($document);

            return $this->redirectToRoute('admin_documents_list');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Documents', $this->generateUrl('admin_documents_list'))
            ->add('Ajout')
        ;

        return $this->render('backend/documents/add.html.twig', 
            [
                'document'    => $document,
                'form'        => $form->createView(),
                'actualites'  => $actualiteRepository->findAll(),
                'articles'    => $articleRepository->findAll(),
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * @Route("/admin/documents/{id}/edit", name="admin_documents_edit", methods={"GET","POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Document                      $document
     * @param \App\Repository\ActualiteRepository       $actualiteRepository
     * @param \App\Repository\ArticleRepository         $articleRepository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request, Document $document, ActualiteRepository $actualiteRepository, ArticleRepository $articleRepository) : Response
    {
        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_documents_list');
        }

        return $this->render(
            'backend/documents/edit.html.twig',
            [
                'document' => $document,
                'form'   => $form->createView(),
                'actualites'  => $actualiteRepository->findAll(),
                'articles'    => $articleRepository->findAll(),
            ]
        );
    }

    /**
     * @Route("/admin/documents/{id}", name="admin_documents_delete", methods={"POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Document                     $document
     * @param \App\Entity\Media                        $media
     * @param \App\Repository\MediasRepository $mediasRepository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(Request $request, Document $document, MediasRepository $mediasRepository) : Response
    {
        if ($this->isCsrfTokenValid('delete' . $document->getId(), $request->request->get('_token'))) {
            $media = $mediasRepository->findOneBy(array('document'=>$document->getId()));
            if($media){
               $this->addFlash('error', 'Suppression non autorisée'); 
            }
            else{
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($document);
                $entityManager->flush();
                $this->addFlash('success', 'Succès suppression');
            }
        }

        return $this->redirectToRoute('admin_documents_list');
    }
}
