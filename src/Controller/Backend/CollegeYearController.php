<?php

namespace App\Controller\Backend;

use App\Entity\AnneeUniversitaire;
use App\Services\BreadcrumbsService;
use App\Form\CollegeYearType;
use App\Manager\AnneeUniversitaireManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CollegeYearController
 *
 * @package App\Controller\Backend
 */
class CollegeYearController extends AbstractController
{

    /**
     * List function
     *
     * @Route("/admin/college-year/liste", name="admin_college_year_index", methods={ "GET" })
     * @param \App\Services\BreadcrumbsService      $breadcrumbsService
     * @param \App\Manager\AnneeUniversitaireManager       $manager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(BreadcrumbsService $breadcrumbsService, AnneeUniversitaireManager $manager)
    {
        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Année universitaire')
        ;

        return $this->render(
            'backend/college-year/index.html.twig',
            [
                'collegeYear'   => $manager->loadAll(),
                'breadcrumbs'   => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Add function
     * 
     * @Route("admin/college-year/new", name="admin_college_year_new", methods={ "GET", "POST" })
     * @param \App\Entity\AnneeUniversitaire                           $collegeYear
     * @param \App\Services\BreadcrumbsService                  $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request         $request
     * @param \App\Manager\AnneeUniversitaireManager                   $manager
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function new(AnneeUniversitaire $collegeYear = null, BreadcrumbsService $breadcrumbsService, Request $request, AnneeUniversitaireManager $manager)
    {
        if(!$collegeYear)
        {
            $collegeYear = new AnneeUniversitaire();
        }

        //Created form
        $form = $this->createForm(CollegeYearType::class, $collegeYear);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
                $collegeYear->setCreatedAt(new \DateTime());
                $manager->save($collegeYear);
                $this->addFlash('Infos', 'Ajouté avec succès');
            return $this->redirectToRoute('admin_college_year_index');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Année universitaire', $this->generateUrl('admin_college_year_index'))
            ->add('Ajout')
        ;

        return $this->render(
            'backend/college-year/add.html.twig',
            [
                'collegeYear'       => $collegeYear,
                'form'              => $form->createView(),
                'breadcrumbs'       => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Edit function
     * 
     * @Route("/admin/college-year/{id}/edit", name="admin_college_year_edit", methods={ "GET", "POST" })
     * @param \App\Entity\AnneeUniversitaire                       $collegeYear
     * @param \App\Services\BreadcrumbsService              $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(AnneeUniversitaire $collegeYear, BreadcrumbsService $breadcrumbsService, Request $request) : Response
    {
        $form = $this->createForm(CollegeYearType::class, $collegeYear);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $collegeYear->setUpdatedAt(new \DateTime());
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('Infos', 'Modifié avec succès');
            return $this->redirectToRoute('admin_college_year_index');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Année universitaire', $this->generateUrl('admin_college_year_index'))
            ->add('Modification')
        ;

        return $this->render(
            'backend/college-year/edit.html.twig',
            [
                'collegeYear'      => $collegeYear,
                'form'  => $form->createView(),
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Delete function
     * 
     * @Route("/admin/college-year/delete/{id}", name="admin_college_year_delete", methods={"DELETE"})
     * @param \App\Entity\AnneeUniversitaire                       $collegeYear
     * @param \Symfony\Component\HttpFoundation\Request     $request
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteDepartement(AnneeUniversitaire $collegeYear, Request $request) : Response
    {
        if ($this->isCsrfTokenValid('delete' . $collegeYear->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($collegeYear);
            $entityManager->flush();
            $this->addFlash('Infos', 'Supprimé avec succès');
        }
        return $this->redirectToRoute('admin_college_year_index');
    }
}
