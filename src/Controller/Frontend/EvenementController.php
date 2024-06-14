<?php

namespace App\Controller\Frontend;

use App\Entity\Evenement;
use App\Repository\ActualiteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Services\MenusService;
use App\Manager\EvenementManager;
use Psr\Log\LoggerInterface;
use Doctrine\Common\Collections\Criteria;

class EvenementController extends AbstractController
{
    /**
     * @Route("/evenement", name="evenement")
     * @param \App\Repository\EvenementRepository $evtRepository
     * @param \App\Services\MenusService $menusService
     */
    public function index(
        EvenementManager    $evtManager,
        MenusService        $menusService,
        ActualiteRepository $actualiteRepository,
        LoggerInterface     $logger
        ) : Response
    {
        $toActuList           = $actualiteRepository->getLastActuality();
        $events = $evtManager->getAll();

        return $this->render(
            'frontend/evenement/index.html.twig',
            [
                'events'            => $events,
                'derniereActus'   => $toActuList
            ]
        );
    }

    /**
     * Detail function
     * 
     * @Route("/evenement/{slug}", name="details_evenement")
     * @param \App\Entity\Evenement                       $evenement
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Services\MenusService $menusService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getDetailEvenement(
        Request             $request,
        EvenementManager    $evtManager,
        ActualiteRepository $actualiteRepository,
        Evenement           $evenement
        ) : Response
    {
        $toActuList           = $actualiteRepository->getLastActuality();
        return $this->render(
            'frontend/evenement/details.html.twig',
            [
                'evenement' => $evenement,
                'derniereActus'   => $toActuList,
            ]
        );
    }
}
