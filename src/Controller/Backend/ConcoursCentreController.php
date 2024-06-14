<?php

namespace App\Controller\Backend;

use App\Entity\ConcoursCentre;
use App\Services\BreadcrumbsService;
use App\Form\ConcoursCentreType;
use App\Manager\ConcoursCentreManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ConcoursCentreController
 *
 * @package App\Controller\Backend
 */
class ConcoursCentreController extends AbstractController
{

    /**
     * List function
     *
     * @Route("/admin/concours-centre", name="admin_rf_concours_centre_index", methods={ "GET" })
     * @param \App\Services\BreadcrumbsService      $breadcrumbsService
     * @param \App\Manager\ConcoursCentreManager          $manager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(BreadcrumbsService $breadcrumbsService, ConcoursCentreManager $manager)
    {
        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Concours centre')
        ;

        return $this->render(
            'backend/concours-centre/index.html.twig',
            [
                'list'       => $manager->loadAll(),
                'breadcrumbs'   => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Add function
     * 
     * @Route("admin/concours-centre/new", name="admin_rf_concours_centre_new", methods={ "GET", "POST" })
     * @param \App\Entity\ConcoursCentre                    $concCentre
     * @param \App\Services\BreadcrumbsService              $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request     $request
     * @param \App\Manager\ConcoursCentreManager            $manager
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function new(ConcoursCentre $concCentre = null, BreadcrumbsService $breadcrumbsService, Request $request, ConcoursCentreManager $manager)
    {
        if(!$concCentre)
        {
            $concCentre = new ConcoursCentre();
        }

        //Created form
        $form = $this->createForm(ConcoursCentreType::class, $concCentre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
                $manager->persist($concCentre);
                $manager->flush();
                $this->addFlash('Infos', 'Ajouté avec succès');
            return $this->redirectToRoute('admin_rf_concours_centre_index');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Concours centre', $this->generateUrl('admin_rf_concours_centre_index'))
            ->add('Ajout')
        ;

        return $this->render(
            'backend/concours-centre/new.html.twig',
            [
                'concCentre'    => $concCentre,
                'form'          => $form->createView(),
                'breadcrumbs'   => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Edit function
     * 
     * @Route("/admin/concours-centre/{id}/edit", name="admin_rf_concours_centre_edit", methods={ "GET", "POST" })
     * @param \App\Entity\ConcoursCentre                          $concCentre
     * @param \App\Services\BreadcrumbsService              $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(ConcoursCentre $concCentre, BreadcrumbsService $breadcrumbsService, Request $request) : Response
    {
        $form = $this->createForm(ConcoursCentreType::class, $concCentre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $concCentre->setUpdatedAt(new \DateTime());
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('Infos', 'Modifié avec succès');
            return $this->redirectToRoute('admin_rf_concours_centre_index');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Concours centre', $this->generateUrl('admin_rf_concours_centre_index'))
            ->add('Modification')
        ;

        return $this->render(
            'backend/concours-centre/edit.html.twig',
            [
                'concCentre'        => $concCentre,
                'form'              => $form->createView(),
                'breadcrumbs'       => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Delete function
     * 
     * @Route("/admin/concours-centre/delete/{id}", name="admin_rf_concours_centre_delete", methods={"DELETE"})
     * @param \App\Entity\ConcoursCentre                    $concCentre
     * @param \Symfony\Component\HttpFoundation\Request     $request
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteDepartement(ConcoursCentre $concCentre, Request $request) : Response
    {
        if ($this->isCsrfTokenValid('delete' . $concCentre->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($concCentre);
            $entityManager->flush();
            $this->addFlash('Infos', 'Supprimé avec succès');
        }
        return $this->redirectToRoute('admin_rf_concours_centre_index');
    }
}
