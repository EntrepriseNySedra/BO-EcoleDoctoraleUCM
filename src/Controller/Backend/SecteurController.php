<?php

namespace App\Controller\Backend;

use App\Entity\Secteur;
use App\Services\BreadcrumbsService;
use App\Form\SecteurType;
use App\Repository\SecteurRepository;
use App\Repository\MentionRepository;
use App\Manager\SecteurManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SecteurController
 *
 * @package App\Controller\Backend
 */
class SecteurController extends AbstractController
{
    /**
     * Add function
     * 
     * @Route("admin/formation/secteur/new", name="admin_rf_secteur_new", methods={ "GET", "POST" })
     * @param \App\Entity\Secteur                           $secteur
     * @param \App\Services\BreadcrumbsService              $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request     $request
     * @param \App\Manager\SecteurManager                   $manager
     * @param \App\Repository\MentionRepository             $mentionRepository 
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addAction(Secteur $secteur = null, BreadcrumbsService $breadcrumbsService, Request $request, SecteurManager $manager, MentionRepository $mentionRepository)
    {
        if(!$secteur)
        {
            $secteur = new Secteur();
        }

        //Created form
        $form = $this->createForm(SecteurType::class, $secteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $secteur->setCreatedAt(new \DateTime());
            $manager->save($secteur);
            $this->addFlash('Infos', 'Ajouté avec succès');
            return $this->redirectToRoute('admin_rf_secteur_liste');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Formations')
            ->add('Secteur',$this->generateUrl('admin_rf_secteur_liste'))
            ->add('Ajout')
        ;

        return $this->render(
            'backend/secteur/add.html.twig',
            [
                'secteur'      => $secteur,
                'mention'      => $mentionRepository->findAll(),
                'formSecteur'  => $form->createView(),
                'breadcrumbs'  => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Edit function
     * 
     * @Route("/admin/formation/secteur/{id}/edit", name="admin_rf_secteur_edit", methods={ "GET", "POST" })
     * @param \App\Entity\Secteur                           $secteur
     * @param \App\Services\BreadcrumbsService              $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request     $request
     * @param \App\Repository\MentionRepository             $mentionRepository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Secteur $secteur, BreadcrumbsService $breadcrumbsService, Request $request, MentionRepository $mentionRepository) : Response
    {
        $form = $this->createForm(SecteurType::class, $secteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $secteur->setUpdatedAt(new \DateTime());
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('Infos', 'Modifié avec succès');
            return $this->redirectToRoute('admin_rf_secteur_liste');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Formations')
            ->add('Secteur',$this->generateUrl('admin_rf_secteur_liste'))
            ->add('Modification')
        ;

        return $this->render(
            'backend/secteur/edit.html.twig',
            [
                'secteur'      => $secteur,
                'mention'      => $mentionRepository->findAll(),
                'formSecteur'  => $form->createView(),
                'breadcrumbs'  => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Delete function
     * 
     * @Route("/admin/formation/secteur/delete/{id}", name="admin_rf_secteur_delete", methods={"DELETE"})
     * @param \App\Entity\Secteur                           $secteur
     * @param \Symfony\Component\HttpFoundation\Request     $request
     * @return void
     */
    public function deleteAction(Secteur $secteur, Request $request) : Response
    {
        if ($this->isCsrfTokenValid('delete' . $secteur->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($secteur);
            $entityManager->flush();
            $this->addFlash('Infos', 'Supprimé avec succès');
        }
        return $this->redirectToRoute('admin_rf_secteur_liste');
    }

    /**
     * List function
     *
     * @Route("/admin/formation/secteur/liste", name="admin_rf_secteur_liste", methods={ "GET" })
     * @param \App\Services\BreadcrumbsService $breadcrumbsService
     * @param \App\Repository\SecteurRepository $repo
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listeAction(BreadcrumbsService $breadcrumbsService, SecteurRepository $repo)
    {
        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Formations')
            ->add('Secteur')
        ;

        return $this->render(
            'backend/secteur/list.html.twig',
            [
                'secteurs'   => $repo->findAll(),
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }
}
