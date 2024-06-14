<?php
/**
 * Description of ConcoursController.php.
 *
 * @package App\Controller\Backend
 * @author Joelio
 */

namespace App\Controller\Backend;

use App\Entity\Concours;
use App\Form\ConcoursType;
use App\Form\ConcoursConfigsType;
use App\Manager\ConcoursConfigManager;
use App\Manager\ConcoursManager;
use App\Manager\ParcoursManager;
use App\Services\BreadcrumbsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/concours")
 */
class ConcoursController extends BaseController
{

    /**
     * @Route("/", name="admin_concours_list", methods={"GET"})
     * @param \App\Manager\ConcoursManager     $manager
     * @param \App\Services\BreadcrumbsService $breadcrumbsService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function list(ConcoursManager $manager, BreadcrumbsService $breadcrumbsService) : Response
    {

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Concours', $this->generateUrl('admin_concours_list'))
            ->add('Liste')
        ;

        return $this->render(
            'backend/concours/list.html.twig',
            [
                'concours'    => $manager->findByCriteria(),
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * @Route("/add", name="admin_concours_add", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Services\BreadcrumbsService          $breadcrumbsService
     * @param \App\Manager\ConcoursManager              $manager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function add(Request $request, BreadcrumbsService $breadcrumbsService, ConcoursManager $manager, ConcoursConfigManager           $concoursConfManager) : Response
    {
        $concoursConf = $concoursConfManager->loadOneBy([]);
        $currentAnneUniv= $concoursConf->getAnneeUniversitaire();
        $concours = $manager->createObject();
        $form     = $this->createForm(
            ConcoursType::class,
            $concours
        );
        $form->handleRequest($request);
        //dump($form->getErrors(true));die;
        if ($form->isSubmitted() && $form->isValid()) {
            if(!$concours->getDeliberation())
                $concours->setDeliberation(Concours::DEFAULT_DELIBERATION);            
            $concours->setResultStatut(Concours::STATUS_CREATED);
            $concours->setAnneeUniversitaire($currentAnneUniv);
            $concours->setStartDate($currentAnneUniv->getDateDebut());
            $concours->setEndDate($currentAnneUniv->getDateFin());
            $manager->save($concours);

            $this->addFlash('Infos', 'Sauvegarde terminée avec succès');

            return $this->redirectToRoute('admin_concours_list');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Concours', $this->generateUrl('admin_concours_list'))
            ->add('Ajout')
        ;

        return $this->render(
            'backend/concours/add.html.twig',
            [
                'concours'    => $concours,
                'form'        => $form->createView(),
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * @Route("/edit/{id}", name="admin_concours_edit", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Concours                      $concours
     * @param \App\Services\BreadcrumbsService          $breadcrumbsService
     * @param \App\Manager\ConcoursManager              $manager
     * @param \App\Manager\ConcoursConfigManager        $concoursConfManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function edit(
        Request                         $request,
        Concours                        $concours,
        BreadcrumbsService              $breadcrumbsService,
        ConcoursManager                 $manager,
        ConcoursConfigManager           $concoursConfManager
    ) : Response {
        $concoursConf = $concoursConfManager->loadOneBy([]);
        $currentAnneUniv= $concoursConf->getAnneeUniversitaire();
        $form = $this->createForm(
            ConcoursType::class,
            $concours,
            [
                'concours' => $concours,
                'em'       => $this->getDoctrine()->getManager()
            ]
        );
        //dump($concours);die;
        //dump($form->getErrors(true));die;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if(!$concours->getDeliberation())
                $concours->setDeliberation(Concours::DEFAULT_DELIBERATION);
            $concours->setResultStatut(Concours::STATUS_CREATED);
            $concours->setAnneeUniversitaire($currentAnneUniv);
            // /dump($form->getData());die;
            // $concours->setStartDate($currentAnneUniv->getDateDebut());
            // $concours->setEndDate($currentAnneUniv->getDateFin());
            $manager->save($concours);
            $this->addFlash('Infos', 'Sauvegarde terminée avec succès');

            return $this->redirectToRoute('admin_concours_list');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Concours', $this->generateUrl('admin_concours_list'))
            ->add('Modification')
        ;

        return $this->render(
            'backend/concours/edit.html.twig',
            [
                'concours'    => $concours,
                'form'        => $form->createView(),
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * @Route("/delete/{id}", name="admin_concours_delete", methods={"DELETE"})
     * @param \Symfony\Component\HttpFoundation\Request $request
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

        return $this->redirectToRoute('admin_concours_list');
    }

    /**
     * @Route("/parcours/options", name="admin_concours_parcours_options", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Services\BreadcrumbsService          $breadcrumbsService
     * @param \App\Manager\ParcoursManager              $manager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function getParcoursOptions(Request $request, ParcoursManager $manager) {
        $mentionId     = $request->get('mention_id');
        $niveauId    = $request->get('niveau_id');
        $parcoursOptions = $manager->loadBy(
            [
                'mention' => $mentionId,
                'niveau' => $niveauId
            ]
        );
        
        return $this->render(
            'backend/concours/_parcours_options.html.twig',
            [
                'parcours'              => $parcoursOptions
            ]
        );

    }

    /**
     * @Route("/configs", name="admin_concours_configs", methods={"GET", "POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Services\BreadcrumbsService          $breadcrumbsService
     * @param \App\Manager\ConcoursConfigManager              $manager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function setConfigs(Request $request, ConcoursConfigManager $manager, BreadcrumbsService $breadcrumbsService) {
        $concours = $manager->loadOneBy([]) ? $manager->loadOneBy([]): $manager->createObject();
        //dump($concours);die;
        $form = $this->createForm(
            ConcoursConfigsType::class,
            $concours,
            [
                'concours_config' => $concours,
                'em'       => $this->getDoctrine()->getManager()
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->save($concours);
            $this->addFlash('Infos', 'Sauvegarde terminée avec succès');

            return $this->redirectToRoute('admin_concours_configs');
        }
        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Concours', $this->generateUrl('admin_concours_list'))
            ->add('Configuration')
        ;
        return $this->render(
            'backend/concours/config.html.twig',
            [
                'concours'    => $concours,
                'form'        => $form->createView(),
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }
}