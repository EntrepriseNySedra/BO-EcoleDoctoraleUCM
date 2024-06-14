<?php

namespace App\Controller\Backend;

use App\Entity\Semestre;
use App\Services\BreadcrumbsService;
use App\Form\SemestreType;
use App\Manager\SemestreManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SemestreController
 *
 * @package App\Controller\Backend
 */
class SemestreController extends AbstractController
{
    /**
     * List function
     *
     * @Route("/admin/formation/semestre/liste", name="admin_rf_semestre_liste", methods={ "GET" })
     * @param \App\Services\BreadcrumbsService      $breadcrumbsService
     * @param \App\Manager\SemestreManager              $manager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(BreadcrumbsService $breadcrumbsService, SemestreManager $manager)
    {
        $semestres = $manager->loadBy(array(), array('libelle' => 'ASC'));
        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Formations')
            ->add('Semestre')
        ;

        return $this->render(
            'backend/semestre/list.html.twig',
            [
                'semestres'   => $semestres,
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Add function
     * 
     * @Route("/admin/formation/semestre/new", name="admin_rf_semestre_new", methods={ "GET", "POST" })
     * @param \App\Entity\Semestre                      $semestre
     * @param \App\Services\BreadcrumbsService          $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\SemestreManager              $manager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function new(
            Semestre $semestre = null, 
            BreadcrumbsService $breadcrumbsService, 
            Request $request, 
            SemestreManager $manager)
    {
        if(!$semestre)
        {
            $semestre = new Semestre();
        }

        //Created form
        $form = $this->createForm(SemestreType::class, $semestre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $semestre->setCreatedAt(new \DateTime());
            $manager->save($semestre);
            $this->addFlash('Infos', 'Ajouté avec succès');
            return $this->redirectToRoute('admin_rf_semestre_liste');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Formations')
            ->add('Semestre',$this->generateUrl('admin_rf_semestre_liste'))
            ->add('Ajout')
        ;

        return $this->render(
            'backend/semestre/add.html.twig',
            [
                'semestre'          => $semestre,
                'formSemestre'      => $form->createView(),
                'breadcrumbs'       => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Edit function
     * 
     * @Route("/admin/formation/semestre/{id}/edit", name="admin_rf_semestre_edit", methods={ "GET", "POST" })
     * @param \App\Entity\Semestre                      $semestre
     * @param \App\Services\BreadcrumbsService          $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(
            Semestre $semestre, 
            BreadcrumbsService $breadcrumbsService, 
            Request $request) : Response
    {
        $form = $this->createForm(SemestreType::class, $semestre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $semestre->setUpdatedAt(new \DateTime());
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('Infos', 'Modifié avec succès');
            return $this->redirectToRoute('admin_rf_semestre_liste');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Formations')
            ->add('Semestre',$this->generateUrl('admin_rf_semestre_liste'))
            ->add('Modification')
        ;

        return $this->render(
            'backend/semestre/edit.html.twig',
            [
                'semestre'      => $semestre,
                'formSemestre'  => $form->createView(),
                'breadcrumbs'   => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Delete function
     * 
     * @Route("/admin/formation/semestre/delete/{id}", name="admin_rf_semestre_delete", methods={"DELETE"})
     * @param \App\Entity\Semestre                              $semestre
     * @param \Symfony\Component\HttpFoundation\Request         $request
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(Semestre $semestre, Request $request) : Response
    {
        if ($this->isCsrfTokenValid('delete' . $semestre->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($semestre);
            $entityManager->flush();
            $this->addFlash('Infos', 'Supprimé avec succès');
        }
        return $this->redirectToRoute('admin_rf_semestre_liste');
    }
}
