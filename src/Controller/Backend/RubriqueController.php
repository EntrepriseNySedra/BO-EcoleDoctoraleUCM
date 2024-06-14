<?php

namespace App\Controller\Backend;

use App\Entity\Rubrique;
use App\Form\RubriqueType;
use App\Entity\Document;
use App\Manager\RubriqueManager;
use App\Repository\RubriqueRepository;
use App\Repository\DocumentRepository;
use App\Services\BreadcrumbsService;
use App\Services\UtilFunctionService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Cocur\Slugify\Slugify;

/**
 * Class RubriqueController
 *
 * @package App\Controller\Backend
 */
class RubriqueController extends BaseController
{
    /**
     * Listing rubriques
     *
     * @Route("/admin/rubriques/list", name="admin_rubriques_list", methods={ "GET" })
     * @param \App\Manager\RubriqueManager $rubriqueManager
     * @param \App\Services\BreadcrumbsService $breadcrumbsService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(BreadcrumbsService $breadcrumbsService, RubriqueManager $rubriqueManager)
    {
        $rubriques = $rubriqueManager->getAllWithParent();      
        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Rubriques', $this->generateUrl('admin_rubriques_list'))
            ->add('Liste')
        ;

        return $this->render(
            'backend/rubriques/list.html.twig',
            [
                'rubriques'   => $rubriques,
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    
    /**
     * Adding page
     * @Route("/admin/rubriques/add", name="admin_rubriques_add", methods={ "GET", "POST" })
     *
     * @param \App\Services\BreadcrumbsService          $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\RubriqueManager              $manager
     * @param \App\Repository\RubriqueRepository        $rubriqueRepository
     * @param \App\Services\UtilFunctionService         $utilFunctionService
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function new(
        BreadcrumbsService $breadcrumbsService,
        Request $request,
        RubriqueManager $manager,
        RubriqueRepository $rubriqueRepository,
        UtilFunctionService $utilFunctionService
    ) {
        $rubrique = new Rubrique();
        // Parentable list
        $rubriquesParentalble = $manager->getParentable();
        $form = $this->createForm(
            RubriqueType::class,
            $rubrique
        );
        $form->handleRequest($request);

        if (empty($rubrique->getParent() == null)) {
            $root = $rubriqueRepository->findOneBy(['code' => 'ROOT']);
            $rubrique->setParent($root->getId());
        }
        $code = $rubrique->getCode();
        $code = empty($code) ? $rubrique->getTitle() : $code;
        $code = $utilFunctionService->constantize($code);
        $rubrique->setCode($code);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($rubrique->getTitle() !== null) {
                $slugify = new Slugify();
                $slug = $slugify->slugify($rubrique->getTitle());
                $rubrique->setSlug($slug);
            }

            $manager->save($rubrique);
            return $this->redirectToRoute('admin_rubriques_list');
        }

        $breadcrumbsService
            ->add('Rubriques', $this->generateUrl('admin_rubriques_list'))
            ->add('Ajout');

        return $this->render(
            'backend/rubriques/add.html.twig',
            [
                'rubrique'    => $rubrique,
                'form'        => $form->createView(),
                'rubriques'   => $rubriquesParentalble,
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }


    /**
     * @Route("/admin/rubriques/{id}/edit", name="admin_rubriques_edit", methods={"GET","POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Rubrique                      $rubrique
     * @param \App\Repository\RubriqueRepository $rubriqueRepository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request, Rubrique $rubrique, RubriqueRepository $rubriqueRepository, UtilFunctionService $utilFunctionService, RubriqueManager $manager) : Response
    {
        $form = $this->createForm(RubriqueType::class, $rubrique);
        $form->handleRequest($request);

        //Parentable rubrique
        $rubriquesParentalble = $manager->getParentable();

        $code = $rubrique->getCode();
        $code = empty($code) ? $rubrique->getTitle() : $code;
        $code = $utilFunctionService->constantize($code);
        $rubrique->setCode($code);
        
        $slugify = new Slugify();
        $slug = $slugify->slugify($rubrique->getTitle());
        $rubrique->setSlug($slug);
            
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_rubriques_list');
        }

        return $this->render(
            'backend/rubriques/edit.html.twig',
            [
                'rubrique' => $rubrique,
                'rubriques'=> $rubriquesParentalble,
                'form'     => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/admin/rubriques/{id}", name="admin_rubriques_delete", methods={"POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Rubrique                      $rubrique
     * @param \App\Repository\DocumentRepository $documentRepository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(Request $request, Rubrique $rubrique, DocumentRepository $documentRepository) : Response
    {
        if ($this->isCsrfTokenValid('delete' . $rubrique->getId(), $request->request->get('_token'))) {
            $document = $documentRepository->findOneBy(array('actualite'=>$rubrique->getId()));
            if($document){
               $this->addFlash('error', 'Suppression non autorisée'); 
            }
            else{
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($rubrique);
                $entityManager->flush();
                $this->addFlash('success', 'Succès suppression');
            }
        }

        return $this->redirectToRoute('admin_rubriques_list');
    }

    /**
     * Formations
     *
     * @Route("/admin/rubriques/formations", name="admin_rubriques_formations", methods={ "GET" })
     * @param \App\Repository\RubriqueRepository $rubriqueRepository
     * @param \App\Services\BreadcrumbsService $breadcrumbsService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listActionFormation(BreadcrumbsService $breadcrumbsService, RubriqueRepository $rubriqueRepository)
    {

        $rubriques = [];

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Rubriques', $this->generateUrl('admin_rubriques_formations'))
            ->add('Formations')
        ;

        return $this->render(
            'backend/rubriques/list.html.twig',
            [
                'rubriques'   => $rubriqueRepository->findAll(),
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

}