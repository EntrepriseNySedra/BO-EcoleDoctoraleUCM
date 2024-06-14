<?php

namespace App\Controller\Backend;

use App\Entity\CalendrierPaiement;
use App\Services\BreadcrumbsService;
use App\Form\CalendrierPaiementType;
use App\Manager\CalendrierPaiementManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CalendrierPaiementController
 *
 * @package App\Controller\Backend
 */
class CalendrierPaiementController extends AbstractController
{
     /**
     * List function
     *
     * @Route("/admin/calendrier-paiement/index", name="admin_calendrier_paiement_index", methods={ "GET" })
     * @param \App\Services\BreadcrumbsService          $breadcrumbsService
     * @param \App\Manager\CalendrierPaiementManager    $manager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(BreadcrumbsService $breadcrumbsService, CalendrierPaiementManager $manager)
    {
        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Calendrier de paiement')
        ;

        return $this->render(
            'backend/calendrier-paiement/index.html.twig',
            [
                'list'   => $manager->loadAll(),
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Add function
     * 
     * @Route("admin/calendrier-paiement/new", name="admin_calendrier_paiement_new", methods={ "GET", "POST" })
     * @param \App\Entity\CalendrierPaiement                $calendrierPaiement
     * @param \App\Services\BreadcrumbsService              $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request     $request
     * @param \App\Manager\CalendrierPaiementManager        $manager
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function new(CalendrierPaiement $calendrierPaiement = null, BreadcrumbsService $breadcrumbsService, Request $request, CalendrierPaiementManager $manager)
    {
        $calendrierPaiement = new CalendrierPaiement();
        //Created form
        $form = $this->createForm(CalendrierPaiementType::class, $calendrierPaiement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
                $calendrierPaiement->setCreatedAt(new \DateTime());
                $manager->save($calendrierPaiement);
                $this->addFlash('Infos', 'Ajouté avec succès');
            return $this->redirectToRoute('admin_calendrier_paiement_index');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Calendrier de paiement',$this->generateUrl('admin_calendrier_paiement_index'))
            ->add('Ajout')
        ;

        return $this->render(
            'backend/calendrier-paiement/new.html.twig',
            [
                'calendrierPaiement'      => $calendrierPaiement,
                'form'                  => $form->createView(),
                'breadcrumbs'           => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Edit function
     * 
     * @Route("/admin/calendrier-paiement/{id}/edit", name="admin_calendrier_paiement_edit", methods={ "GET", "POST" })
     * @param \App\Entity\CalendrierPaiement                $calendrierPaiment
     * @param \App\Services\BreadcrumbsService              $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(CalendrierPaiement $calendrierPaiement, BreadcrumbsService $breadcrumbsService, Request $request) : Response
    {
        $form = $this->createForm(CalendrierPaiementType::class, $calendrierPaiement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $calendrierPaiement->setUpdatedAt(new \DateTime());
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('Infos', 'Modifié avec succès');
            return $this->redirectToRoute('admin_calendrier_paiement_index');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Calendrier de paiement',$this->generateUrl('admin_calendrier_paiement_index'))
            ->add('Modification')
        ;

        return $this->render(
            'backend/calendrier-paiement/edit.html.twig',
            [
                'calendrierPaiement'     => $calendrierPaiement,
                'form'            => $form->createView(),
                'breadcrumbs'           => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Delete function
     * 
     * @Route("/admin/calendrier-paiement/delete/{id}", name="admin_calendrier_paiement_delete", methods={"DELETE"})
     * @param \App\Entity\CalendrierPaiement                $calendrierPaiement
     * @param \Symfony\Component\HttpFoundation\Request     $request
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(CalendrierPaiement $calendrierPaiement, Request $request) : Response
    {
        if ($this->isCsrfTokenValid('delete' . $calendrierPaiement->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($calendrierPaiement);
            $entityManager->flush();
            $this->addFlash('Infos', 'Supprimé avec succès');
        }
        return $this->redirectToRoute('admin_calendrier_paiement_index');
    }

}
