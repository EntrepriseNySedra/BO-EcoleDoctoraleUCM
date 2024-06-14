<?php

namespace App\Controller\Backend;

use App\Entity\Evenement;
use App\Form\EventType;
use App\Manager\EvenementManager;
use App\Repository\EvenementRepository;
use App\Services\BreadcrumbsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use \Psr\Container\ContainerInterface;
use Cocur\Slugify\Slugify;

class EvenementController extends AbstractController
{
    /**
     * Listing events
     *
     * @Route("/admin/events/list", name="admin_events_list", methods={ "GET" })
     * @param \App\Repository\EvenementRepository $eventRepository
     * @param \App\Services\BreadcrumbsService $breadcrumbsService
     * @param \App\Manager\EvenementManager    $eventManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(BreadcrumbsService $breadcrumbsService, EvenementRepository $eventRepository, EvenementManager $eventManager)
    {

        $events = $eventRepository->findAll();
        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('events', $this->generateUrl('admin_events_list'))
            ->add('Liste')
        ;

        return $this->render(
            'backend/events/list.html.twig',
            [
                'events'  => $events,
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Adding page
     * @Route("/admin/events/add", name="admin_events_add", methods={ "GET", "POST" })
     *
     * @param \App\Services\BreadcrumbsService          $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\EvenementManager             $eventManager
     * @param \App\Repository\EvenementRepository     $eventRepository
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function new(BreadcrumbsService $breadcrumbsService, Request $request, ContainerInterface $container, EvenementManager $eventManager, EvenementRepository $eventRepository)
    {
        $event = new Evenement();
        $form   = $this->createForm(EventType::class, $event);
        
        $form->handleRequest($request);
        $slugify = new Slugify();
        $slug = $slugify->slugify($event->getTitle());
        $event->setSlug($slug);

        if ($form->isSubmitted() && $form->isValid()) {
            $eventManager->save($event);
            return $this->redirectToRoute('admin_events_list');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('events', $this->generateUrl('admin_events_list'))
            ->add('Ajout')
        ;

        return $this->render('backend/events/add.html.twig', 
            [
                'event'   => $event,
                'form'        => $form->createView(),
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs()
            ]
        );
    }

    /**
     * @Route("/admin/events/{id}/edit", name="admin_events_edit", methods={"GET","POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Evenement                     $event
     * @param \App\Repository\RubriqueRepository $rubriqueRepository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request, ContainerInterface $container, Evenement $event) : Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $slugify = new Slugify();
            $slug = $slugify->slugify($event->getTitle());
            $event->setSlug($slug);

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_events_list');
        }

        return $this->render(
            'backend/events/edit.html.twig',
            [
                'event'   => $event,
                'form'      => $form->createView()
            ]
        );
    }


    /**
     * @Route("/admin/events/{id}", name="admin_events_delete", methods={"POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Evenement                     $event
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(Request $request, Evenement $event) : Response
    {
        if ($this->isCsrfTokenValid('delete' . $event->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($event);
            $entityManager->flush();
            $this->addFlash('success', 'Succès suppression');
        }

        return $this->redirectToRoute('admin_events_list');
    }


}
