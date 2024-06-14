<?php

namespace App\Controller\Backend;

use App\Entity\Profil;
use App\Form\ProfilType;
use App\Manager\ProfilManager;
use App\Repository\ProfilRepository;
use App\Services\BreadcrumbsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @package App\Controller\Backend
 * @author  Joelio
 * @Route("/admin/profil")
 */
class ProfilController extends AbstractController
{
    /**
     * @Route("/", name="admin_profil_list", methods={"GET"})
     * @param \App\Repository\ProfilRepository $profilRepository
     * @param \App\Services\BreadcrumbsService $breadcrumbsService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function list(ProfilRepository $profilRepository, BreadcrumbsService $breadcrumbsService) : Response
    {

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Profil', $this->generateUrl('admin_profil_list'))
            ->add('Liste')
        ;

        return $this->render(
            'backend/profil/list.html.twig',
            [
                'profils'     => $profilRepository->findAll(),
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * @Route("/add", name="admin_profil_add", methods={"GET","POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Services\BreadcrumbsService          $breadcrumbsService
     * @param \App\Manager\ProfilManager                $manager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function add(Request $request, BreadcrumbsService $breadcrumbsService, ProfilManager $manager) : Response
    {
        $profil = $manager->createObject();
        $form   = $this->createForm(
            ProfilType::class,
            $profil
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->save($profil);

            return $this->redirectToRoute('admin_profil_list');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Profil', $this->generateUrl('admin_profil_list'))
            ->add('Ajout')
        ;

        return $this->render(
            'backend/profil/add.html.twig',
            [
                'profil'      => $profil,
                'form'        => $form->createView(),
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * @Route("/{id}/edit", name="admin_profil_edit", methods={"GET","POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Profil                        $profil
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request, Profil $profil) : Response
    {
        $form = $this->createForm(ProfilType::class, $profil);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_profil_list');
        }

        return $this->render(
            'backend/profil/edit.html.twig',
            [
                'profil' => $profil,
                'form'   => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="profil_delete", methods={"DELETE"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Profil                        $profil
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(Request $request, Profil $profil) : Response
    {
        if ($this->isCsrfTokenValid('delete' . $profil->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($profil);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_profil_list');
    }
}
