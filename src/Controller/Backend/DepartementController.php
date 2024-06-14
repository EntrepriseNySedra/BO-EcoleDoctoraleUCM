<?php

namespace App\Controller\Backend;

use App\Entity\Departement;
use App\Services\BreadcrumbsService;
use App\Form\DepartementType;
use App\Manager\DepartementManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DepartementController
 *
 * @package App\Controller\Backend
 */
class DepartementController extends AbstractController
{
    /**
     * Add function
     * 
     * @Route("/admin/formation/departement/new", name="admin_rf_departement_new", methods={ "GET", "POST" })
     * @param \App\Entity\Departement                   $departement
     * @param \App\Services\BreadcrumbsService          $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\DepartementManager           $manager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addAction(Departement $departement = null, BreadcrumbsService $breadcrumbsService, Request $request, DepartementManager $manager)
    {
        if(!$departement)
        {
            $departement = new Departement();
        }

        //Created form
        $form = $this->createForm(DepartementType::class, $departement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $departement->setCreatedAt(new \DateTime());
            $manager->save($departement);
            $this->addFlash('Infos', 'Ajouté avec succès');
            return $this->redirectToRoute('admin_rf_depatement_liste');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Formations')
            ->add('Département',$this->generateUrl('admin_rf_depatement_liste'))
            ->add('Ajout')
        ;

        return $this->render(
            'backend/departement/add.html.twig',
            [
                'departement'      => $departement,
                'formDepartement'  => $form->createView(),
                'breadcrumbs'      => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Edit function
     * 
     * @Route("/admin/formation/departement/{id}/edit", name="admin_rf_departement_edit", methods={ "GET", "POST" })
     * @param \App\Entity\Departement                   $departement
     * @param \App\Services\BreadcrumbsService          $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Departement $departement, BreadcrumbsService $breadcrumbsService, Request $request) : Response
    {
        $form = $this->createForm(DepartementType::class, $departement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $departement->setUpdatedAt(new \DateTime());
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('Infos', 'Modifié avec succès');
            return $this->redirectToRoute('admin_rf_depatement_liste');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Formations')
            ->add('Département',$this->generateUrl('admin_rf_depatement_liste'))
            ->add('Modification')
        ;

        return $this->render(
            'backend/departement/edit.html.twig',
            [
                'departement'      => $departement,
                'formDepartement'  => $form->createView(),
                'breadcrumbs'      => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Delete function
     * 
     * @Route("/admin/formation/departement/delete/{id}", name="admin_rf_depatement_delete", methods={"DELETE"})
     * @param \App\Entity\Departement                           $departement
     * @param \Symfony\Component\HttpFoundation\Request         $request
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Departement $departement, Request $request) : Response
    {
        if ($this->isCsrfTokenValid('delete' . $departement->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($departement);
            $entityManager->flush();
            $this->addFlash('Infos', 'Supprimé avec succès');
        }
        return $this->redirectToRoute('admin_rf_depatement_liste');
    }

    /**
     * List function
     *
     * @Route("/admin/formation/departement/liste", name="admin_rf_depatement_liste", methods={ "GET" })
     * @param \App\Services\BreadcrumbsService $breadcrumbsService
     * @param \App\Manager\DepartementManager     $manager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(BreadcrumbsService $breadcrumbsService, DepartementManager $manager)
    {
        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Formations')
            ->add('Département')
        ;

        return $this->render(
            'backend/departement/list.html.twig',
            [
                'departements'      => $manager->loadAll(),
                'breadcrumbs'       => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }
}