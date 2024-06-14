<?php
/**
 * Description of ConcoursMatiereController.php.
 *
 * @package App\Controller\Backend
 * @author Joelio
 */

namespace App\Controller\Backend;

use App\Entity\Concours;
use App\Entity\ConcoursMatiere;
use App\Form\ConcoursMatiereType;
use App\Manager\ConcoursMaitereManager;
use App\Repository\MentionRepository;
use App\Services\BreadcrumbsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/concours/matiere")
 */
class ConcoursMatiereController extends BaseController
{

    /**
     * @Route("/", name="admin_concours_matiere_list", methods={"GET"})
     * @param \App\Manager\ConcoursMaitereManager $manager
     * @param \App\Services\BreadcrumbsService    $breadcrumbsService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function list(ConcoursMaitereManager $manager, BreadcrumbsService $breadcrumbsService) : Response
    {

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Concours Matieres', $this->generateUrl('admin_concours_matiere_list'))
            ->add('Liste')
        ;

        return $this->render(
            'backend/concoursmatiere/list.html.twig',
            [
                'matieres'    => $manager->findByCriteria(),
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * @Route("/add", name="admin_concours_matiere_add", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Services\BreadcrumbsService          $breadcrumbsService
     * @param \App\Manager\ConcoursMaitereManager       $manager
     * @param \App\Repository\MentionRepository         $mentionRepo
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function add(
        Request $request,
        BreadcrumbsService $breadcrumbsService,
        ConcoursMaitereManager $manager,
        MentionRepository $mentionRepo
    ) : Response {
        $matieres = $manager->createObject();
        $form     = $this->createForm(
            ConcoursMatiereType::class,
            $matieres
        );
        $mentions = $mentionRepo->findBy(['active' => 1], ['nom' => 'ASC']);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->save($matieres);

            $this->addFlash('Infos', 'Sauvegarde terminée avec succès');

            return $this->redirectToRoute('admin_concours_matiere_list');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Concours Matieres', $this->generateUrl('admin_concours_matiere_list'))
            ->add('Ajout')
        ;

        return $this->render(
            'backend/concoursmatiere/add.html.twig',
            [
                'mentions'    => $mentions,
                'matieres'    => $matieres,
                'form'        => $form->createView(),
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * @Route("/edit/{id}", name="admin_concours_matiere_edit", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\ConcoursMatiere               $matiere
     * @param \App\Services\BreadcrumbsService          $breadcrumbsService
     * @param \App\Manager\ConcoursMaitereManager       $manager
     * @param \App\Repository\MentionRepository         $mentionRepo
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function edit(
        Request $request,
        ConcoursMatiere $matiere,
        BreadcrumbsService $breadcrumbsService,
        ConcoursMaitereManager $manager,
        MentionRepository $mentionRepo
    ) : Response {
        $mentions = $mentionRepo->findBy(['active' => 1], ['nom' => 'ASC']);
        $form     = $this->createForm(
            ConcoursMatiereType::class,
            $matiere,
            [
                'matieres' => $matiere,
                'em'       => $this->getDoctrine()->getManager()
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->save($matiere);

            $this->addFlash('Infos', 'Sauvegarde terminée avec succès');

            return $this->redirectToRoute('admin_concours_matiere_list');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Concours Matieres', $this->generateUrl('admin_concours_matiere_list'))
            ->add('Modification')
        ;

        return $this->render(
            'backend/concoursmatiere/edit.html.twig',
            [
                'mentions'    => $mentions,
                'matieres'    => $matiere,
                'form'        => $form->createView(),
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * @Route("/delete/{id}", name="admin_concours_matiere_delete", methods={"DELETE"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\ConcoursMatiere               $matiere
     * @param \App\Manager\ConcoursMaitereManager       $manager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function delete(Request $request, ConcoursMatiere $matiere, ConcoursMaitereManager $manager) : Response
    {
        if ($this->isCsrfTokenValid('delete' . $matiere->getId(), $request->request->get('_token'))) {
            $matiere->setDeletedAt(new \DateTime());

            $manager->save($matiere);

            $this->addFlash('Infos', 'Suppression terminée avec succès');
        }

        return $this->redirectToRoute('admin_concours_matiere_list');
    }
}