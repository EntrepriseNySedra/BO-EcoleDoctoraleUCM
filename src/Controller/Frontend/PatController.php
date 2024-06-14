<?php

namespace App\Controller\Frontend;
// use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
// use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
// use Psr\Container\ContainerInterface;

/**
 * Description of TeacherController.php.
 *
 * @package App\Controller\Frontend
 * @Route("/pat")
 */
class PatController extends AbstractController
{

    /**
     * @Route("/identification", name="front_pat_login")
     */
    public function login()
    {
        return $this->render(
            'frontend/common/login.html.twig',
            [
                'entity'     => 'Administratif',
                'espacename' => 'Espace administratif et technique'
            ]
        );
    }
}