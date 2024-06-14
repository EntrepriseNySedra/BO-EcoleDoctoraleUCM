<?php

namespace App\Controller\Backend;

use App\Entity\Salles;
use App\Services\BreadcrumbsService;
use App\Form\SallesType;
use App\Manager\SallesManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SallesController
 *
 * @package App\Controller\Backend
 */
class SallesController extends AbstractController
{

    /**
     * List function
     *
     * @Route("/admin/salle/liste", name="admin_rf_salle_liste", methods={ "GET" })
     * @param \App\Services\BreadcrumbsService      $breadcrumbsService
     * @param \App\Manager\SallesManager          $manager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(BreadcrumbsService $breadcrumbsService, SallesManager $manager)
    {
        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Salles')
        ;

        return $this->render(
            'backend/salle/index.html.twig',
            [
                'salles'       => $manager->loadAll(),
                'breadcrumbs'   => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Add function
     * 
     * @Route("admin/salle/new", name="admin_rf_salle_new", methods={ "GET", "POST" })
     * @param \App\Entity\Salles                          $salle
     * @param \App\Services\BreadcrumbsService              $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request     $request
     * @param \App\Manager\SallesManager                    $manager
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function new(Salles $salle = null, BreadcrumbsService $breadcrumbsService, Request $request, SallesManager $manager)
    {
        if(!$salle)
        {
            $salle = new Salles();
        }

        //Created form
        $form = $this->createForm(SallesType::class, $salle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
                $salle->setCreatedAt(new \DateTime());
                $manager->save($salle);
                $this->addFlash('Infos', 'Ajouté avec succès');
            return $this->redirectToRoute('admin_rf_salle_liste');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Bâtiment', $this->generateUrl('admin_rf_salle_liste'))
            ->add('Ajout')
        ;

        return $this->render(
            'backend/salle/add.html.twig',
            [
                'salle'      => $salle,
                'form'          => $form->createView(),
                'breadcrumbs'   => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Edit function
     * 
     * @Route("/admin/salle/{id}/edit", name="admin_rf_salle_edit", methods={ "GET", "POST" })
     * @param \App\Entity\Salles                          $salle
     * @param \App\Services\BreadcrumbsService              $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Salles $salle, BreadcrumbsService $breadcrumbsService, Request $request) : Response
    {
        $form = $this->createForm(SallesType::class, $salle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $salle->setUpdatedAt(new \DateTime());
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('Infos', 'Modifié avec succès');
            return $this->redirectToRoute('admin_rf_salle_liste');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Bâtiment', $this->generateUrl('admin_rf_salle_liste'))
            ->add('Modification')
        ;

        return $this->render(
            'backend/salle/edit.html.twig',
            [
                'salle'      => $salle,
                'form'  => $form->createView(),
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Delete function
     * 
     * @Route("/admin/salle/delete/{id}", name="admin_rf_salle_delete", methods={"DELETE"})
     * @param \App\Entity\Salles                            $salle
     * @param \Symfony\Component\HttpFoundation\Request     $request
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteDepartement(Salles $salle, Request $request) : Response
    {
        if ($this->isCsrfTokenValid('delete' . $salle->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($salle);
            $entityManager->flush();
            $this->addFlash('Infos', 'Supprimé avec succès');
        }
        return $this->redirectToRoute('admin_rf_salle_liste');
    }
}
