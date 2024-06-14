<?php
/**
 * Description of ConcoursCandidatureController.php.
 *
 * @package App\Controller\Backend
 * @author Joelio
 */

namespace App\Controller\Backend;

use App\Entity\Concours;
use App\Entity\ConcoursCandidature;
use App\Form\ConcoursType;
use App\Manager\ConcoursManager;
use App\Services\BreadcrumbsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/concours/candidature")
 */
class ConcoursCandidatureController extends BaseController
{

    /**
     * @Route("/", name="admin_concours_candidature_candidature_list", methods={"GET"})
     * @param \App\Manager\ConcoursManager     $manager
     * @param \App\Services\BreadcrumbsService $breadcrumbsService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function list(ConcoursManager $manager, BreadcrumbsService $breadcrumbsService) : Response
    {

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Concours', $this->generateUrl('admin_concours_candidature_list'))
            ->add('Liste')
        ;

        return $this->render(
            'backend/concours/list.html.twig',
            [
                'concours'    => $manager->findByCriteria(),
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * @Route("/edit/{id}", name="admin_concours_candidature_edit", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\ConcoursCandidature           $candidature
     * @param \App\Services\BreadcrumbsService          $breadcrumbsService
     * @param \App\Manager\ConcoursManager              $manager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function edit(
        Request $request,
        ConcoursCandidature $candidature,
        BreadcrumbsService $breadcrumbsService,
        ConcoursManager $manager
    ) : Response {
        $form = $this->createForm(
            ConcoursType::class,
            $candidature,
            [
                'candidature' => $candidature,
                'em'       => $this->getDoctrine()->getManager()
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->save($candidature);

            $this->addFlash('Infos', 'Sauvegarde terminée avec succès');

            return $this->redirectToRoute('admin_concours_candidature_list');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('candidature', $this->generateUrl('admin_concours_candidature_list'))
            ->add('Modification')
        ;

        return $this->render(
            'backend/concours/edit.html.twig',
            [
                'concours'    => $candidature,
                'form'        => $form->createView(),
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * @Route("/delete/{id}", name="admin_concours_candidature_delete", methods={"DELETE"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\ConcoursCandidature           $candidature
     * @param \App\Manager\ConcoursManager              $manager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function delete(Request $request, ConcoursCandidature $candidature, ConcoursManager $manager) : Response
    {
        if ($this->isCsrfTokenValid('delete' . $candidature->getId(), $request->request->get('_token'))) {
            $candidature->setDeletedAt(new \DateTime());

            $manager->save($candidature);
            $this->addFlash('Infos', 'Suppression terminée avec succès');
        }

        return $this->redirectToRoute('admin_concours_candidature_list');
    }
}