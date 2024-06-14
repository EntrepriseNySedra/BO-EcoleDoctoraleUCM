<?php
/**
 * Description of CandidatureHistoriqueController.php.
 *
 * @package App\Controller\Backend
 * @author Joelio
 */

namespace App\Controller\Backend;

use App\Entity\Concours;
use App\Form\ConcoursType;
use App\Manager\CandidatureHistoriqueManager;
use App\Manager\ConcoursManager;
use App\Services\BreadcrumbsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/candidature/hitsorique")
 */
class CandidatureHistoriqueController extends BaseController
{

    /**
     * @Route("/", name="admin_candidature_historique_list", methods={"GET"})
     * @param \App\Manager\CandidatureHistoriqueManager $manager
     * @param \App\Services\BreadcrumbsService          $breadcrumbsService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function list(CandidatureHistoriqueManager $manager, BreadcrumbsService $breadcrumbsService) : Response
    {

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Candidature Historique', $this->generateUrl('admin_candidature_historique_list'))
            ->add('Liste')
        ;

        return $this->render(
            'backend/concours/candidature/historique/list.html.twig',
            [
                'historical'    => $manager->findByCriteria(),
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * @Route("/delete/{id}", name="admin_candidature_historique_delete", methods={"DELETE"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Concours                      $concours
     * @param \App\Manager\ConcoursManager              $manager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function delete(Request $request, Concours $concours, ConcoursManager $manager) : Response
    {
        if ($this->isCsrfTokenValid('delete' . $concours->getId(), $request->request->get('_token'))) {
            $concours->setDeletedAt(new \DateTime());

            $manager->save($concours);
            $this->addFlash('Infos', 'Suppression terminée avec succès');
        }

        return $this->redirectToRoute('admin_candidature_historique_list');
    }
}