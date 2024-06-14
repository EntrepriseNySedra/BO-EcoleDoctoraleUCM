<?php

namespace App\Controller\Backend;

use App\Entity\Actualite;
use App\Entity\Rubrique;
use App\Entity\Departement;
use App\Entity\Mention;
use App\Entity\Niveau;
use App\Entity\Secteur;
use App\Repository\ActualiteRepository;
use App\Repository\RubriqueRepository;
use App\Repository\DocumentRepository;
use App\Repository\DepartementRepository;
use App\Repository\MentionRepository;
use App\Repository\NiveauRepository;
use App\Repository\SecteurRepository;
use App\Form\ActualiteType;
use App\Manager\ActualiteManager;
use App\Manager\RubriqueManager;
use App\Services\BreadcrumbsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use \Psr\Container\ContainerInterface;
use Cocur\Slugify\Slugify;

class ActualiteController extends AbstractController
{
    /**
     * Listing actualites
     *
     * @Route("/admin/actualites/list", name="admin_actualites_list", methods={ "GET" })
     * @param \App\Repository\ActualiteRepository $actualiteRepository
     * @param \App\Services\BreadcrumbsService $breadcrumbsService
     * @param \App\Manager\ActualiteManager    $actualiteManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(BreadcrumbsService $breadcrumbsService, ActualiteRepository $actualiteRepository, ActualiteManager $actualiteManager)
    {

        $actualites = $actualiteManager->getAllWithParent();
        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Actualites', $this->generateUrl('admin_actualites_list'))
            ->add('Liste')
        ;

        return $this->render(
            'backend/actualites/list.html.twig',
            [
                'actualites'  => $actualites,
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Adding page
     * @Route("/admin/actualites/add", name="admin_actualites_add", methods={ "GET", "POST" })
     *
     * @param \App\Services\BreadcrumbsService          $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\ActualiteManager             $manager
     * @param \App\Repository\DepartementRepository     $departementRepository
     * @param \App\Repository\MentionRepository         $mentionRepository
     * @param \App\Repository\NiveauRepository          $niveauRepository
     * @param \App\Repository\SecteurRepository         $secteurRepository
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function new(BreadcrumbsService $breadcrumbsService, Request $request, ContainerInterface $container, ActualiteManager $manager, 
            RubriqueRepository $rubriqueRepository, 
            DepartementRepository $departementRepository,
            MentionRepository $mentionRepository, 
            NiveauRepository $niveauRepository, 
            SecteurRepository $secteurRepository,
            RubriqueManager $rubriqueManager)
    {
        $actualite = new Actualite();
        $form   = $this->createForm(
            ActualiteType::class,
            $actualite
        );
        $rubriques = $rubriqueManager->getAllStructParentChild();
        $artcilerRessourceTypeList = Actualite::$ressourceTypeList;
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slugify = new Slugify();
            $slug = $slugify->slugify($actualite->getTitle());
            $actualite->setSlug($slug);
            $file = $form->get('file')->getData();
            
            // Upload file
            if ($file) {
                $directory      = $container->getParameter('actualite_directory');
                $uploader       = new \App\Services\FileUploader($directory);
                
                $fileDirectory = $actualite->getUuid();
                $file_dispaly   = $uploader->upload($file,$directory,$fileDirectory);
                $actualite->setPath($file_dispaly["filename"]);
            }
            $manager->save($actualite);

            return $this->redirectToRoute('admin_actualites_list');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Actualites', $this->generateUrl('admin_actualites_list'))
            ->add('Ajout')
        ;

        return $this->render('backend/actualites/add.html.twig', 
            [
                'actualite'   => $actualite,
                'form'        => $form->createView(),
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
                'rubriques'   => $rubriques,
                'departements'=> $departementRepository->findAll(),
                'mentions'    => $mentionRepository->findAll(),
                'niveaux'     => $niveauRepository->findAll(),
                'ressourceTypeList' => $artcilerRessourceTypeList,
            ]
        );
    }

    /**
     * @Route("/admin/actualites/{id}/edit", name="admin_actualites_edit", methods={"GET","POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Actualite                     $actualite
     * @param \App\Repository\RubriqueRepository $rubriqueRepository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(
            Request $request, 
            ContainerInterface $container, 
            Actualite $actualite, 
            RubriqueRepository $rubriqueRepository, 
            DepartementRepository $departementRepository,
            MentionRepository $mentionRepository, 
            NiveauRepository $niveauRepository, 
            SecteurRepository $secteurRepository,
            RubriqueManager $rubriqueManager
            ) : Response
    {
        $form = $this->createForm(ActualiteType::class, $actualite);
        $form->handleRequest($request);
        $rubriques = $rubriqueManager->getAllStructParentChild();
        $artcilerRessourceTypeList = Actualite::$ressourceTypeList;
        
        if ($form->isSubmitted() && $form->isValid()) {
            $slugify = new Slugify();
            $slug = $slugify->slugify($actualite->getTitle());
            $actualite->setSlug($slug);

            $file = $form->get('file')->getData();
            
            // Upload file
            if ($file) {
                $directory      = $container->getParameter('actualite_directory');
                $uploader       = new \App\Services\FileUploader($directory);
                
                $fileDirectory = $actualite->getUuid();
                $file_dispaly   = $uploader->upload($file,$directory,$fileDirectory);
                $actualite->setPath($file_dispaly["filename"]);
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_actualites_list');
        } else {
            dump($form);
        }

        return $this->render(
            'backend/actualites/edit.html.twig',
            [
                'actualite'  => $actualite,
                'form'       => $form->createView(),
                'rubriques'  => $rubriques,
                'departements'=> $departementRepository->findAll(),
                'mentions'    => $mentionRepository->findAll(),
                'niveaux'     => $niveauRepository->findAll(),
                'ressourceTypeList' => $artcilerRessourceTypeList,
            ]
        );
    }

    /**
     * @Route("/admin/actualites/{id}", name="admin_actualites_delete", methods={"POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Actualite                     $actualite
     * @param \App\Repository\DocumentRepository $documentRepository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(Request $request, Actualite $actualite,DocumentRepository $documentRepository) : Response
    {
        if ($this->isCsrfTokenValid('delete' . $actualite->getId(), $request->request->get('_token'))) {
            $document = $documentRepository->findOneBy(array('actualite'=>$actualite->getId()));
            if($document){
               $this->addFlash('error', 'Suppression non autorisée'); 
            }
            else{
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($actualite);
                $entityManager->flush();
                $this->addFlash('success', 'Succès suppression');
            }
        }

        return $this->redirectToRoute('admin_actualites_list');
    }
}
