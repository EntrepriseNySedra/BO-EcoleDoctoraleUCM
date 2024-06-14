<?php

namespace App\Controller\Backend;

use App\Entity\Examens;
use App\Services\BreadcrumbsService;
use App\Form\ExamenType;
use App\Manager\ExamensManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ExamenController
 *
 * @package App\Controller\Backend
 */
class ExamenController extends AbstractController
{

    /**
     * List function
     *
     * @Route("/admin/examen/index", name="admin_examen_index", methods={ "GET" })
     * @param \App\Services\BreadcrumbsService      $breadcrumbsService
     * @param \App\Manager\ExamensManager          $manager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(BreadcrumbsService $breadcrumbsService, ExamensManager $manager)
    {
        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Examen')
        ;

        return $this->render(
            'backend/examen/index.html.twig',
            [
                'examens'       => $manager->loadAll(),
                'breadcrumbs'   => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Add function
     * 
     * @Route("admin/examen/new", name="admin_examen_new", methods={ "GET", "POST" })
     * @param \App\Services\BreadcrumbsService              $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request     $request
     * @param \App\Manager\ExamensManager                    $manager
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function new(BreadcrumbsService $breadcrumbsService, Request $request, ExamensManager $manager)
    {
        $examen = $manager->createObject();

        //Created form
        $form = $this->createForm(ExamenType::class, $examen);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
                $examen->setCreatedAt(new \DateTime());
                $manager->save($examen);
                $this->addFlash('Infos', 'Ajouté avec succès');
            return $this->redirectToRoute('admin_examen_index');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Examen', $this->generateUrl('admin_examen_index'))
            ->add('Ajout')
        ;

        return $this->render(
            'backend/examen/add.html.twig',
            [
                'examen'        => $examen,
                'form'          => $form->createView(),
                'breadcrumbs'   => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Edit function
     * 
     * @Route("/admin/examen/{id}/edit", name="admin_examen_edit", methods={ "GET", "POST" })
     * @param \App\Entity\Examens                          $examen
     * @param \App\Services\BreadcrumbsService              $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Examens $examen, BreadcrumbsService $breadcrumbsService, Request $request, ExamensManager $manager) : Response
    {
        $form = $this->createForm(ExamenType::class, $examen);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->save($examen);

            $this->addFlash('Infos', 'Modifié avec succès');
            return $this->redirectToRoute('admin_examen_index');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Examen', $this->generateUrl('admin_examen_index'))
            ->add('Modification')
        ;

        return $this->render(
            'backend/examen/edit.html.twig',
            [
                'examen'        => $examen,
                'form'          => $form->createView(),
                'breadcrumbs'   => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Delete function
     * 
     * @Route("/admin/examen/delete/{id}", name="admin_examen_delete", methods={"DELETE"})
     * @param \App\Entity\Examens                           $examen
     * @param \Symfony\Component\HttpFoundation\Request     $request
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteDepartement(Examens $examen, Request $request) : Response
    {
        if ($this->isCsrfTokenValid('delete' . $examen->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($examen);
            $entityManager->flush();
            $this->addFlash('Infos', 'Supprimé avec succès');
        }
        return $this->redirectToRoute('admin_examen_index');
    }
}
