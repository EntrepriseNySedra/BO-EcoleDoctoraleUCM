<?php

namespace App\Controller\Backend;

use App\Entity\Ecolage;
use App\Services\BreadcrumbsService;
use App\Form\EcolageType;
use App\Manager\EcolageManager;
use App\Manager\ParcoursManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class NiveauController
 *
 * @package App\Controller\Backend
 */
class EcolageController extends AbstractController
{
    /**
     * index function
     *
     * @Route("/admin/ecolages", name="admin_ecolage_index", methods={ "GET" })
     * @param \App\Services\BreadcrumbsService      $breadcrumbsService
     * @param \App\Manager\EcolageManager                    $manager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(BreadcrumbsService $breadcrumbsService, EcolageManager $manager)
    {
        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Ecolage')
        ;

        return $this->render(
            'backend/ecolage/index.html.twig',
            [
                'list'   => $manager->loadAll(),
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * New Action
     * 
     * @Route("admin/ecolage/new", name="admin_ecolage_new", methods={ "GET", "POST" })
     * @param \App\Entity\Ecolage                           $ecolage
     * @param \App\Services\BreadcrumbsService              $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request     $request
     * @param \App\Manager\EcolageManager                   $manager
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function new(Ecolage $ecolage = null, BreadcrumbsService $breadcrumbsService, Request $request, EcolageManager $manager)
    {
        if(!$ecolage)
        {
            $ecolage = new Ecolage();
        }

        //Created form
        $form = $this->createForm(EcolageType::class, $ecolage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
                $manager->save($ecolage);
                $this->addFlash('Infos', 'Ajouté avec succès');
            return $this->redirectToRoute('admin_ecolage_index');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Ecolage',$this->generateUrl('admin_ecolage_index'))
            ->add('Ajout')
        ;

        return $this->render(
            'backend/ecolage/new.html.twig',
            [
                'ecolage'     => $ecolage,
                'form'        => $form->createView(),
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * New Action
     * 
     * @Route("admin/ecolage/{id}/edit", name="admin_ecolage_edit", methods={ "GET", "POST" })
     * @param \App\Entity\Ecolage                           $ecolage
     * @param \App\Services\BreadcrumbsService              $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request     $request
     * @param \App\Manager\EcolageManager                   $manager
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Ecolage $ecolage = null, BreadcrumbsService $breadcrumbsService, Request $request, EcolageManager $manager)
    {
        //Created form
        $form = $this->createForm(EcolageType::class, $ecolage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
                $manager->save($ecolage);
                $this->addFlash('Infos', 'Ajouté avec succès');
            return $this->redirectToRoute('admin_ecolage_index');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Ecolage',$this->generateUrl('admin_ecolage_index'))
            ->add('Edit')
        ;

        return $this->render(
            'backend/ecolage/new.html.twig',
            [
                'ecolage'     => $ecolage,
                'form'        => $form->createView(),
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Delete function
     * 
     * @Route("/admin/ecolage/{id}/delete", name="admin_ecolage_delete", methods={"DELETE"})
     * @param \App\Entity\Ecolage                           $ecolage
     * @param \Symfony\Component\HttpFoundation\Request     $request
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(Ecolage $ecolage, Request $request) : Response
    {
        if ($this->isCsrfTokenValid('delete' . $niveau->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($ecolage);
            $entityManager->flush();
            $this->addFlash('Infos', 'Supprimé avec succès');
        }
        return $this->redirectToRoute('admin_ecolage_index');
    }

    /**
     * ajax option Action
     * 
     * @Route("admin/ecolage/parcours", name="admin_ecolage_parcours", methods={ "GET" })
     * @param \App\Services\BreadcrumbsService              $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request     $request
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getParcours(Request $request, ParcoursManager $parcoursManager)
    {
        $_niveauId = $request->get('niveau_id', 0);
        $_mentionId = $request->get('mention_id', 0);
        //Created form
        $parcours              = $parcoursManager->loadBy(
            ['mention' => $_mentionId, 'niveau' => $_niveauId],
            ['nom' => 'ASC']
        );

        return $this->render(
            'backend/ecolage/_parcours_options.html.twig',
            [
                'parcours'     => $parcours
            ]
        );
    }
}
