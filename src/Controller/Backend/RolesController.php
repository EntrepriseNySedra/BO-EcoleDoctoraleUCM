<?php

namespace App\Controller\Backend;

use App\Entity\Roles;
use App\Form\RolesType;
use App\Manager\RoleManager;
use App\Services\BreadcrumbsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @package App\Controller\Backend
 * @author Joelio
 * @Route("/admin/roles")
 */
class RolesController extends BaseController
{
    /**
     * @Route("/", name="admin_roles_list", methods={"GET"})
     * @param \App\Manager\RoleManager         $manager
     * @param \App\Services\BreadcrumbsService $breadcrumbsService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function list(RoleManager $manager, BreadcrumbsService $breadcrumbsService) : Response
    {

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Action', $this->generateUrl('admin_roles_list'))
            ->add('Liste')
        ;

        return $this->render(
            'backend/roles/list.html.twig',
            [
                'roles'       => $manager->loadAll(),
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * @Route("/add", name="admin_roles_add", methods={"GET","POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Services\BreadcrumbsService          $breadcrumbsService
     * @param \App\Manager\RoleManager                  $manager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function add(Request $request, BreadcrumbsService $breadcrumbsService, RoleManager $manager) : Response
    {
        $role = $manager->createObject();
        $form = $this->createForm(RolesType::class, $role);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->save($role);

            return $this->redirectToRoute('admin_roles_list');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Action', $this->generateUrl('admin_roles_list'))
            ->add('Ajout')
        ;

        return $this->render(
            'backend/roles/add.html.twig',
            [
                'role'        => $role,
                'form'        => $form->createView(),
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * @Route("/{id}/edit", name="admin_roles_edit", methods={"GET","POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Roles                         $role
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request, Roles $role) : Response
    {
        $form = $this->createForm(RolesType::class, $role);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_roles_list');
        }

        return $this->render(
            'backend/roles/edit.html.twig',
            [
                'role' => $role,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="roles_delete", methods={"DELETE"})
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Roles                         $role
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(Request $request, Roles $role) : Response
    {
        if ($this->isCsrfTokenValid('delete' . $role->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($role);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_roles_list');
    }
}
