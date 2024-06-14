<?php

namespace App\Controller\Backend;

use App\Entity\Parcours;
use App\Services\BreadcrumbsService;
use App\Form\ParcoursType;
use App\Manager\ParcoursManager;
use App\Manager\NiveauManager;
use App\Repository\MentionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ParcoursController
 *
 * @package App\Controller\Backend
 */
class ParcoursController extends AbstractController
{
    /**
     * Add function
     * 
     * @Route("admin/formation/parcours/new", name="admin_rf_parcours_new", methods={ "GET", "POST" })
     * @param \App\Entity\Parcours                            $parcours
     * @param \App\Services\BreadcrumbsService              $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request     $request
     * @param \App\Manager\ParcoursManager                    $manager
     * @param \App\Manager\ParcoursManager                    $manager
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function new(Parcours $parcours = null, BreadcrumbsService $breadcrumbsService, Request $request, ParcoursManager $manager, MentionRepository $mentionRepo, NiveauManager $nivManager)
    {
        $mentions = $mentionRepo->findBy(['active' => 1]);
        $niveaux = $nivManager->loadAll();
        if(!$parcours)
        {
            $parcours = new Parcours();
        }

        //Created form
        $form = $this->createForm(ParcoursType::class, $parcours);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
                $manager->save($parcours);
                $this->addFlash('Infos', 'Ajouté avec succès');
            return $this->redirectToRoute('admin_rf_parcours_liste');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Formations')
            ->add('Parcours',$this->generateUrl('admin_rf_parcours_liste'))
            ->add('Ajout')
        ;

        return $this->render(
            'backend/parcours/add.html.twig',
            [
                'mentions'      => $mentions,
                'niveaux'       => $niveaux,
                'parcours'      => $parcours,
                'formParcours'  => $form->createView(),
                'breadcrumbs'   => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Edit function
     * 
     * @Route("/admin/formation/parcours/{id}/edit", name="admin_rf_parcours_edit", methods={ "GET", "POST" })
     * @param \App\Entity\Parcours                            $parcours
     * @param \App\Services\BreadcrumbsService              $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Parcours $parcours, BreadcrumbsService $breadcrumbsService, Request $request, MentionRepository $mentionRepo, NiveauManager $nivManager) : Response
    {
        $mentions = $mentionRepo->findBy(['active' => 1]);
        $niveaux = $nivManager->loadAll();
        $form = $this->createForm(ParcoursType::class, $parcours);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $parcours->setUpdatedAt(new \DateTime());
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('Infos', 'Modifié avec succès');
            return $this->redirectToRoute('admin_rf_parcours_liste');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Formations')
            ->add('parcours',$this->generateUrl('admin_rf_parcours_liste'))
            ->add('Modification')
        ;

        return $this->render(
            'backend/parcours/edit.html.twig',
            [
                'niveaux'       => $niveaux,
                'mentions'      => $mentions,
                'parcours'      => $parcours,
                'formParcours'  => $form->createView(),
                'breadcrumbs'   => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Delete function
     * 
     * @Route("/admin/formation/parcours/delete/{id}", name="admin_rf_parcours_delete", methods={"DELETE"})
     * @param \App\Entity\Parcours                            $parcours
     * @param \Symfony\Component\HttpFoundation\Request     $request
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteDepartement(Parcours $parcours, Request $request) : Response
    {
        if ($this->isCsrfTokenValid('delete' . $parcours->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($parcours);
            $entityManager->flush();
            $this->addFlash('Infos', 'Supprimé avec succès');
        }
        return $this->redirectToRoute('admin_rf_parcours_liste');
    }

    /**
     * List function
     *
     * @Route("/admin/formation/parcours/liste", name="admin_rf_parcours_liste", methods={ "GET" })
     * @param \App\Services\BreadcrumbsService      $breadcrumbsService
     * @param \App\Manager\ParcoursManager                    $manager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(BreadcrumbsService $breadcrumbsService, ParcoursManager $manager)
    {
        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Formations')
            ->add('Parcours')
        ;

        return $this->render(
            'backend/parcours/list.html.twig',
            [
                'parcours'      => $manager->loadAll(),
                'breadcrumbs'   => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }
}
