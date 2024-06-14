<?php

namespace App\Controller\Backend;

use App\Entity\Niveau;
use App\Services\BreadcrumbsService;
use App\Form\NiveauType;
use App\Manager\NiveauManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class NiveauController
 *
 * @package App\Controller\Backend
 */
class NiveauController extends AbstractController
{
    /**
     * Add function
     * 
     * @Route("admin/formation/niveau/new", name="admin_rf_niveau_new", methods={ "GET", "POST" })
     * @param \App\Entity\Niveau                            $niveau
     * @param \App\Services\BreadcrumbsService              $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request     $request
     * @param \App\Manager\NiveauManager                    $manager
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addAction(Niveau $niveau = null, BreadcrumbsService $breadcrumbsService, Request $request, NiveauManager $manager)
    {
        if(!$niveau)
        {
            $niveau = new Niveau();
        }

        //Created form
        $form = $this->createForm(NiveauType::class, $niveau);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
                $niveau->setCreatedAt(new \DateTime());
                $manager->save($niveau);
                $this->addFlash('Infos', 'Ajouté avec succès');
            return $this->redirectToRoute('admin_rf_niveau_liste');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Formations')
            ->add('Niveau',$this->generateUrl('admin_rf_niveau_liste'))
            ->add('Ajout')
        ;

        return $this->render(
            'backend/niveau/add.html.twig',
            [
                'niveau'      => $niveau,
                'formNiveau'  => $form->createView(),
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Edit function
     * 
     * @Route("/admin/formation/niveau/{id}/edit", name="admin_rf_niveau_edit", methods={ "GET", "POST" })
     * @param \App\Entity\Niveau                            $niveau
     * @param \App\Services\BreadcrumbsService              $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Niveau $niveau, BreadcrumbsService $breadcrumbsService, Request $request) : Response
    {
        $form = $this->createForm(NiveauType::class, $niveau);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $niveau->setUpdatedAt(new \DateTime());
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('Infos', 'Modifié avec succès');
            return $this->redirectToRoute('admin_rf_niveau_liste');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Formations')
            ->add('Niveau',$this->generateUrl('admin_rf_niveau_liste'))
            ->add('Modification')
        ;

        return $this->render(
            'backend/niveau/edit.html.twig',
            [
                'niveau'      => $niveau,
                'formNiveau'  => $form->createView(),
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Delete function
     * 
     * @Route("/admin/formation/niveau/delete/{id}", name="admin_rf_niveau_delete", methods={"DELETE"})
     * @param \App\Entity\Niveau                            $niveau
     * @param \Symfony\Component\HttpFoundation\Request     $request
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(Niveau $niveau, Request $request) : Response
    {
        if ($this->isCsrfTokenValid('delete' . $niveau->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($niveau);
            $entityManager->flush();
            $this->addFlash('Infos', 'Supprimé avec succès');
        }
        return $this->redirectToRoute('admin_rf_niveau_liste');
    }

    /**
     * List function
     *
     * @Route("/admin/formation/niveau/liste", name="admin_rf_niveau_liste", methods={ "GET" })
     * @param \App\Services\BreadcrumbsService      $breadcrumbsService
     * @param \App\Manager\NiveauManager                    $manager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(BreadcrumbsService $breadcrumbsService, NiveauManager $manager)
    {
        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Formations')
            ->add('Niveau')
        ;

        return $this->render(
            'backend/niveau/list.html.twig',
            [
                'niveaux'   => $manager->loadAll(),
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }
}
