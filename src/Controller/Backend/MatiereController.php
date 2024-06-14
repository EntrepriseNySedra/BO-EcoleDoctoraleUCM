<?php

namespace App\Controller\Backend;

use App\Entity\Matiere;
use App\Entity\UniteEnseignements;
use App\Services\BreadcrumbsService;
use App\Form\MatiereType;
use App\Repository\MatiereRepository;
use App\Repository\MentionRepository;
use App\Repository\ParcoursRepository;
use App\Manager\MatiereManager;
use App\Manager\EnseignantManager;
use App\Manager\UniteEnseignementsManager;
use App\Repository\UniteEnseignementsRepository;
use App\Repository\NiveauRepository;
use App\Repository\SemestreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MatiereController
 *
 * @package App\Controller\Backend
 */
class MatiereController extends AbstractController
{
    /**
     * Add function
     * 
     * @Route("/admin/formation/matiere/new", name="admin_rf_matiere_new", methods={ "GET", "POST" })
     * @param \App\Entity\Matiere                                   $matiere
     * @param \App\Services\BreadcrumbsService                      $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request             $request
     * @param \App\Manager\MatiereManager                           $manager
     * @param \App\Repository\UniteEnseignementsRepository          $u_eRepository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function new(Matiere $matiere = null, BreadcrumbsService $breadcrumbsService, Request $request, MentionRepository $mentionRepo, MatiereManager $manager, UniteEnseignementsRepository $u_eRepository, NiveauRepository $niveauRepository)
    {
        if(!$matiere)
        {
            $matiere = new Matiere();
        }

        $mentions = $mentionRepo->findBy(['active' => 1]);
        $parcours = [];
        $niveaux = $niveauRepository->findAll();

        //Created form
        $form = $this->createForm(MatiereType::class, $matiere);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $matiere->setCreatedAt(new \DateTime());
            $manager->save($matiere);
            $this->addFlash('Infos', 'Ajouté avec succès');
            return $this->redirectToRoute('admin_rf_matiere_liste');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Formations')
            ->add('Matiere',$this->generateUrl('admin_rf_matiere_liste'))
            ->add('Ajout')
        ;

        return $this->render(
            'backend/matiere/add.html.twig',
            [
                'mentions'              => $mentions,
                'parcours'              => $parcours,
                'niveaux'               => $niveaux,
                'matiere'               => $matiere,
                'unite_enseignement'    => $u_eRepository->findAll(),
                'formMatiere'           => $form->createView(),
                'breadcrumbs'           => $breadcrumbsService->getBreadcrumbs()
            ]
        );
    }

    /**
     * Edit function
     * 
     * @Route("/admin/formation/matiere/{id}/edit", name="admin_rf_matiere_edit", methods={ "GET", "POST" })
     * @param \App\Entity\Matiere                                   $matiere
     * @param \App\Services\BreadcrumbsService                      $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request             $request
     * @param \App\Repository\UniteEnseignementsRepository          $u_eRepository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Matiere $matiere, BreadcrumbsService $breadcrumbsService, Request $request, UniteEnseignementsRepository $u_eRepository, MentionRepository $mentionRepo, ParcoursRepository $parcoursRepo, NiveauRepository $niveauRepository, SemestreRepository $semestreRepo) : Response
    {
        $mentions = $mentionRepo->findBy(['active' => 1]);
        $parcours = $parcoursRepo->findAll();
        $niveaux = $niveauRepository->findAll();
        $semestres = $semestreRepo->findAll();
        $ues = $u_eRepository->findBy(
            [
                'mention' => $matiere->getUniteEnseignements()->getMention(),
                'niveau' => $matiere->getUniteEnseignements()->getNiveau()
            ]
        );
        $form = $this->createForm(MatiereType::class, $matiere);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $matiere->setUpdatedAt(new \DateTime());
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('Infos', 'Modifié avec succès');
            return $this->redirectToRoute('admin_rf_matiere_liste');
        }        

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Formations')
            ->add('Matière',$this->generateUrl('admin_rf_matiere_liste'))
            ->add('Modification')
        ;

        return $this->render(
            'backend/matiere/edit.html.twig',
            [
                'mentions'              => $mentions,
                'parcours'              => $parcours,
                'niveaux'               => $niveaux,
                'semestres'             => $semestres,
                'matiere'               => $matiere,
                'unite_enseignement'    => $ues,
                'formMatiere'           => $form->createView(),
                'breadcrumbs'           => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Delete function
     * 
     * @Route("/admin/formation/matiere/delete/{id}", name="admin_rf_matiere_delete", methods={"DELETE"})
     * @param \App\Entity\Matiere                       $matiere
     * @param \Symfony\Component\HttpFoundation\Request $request
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(Matiere $matiere, Request $request) : Response
    {
        if ($this->isCsrfTokenValid('delete' . $matiere->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($matiere);
            $entityManager->flush();
            $this->addFlash('Infos', 'Supprimé avec succès');
        }
        return $this->redirectToRoute('admin_rf_matiere_liste');
    }

    /**
     * List function
     *
     * @Route("/admin/formation/matiere/liste", name="admin_rf_matiere_liste", methods={ "GET" })
     * @param \App\Services\BreadcrumbsService          $breadcrumbsService
     * @param \App\Repository\MatiereRepository         $repo
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(BreadcrumbsService $breadcrumbsService, MatiereRepository $repo)
    {
        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Formations')
            ->add('Matière')
        ;

        return $this->render(
            'backend/matiere/list.html.twig',
            [
                'matieres'    => $repo->findAll(),
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * render UEs filter by mention, parcours, niveau, semestre
     * @Route("/admin/formation/matiere/unite_enseignements", name="admin_rf_matiere_unite_enseignements", * methods={"GET"})
     * @param \App\Manager\UniteEnseignementsManager     $ueManager
     * use Symfony\Component\HttpFoundation\Request;
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getUeByMixedFilter(UniteEnseignementsManager $ueManager, Request $request) {
        $mentionId  = $request->query->getInt('mention_id');
        $parcoursId  = $request->query->getInt('parcours_id');
        $niveauId   = $request->query->getInt('niveau_id');
        $semestreId = $request->query->getInt('semestre_id');

        $ues = $ueManager->findAllByCriteria(
            [
                'mentionId'     => $mentionId,
                'parcoursId'    => $parcoursId,
                'niveauId'      => $niveauId,
                'semestreId'    => $semestreId
            ]
        );
        return $this->render(
            'backend/matiere/_ue_options.html.twig',
            [
                'unite_enseignements'   => $ues
            ]
        );
    }

}