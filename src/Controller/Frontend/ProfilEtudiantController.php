<?php

namespace App\Controller\Frontend;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfilEtudiantController extends AbstractController
{
    /**
     * @Route("/espaceEtudiant/profil-etudiant", name="profil étudiant")
     */
    public function ProfilEtudiant() : Response
    {
        return $this->render(
            'frontend/espaceEtudiant/profil-etudiant.html.twig'
        );
    }
}
