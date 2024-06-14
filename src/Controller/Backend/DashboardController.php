<?php
/**
 * Description of DashboardController.php.
 *
 * @package App\Backend
 * @author  Joelio
 */

namespace App\Controller\Backend;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */
class DashboardController extends Controller
{

    /**
     * @Route("/", name="admin_dashboard", methods={"GET"})
     */
    public function dashboard()
    {
        return $this->render(
            'backend/dashboard/dashboard.html.twig',
            [
                // ...
            ]
        );
    }
}