<?php

namespace App\Controller\Frontend;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EspaceEtudiantController extends AbstractController
{
    /**
     * @Route("/espaceEtudiant/espace-etudiant", name="espace étudiant")
     */
    public function EspaceEtudiant() : Response
    {
        return $this->render(
            'frontend/espaceEtudiant/espace-etudiant.html.twig'
        );
    }
}
