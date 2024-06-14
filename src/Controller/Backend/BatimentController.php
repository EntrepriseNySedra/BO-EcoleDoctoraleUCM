<?php

namespace App\Controller\Backend;

use App\Entity\Batiment;
use App\Services\BreadcrumbsService;
use App\Form\BatimentType;
use App\Manager\BatimentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class BatimentController
 *
 * @package App\Controller\Backend
 */
class BatimentController extends AbstractController
{

    /**
     * List function
     *
     * @Route("/admin/batiment/liste", name="admin_rf_batiment_liste", methods={ "GET" })
     * @param \App\Services\BreadcrumbsService      $breadcrumbsService
     * @param \App\Manager\BatimentManager          $manager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(BreadcrumbsService $breadcrumbsService, BatimentManager $manager)
    {
        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Batiment')
        ;

        return $this->render(
            'backend/batiment/index.html.twig',
            [
                'batiments'       => $manager->loadAll(),
                'breadcrumbs'   => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Add function
     * 
     * @Route("admin/batiment/new", name="admin_rf_batiment_new", methods={ "GET", "POST" })
     * @param \App\Entity\Batiment                          $batiment
     * @param \App\Services\BreadcrumbsService              $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request     $request
     * @param \App\Manager\BatimentManager                    $manager
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function new(Batiment $batiment = null, BreadcrumbsService $breadcrumbsService, Request $request, BatimentManager $manager)
    {
        if(!$batiment)
        {
            $batiment = new Batiment();
        }

        //Created form
        $form = $this->createForm(BatimentType::class, $batiment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
                $batiment->setCreatedAt(new \DateTime());
                $manager->save($batiment);
                $this->addFlash('Infos', 'Ajouté avec succès');
            return $this->redirectToRoute('admin_rf_batiment_liste');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Bâtiment', $this->generateUrl('admin_rf_batiment_liste'))
            ->add('Ajout')
        ;

        return $this->render(
            'backend/batiment/add.html.twig',
            [
                'batiment'      => $batiment,
                'form'          => $form->createView(),
                'breadcrumbs'   => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Edit function
     * 
     * @Route("/admin/batiment/{id}/edit", name="admin_rf_batiment_edit", methods={ "GET", "POST" })
     * @param \App\Entity\Batiment                          $batiment
     * @param \App\Services\BreadcrumbsService              $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Batiment $batiment, BreadcrumbsService $breadcrumbsService, Request $request) : Response
    {
        $form = $this->createForm(BatimentType::class, $batiment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $batiment->setUpdatedAt(new \DateTime());
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('Infos', 'Modifié avec succès');
            return $this->redirectToRoute('admin_rf_batiment_liste');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Bâtiment', $this->generateUrl('admin_rf_batiment_liste'))
            ->add('Modification')
        ;

        return $this->render(
            'backend/batiment/edit.html.twig',
            [
                'batiment'      => $batiment,
                'form'  => $form->createView(),
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Delete function
     * 
     * @Route("/admin/batiment/delete/{id}", name="admin_rf_batiment_delete", methods={"DELETE"})
     * @param \App\Entity\Batiment                            $batiment
     * @param \Symfony\Component\HttpFoundation\Request     $request
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteDepartement(Batiment $batiment, Request $request) : Response
    {
        if ($this->isCsrfTokenValid('delete' . $batiment->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($batiment);
            $entityManager->flush();
            $this->addFlash('Infos', 'Supprimé avec succès');
        }
        return $this->redirectToRoute('admin_rf_batiment_liste');
    }
}
