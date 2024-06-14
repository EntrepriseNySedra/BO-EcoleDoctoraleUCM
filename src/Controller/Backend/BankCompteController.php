<?php

namespace App\Controller\Backend;

use App\Entity\BankCompte;
use App\Form\BankCompteType;
use App\Services\BreadcrumbsService;
use App\Manager\MentionManager;
use App\Manager\BankCompteManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class BankCompteController
 *
 * @package App\Controller\Backend
 */
class BankCompteController extends AbstractController
{
     /**
     * List function
     *
     * @Route("/admin/bank-compte/index", name="admin_bank_compte_index", methods={ "GET" })
     * @param \App\Services\BreadcrumbsService          $breadcrumbsService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(BreadcrumbsService $breadcrumbsService, BankCompteManager $bkManager)
    {
        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Compte bancaire')
        ;

        return $this->render(
            'backend/bank-compte/index.html.twig',
            [
                'list'   => $bkManager->loadAll(),
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Add function
     * 
     * @Route("admin/bank-compte/new",  name="admin_bank_compte_new", methods={ "GET", "POST" })
     * @param \App\Entity\BankCompte                        $bankCompte
     * @param \App\Services\BreadcrumbsService              $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request     $request
     * @param \App\Manager\TypePrestationManager            $manager
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function new(BankCompte $bankCompte = null, BreadcrumbsService $breadcrumbsService, Request $request, BankCompteManager $bkManager, MentionManager $mentionManager)
    {
        $bankCompte = new BankCompte();
        $mentions       = $mentionManager->loadBy([], ['nom' => 'ASC']);
        //Created form
        $form = $this->createForm(BankCompteType::class, $bankCompte);
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($bankCompte);
            $em->flush();

            $this->addFlash('Infos', 'Ajouté avec succès');
            return $this->redirectToRoute('admin_bank_compte_index');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Compte bancaire',$this->generateUrl('admin_bank_compte_index'))
            ->add('Ajout')
        ;

        return $this->render(
            'backend/bank-compte/new.html.twig',
            [
                'bankCompte'            => $bankCompte,
                'mentions'              => $mentions,
                'form'                  => $form->createView(),
                'breadcrumbs'           => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Edit function
     * 
     * @Route("/admin/bank-compte/{id}/edit", name="admin_bank_compte_edit", methods={ "GET", "POST" })
     * @param \App\Entity\BankCompte                        $bankCompte
     * @param \App\Services\BreadcrumbsService              $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(BankCompte $bankCompte, BreadcrumbsService $breadcrumbsService, MentionManager $mentionManager, Request $request) : Response
    {
        $mentions       = $mentionManager->loadBy([], ['nom' => 'ASC']);
        $form = $this->createForm(BankCompteType::class, $bankCompte);
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($bankCompte);
            $em->flush();

            $this->addFlash('Infos', 'Modifié avec succès');
            return $this->redirectToRoute('admin_bank_compte_index');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Compte bancaire',$this->generateUrl('admin_bank_compte_index'))
            ->add('Modification')
        ;
        
        return $this->render(
            'backend/bank-compte/edit.html.twig',
            [
                'mentions'              => $mentions,
                'bankCompte'            => $bankCompte,
                'form'                  => $form->createView(),
                'breadcrumbs'           => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Delete function
     * 
     * @Route("/admin/bank-compte/delete/{id}", name="admin_bank_compte_delete", methods={"DELETE"})
     * @param \App\Entity\BankCompte                        $bankCompte
     * @param \Symfony\Component\HttpFoundation\Request     $request
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(BankCompte $bankCompte, Request $request) : Response
    {
        if ($this->isCsrfTokenValid('delete' . $bankCompte->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($bankCompte);
            $entityManager->flush();
            $this->addFlash('Infos', 'Supprimé avec succès');
        }
        return $this->redirectToRoute('admin_bank_compte_index');
    }

}
