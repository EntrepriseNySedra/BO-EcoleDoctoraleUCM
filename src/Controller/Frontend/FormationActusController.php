<?php

namespace App\Controller\Frontend;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FormationActusController extends AbstractController
{
    /**
     * @Route("/formation/actualites", name="actualites")
     */
    public function formationActus() : Response
    {
        return $this->render(
            'frontend/formation/formation-actus.html.twig'
        );
    }
}
