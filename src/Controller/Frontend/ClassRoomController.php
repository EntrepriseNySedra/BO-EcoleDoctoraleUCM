<?php

namespace App\Controller\Frontend;

use App\Entity\Salles;
use App\Form\ClassroomType;
use App\Manager\SallesManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ClassRoomController
 * @IsGranted("ROLE_SCOLARITE")
 * @Route("/scolarite")
 *
 * @package App\Controller\Frontend
 */
class ClassRoomController extends AbstractFrontController
{

    /**
     * @Route("/salle", name="front_classroom_index", methods={"GET"})
     * @param \App\Manager\SallesManager $manager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(SallesManager $manager)
    {
        $classRooms = $manager->loadBy(['status' => Salles::STATUS_ENABLED]);

        return $this->render(
            'frontend/classroom/index.html.twig',
            [
                'classRooms' => $classRooms
            ]
        );
    }

    /**
     * @Route("/salle/ajout", name="front_classroom_create", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\SallesManager                $manager
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function create(Request $request, SallesManager $manager)
    {
        $classRoom = $manager->createObject();
        $form      = $this->createForm(ClassroomType::class, $classRoom);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->save($classRoom);

            return $this->redirectToRoute('front_classroom_index');
        }

        return $this->render(
            'frontend/classroom/form.html.twig',
            [
                'title' => 'Nouvelle salle',
                'form'  => $form->createView()
            ]
        );
    }

    /**
     * @Route("/salle/edit/{id}", name="front_classroom_edit", methods={"GET","POST"})
     * @param \App\Entity\Salles                        $classRoom
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\SallesManager                $manager
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function edit(Salles $classRoom, Request $request, SallesManager $manager)
    {
        $form = $this->createForm(ClassroomType::class, $classRoom);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->save($classRoom);

            return $this->redirectToRoute('front_classroom_index');
        }

        return $this->render(
            'frontend/classroom/form.html.twig',
            [
                'title' => 'Modification salle',
                'form'  => $form->createView()
            ]
        );
    }

    /**
     * @Route("/delete/{id}", name="front_classroom_delete", methods={"POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Salles                        $salle
     * @param \App\Manager\SallesManager                $manager
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function delete(Request $request, Salles $salle, SallesManager $manager)
    {
        if ($this->isCsrfTokenValid('delete' . $salle->getId(), $request->request->get('_token'))) {
            $salle->setStatus(Salles::STATUS_DISABLED);
            $salle->setUpdatedAt(new \DateTime());

            $manager->save($salle);
        }

        return $this->redirectToRoute('front_classroom_index');
    }
}