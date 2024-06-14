<?php

namespace App\Controller\Backend;

use App\Entity\UniteEnseignements;
use App\Form\UniteEnseignementType;
use App\Services\BreadcrumbsService;
use App\Repository\UniteEnseignementsRepository;
use App\Repository\MentionRepository;
use App\Repository\ParcoursRepository;
use App\Repository\SemestreRepository;
use App\Repository\NiveauRepository;
use App\Manager\UniteEnseignementsManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UniteEnseignementController
 *
 * @package App\Controller\Backend
 */
class UniteEnseignementController extends AbstractController
{
    /**
     * Add function
     * 
     * @Route("admin/formation/unite_enseignements/new", name="admin_rf_unite_enseignements_new", methods={ "GET", "POST" })
     * @param \App\Entity\UniteEnseignements                            $u_e
     * @param \App\Services\BreadcrumbsService                          $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request                 $request
     * @param \App\Manager\UniteEnseignementsManager                    $manager
     * @param \App\Repository\ParcoursRepository                        $parcoursRepository 
     * @param \App\Repository\NiveauRepository                          $niveauRepository 
     * @param \App\Repository\SemestreRepository                        $semestreRepository 
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function new(UniteEnseignements $u_e = null, BreadcrumbsService $breadcrumbsService, Request $request, UniteEnseignementsManager $manager, ParcoursRepository $parcoursRepository, NiveauRepository $niveauRepository, SemestreRepository $semestreRepository, MentionRepository $mentionRepo)
    {
        if(!$u_e)
        {
            $u_e = new UniteEnseignements();
        }
        $mentions = $mentionRepo->findBy(['active' => 1]);
        $parcours = [];
        $niveau = $niveauRepository->findAll();
        $semestre = $semestreRepository->findAll();
        
        //Created form
        $form = $this->createForm(UniteEnseignementType::class, $u_e);
        $form->handleRequest($request);
        // begin manage breadcrumbs
        $breadcrumbs = $breadcrumbsService
            ->add('Formations')
            ->add('Unite enseignements',$this->generateUrl('admin_rf_unite_enseignements_liste'))
            ->add('Ajout')
            ->getBreadcrumbs()
        ;
        $u_e->setCreatedAt(new \DateTime());
        if ($form->isSubmitted() && $form->isValid()) {
            {
                $manager->save($u_e);
                $this->addFlash('Infos', 'Ajouté avec succès');
                return $this->redirectToRoute('admin_rf_unite_enseignements_liste');
            } 
        }
        
        $formUe = $form->createView();      

        return $this->render(
            'backend/unite_enseignement/add.html.twig',
            [
                'unite_enseignements'       => $u_e,
                'mentions'                  => $mentions,
                'parcours'                  => $parcours,
                'niveau'                    => $niveau,
                'semestre'                  => $semestre,
                'formUE'                    => $formUe,
                'breadcrumbs'               => $breadcrumbs
            ]
        );
    }

    /**
     * Edit function
     * 
     * @Route("/admin/formation/unite_enseignements/{id}/edit", name="admin_rf_unite_enseignements_edit", methods={ "GET", "POST" })
     * @param \App\Entity\UniteEnseignements                    $u_e
     * @param \App\Services\BreadcrumbsService                  $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request         $request
     * @param \App\Repository\ParcoursRepository                $parcoursRepository 
     * @param \App\Repository\NiveauRepository                  $niveauRepository 
     * @param \App\Repository\SemestreRepository                $semestreRepository 
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(UniteEnseignements $u_e, BreadcrumbsService $breadcrumbsService, Request $request, ParcoursRepository $parcoursRepository, NiveauRepository $niveauRepository, SemestreRepository $semestreRepository, MentionRepository $mentionRepo ) : Response
    {
        $form = $this->createForm(UniteEnseignementType::class, $u_e);
        $form->handleRequest($request);
        $formUe = $form->createView();
        $mentions = $mentionRepo->findBy(['active' => 1]);
        $parcours = $parcoursRepository->findBy([
            'mention' => $u_e->getMention()
        ]);
        $niveau = $niveauRepository->findAll();
        $semestre = $semestreRepository->findAll();
        
        // begin manage breadcrumbs
        $breadcrumbs = $breadcrumbsService
            ->add('Formations')
            ->add('Unite enseignements',$this->generateUrl('admin_rf_unite_enseignements_liste'))
            ->add('Ajout')
            ->getBreadcrumbs()
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $u_e->setUpdatedAt(new \DateTime());
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('Infos', 'Modifié avec succès');
            return $this->redirectToRoute('admin_rf_unite_enseignements_liste');
        }

        return $this->render(
            'backend/unite_enseignement/edit.html.twig',
            [
                'unite_enseignements'       => $u_e,
                'mentions'                  => $mentions,
                'parcours'                  => $parcours,
                'niveau'                    => $niveau,
                'semestre'                  => $semestre,
                'formUE'                    => $formUe,
                'breadcrumbs'               => $breadcrumbs
            ]
        );
    }

    /**
     * Delete function
     * 
     * @Route("/admin/formation/unite_enseignements/delete/{id}", name="admin_rf_unite_enseignements_delete", methods={"DELETE"})
     * @param \App\Entity\UniteEnseignements                    $u_e
     * @param \Symfony\Component\HttpFoundation\Request         $request
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(UniteEnseignements $u_e, Request $request) : Response
    {
        if ($this->isCsrfTokenValid('delete' . $u_e->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($u_e);
            $entityManager->flush();
            $this->addFlash('Infos', 'Supprimé avec succès');
        }
        return $this->redirectToRoute('admin_rf_unite_enseignements_liste');
    }

    /**
     * List function
     *
     * @Route("/admin/formation/unite_enseignements/liste", name="admin_rf_unite_enseignements_liste", methods={ "GET" })
     * @param \App\Services\BreadcrumbsService                  $breadcrumbsService
     * @param \App\Repository\UniteEnseignementsRepository      $repo
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(BreadcrumbsService $breadcrumbsService, UniteEnseignementsRepository $repo)
    {
        $typeList = array_flip(UniteEnseignements::$typeList);
        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Formations')
            ->add('Unite enseignements')
        ;

        return $this->render(
            'backend/unite_enseignement/list.html.twig',
            [
                'typeList' => $typeList,
                'unite_enseignements'   => $repo->findAll(),
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * render parcours of selected mention
     * @Route("/admin/formation/unite_enseignements/mention/{mention_id}/parcours", name="admin_rf_unite_enseignements_mention_parcours", * methods={"GET"})
     * @param \App\Repository\ParcoursRepository     $parcoursRepo
     * use Symfony\Component\HttpFoundation\Request;
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getMentionParcours(ParcoursRepository $parcoursRepo, Request $request) {
        $mentionId = $request->query->getInt('mention_id');
        $niveauId = $request->query->getInt('niveau_id');
        $parcours = $parcoursRepo->findBy(['mention' => $mentionId, 'niveau' => $niveauId]);
        return $this->render(
            'backend/unite_enseignement/_parcours_options.html.twig',
            [
                'parcours'   => $parcours
            ]
        );
    }

    /**
     * render semestre of selected niveau
     * @Route("/admin/formation/unite_enseignement/niveau/{niveau_id}/semestres", name="admin_rf_niveau_semestre_parcours", * methods={"GET"})
     * @param \App\Repository\SemestreRepository     $semestreRepo
     * use Symfony\Component\HttpFoundation\Request;
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getNiveauSemestres(SemestreRepository $semestreRepo, Request $request) {
        $niveauId = $request->query->getInt('niveau_id');
        $semestres = $semestreRepo->findBy(['niveau' => $niveauId]);
        return $this->render(
            'backend/unite_enseignement/_semestres_options.html.twig',
            [
                'semestres'   => $semestres
            ]
        );
    }


}
