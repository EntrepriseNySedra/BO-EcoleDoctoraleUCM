<?php

namespace App\Controller\Backend;

use App\Entity\Medias;
use App\Entity\Document;
use App\Form\MediasType;
use App\Manager\MediasManager;
use App\Repository\MediasRepository;
use App\Repository\DocumentRepository;
use App\Services\BreadcrumbsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use \Psr\Container\ContainerInterface;

class MediaController extends AbstractController {

    /**
     * Listing medias
     *
     * @Route("/admin/medias/list", name="admin_medias_list", methods={ "GET" })
     * @param \App\Repository\MediasRepository $mediasRepository
     * @param \App\Services\BreadcrumbsService $breadcrumbsService
     * @param \App\Manager\MediasManager    $mediasManager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(BreadcrumbsService $breadcrumbsService, MediasRepository $mediasRepository) {

        $medias = [];

        // begin manage breadcrumbs
        $breadcrumbsService
                ->add('Medias', $this->generateUrl('admin_medias_list'))
                ->add('Liste')
        ;

        return $this->render(
                        'backend/medias/list.html.twig',
                        [
                            'medias' => $mediasRepository->findAll(),
                            'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
                        ]
        );
    }

    /**
     * Adding page
     * @Route("/admin/medias/add", name="admin_medias_add", methods={ "GET", "POST" })
     *
     * @param \App\Services\BreadcrumbsService          $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Manager\MediasManager                $manager
     * @param \App\Repository\DocumentRepository        $documentRepository
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function new(BreadcrumbsService $breadcrumbsService, ContainerInterface $container, Request $request, MediasManager $manager, DocumentRepository $documentRepository) {
        $medias = new Medias();

        $form = $this->createForm(
                MediasType::class,
                $medias
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();
            
            // Upload file
            if ($file) {
                $directory      = $container->getParameter('document_directory');
                $uploader       = new \App\Services\FileUploader($directory);
                
                $fileDirectory = $medias->getDocument()->getUuid()."/".$medias->getUuid();
                $file_dispaly   = $uploader->upload($file,$directory,$fileDirectory);
                
                //$file_dispaly      = $uploader->upload($_FILES, "_file_dispaly");      // Upload image
                //$file_grand_photo  = $uploader->upload($_FILES, "_file_grand_photo"); // Upload image
                 
                $medias->setPath($file_dispaly["filename"]);
            }
            
            $manager->save($medias);

            return $this->redirectToRoute('admin_medias_list');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
                ->add('Medias', $this->generateUrl('admin_medias_list'))
                ->add('Ajout')
        ;

        return $this->render('backend/medias/add.html.twig',
                        [
                            'media' => $medias,
                            'form' => $form->createView(),
                            'documents' => $documentRepository->findAll(),
                            'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
                        ]
        );
    }

    /**
     * @Route("/admin/medias/{id}/edit", name="admin_medias_edit", methods={"GET","POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Medias                        $medias
     * @param \App\Repository\DocumentRepository        $documentRepository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request, ContainerInterface $container, Medias $medias, DocumentRepository $documentRepository): Response {
        $form = $this->createForm(MediasType::class, $medias);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();
            
            // Upload file
            if ($file) {
                $directory      = $container->getParameter('document_directory');
                $uploader       = new \App\Services\FileUploader($directory);
                
                $fileDirectory = $medias->getDocument()->getUuid()."/".$medias->getUuid();
                $file_dispaly   = $uploader->upload($file,$directory,$fileDirectory);
                 
                $medias->setPath($file_dispaly["filename"]);
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_medias_list');
        }

        return $this->render(
                        'backend/medias/edit.html.twig',
                        [
                            'media' => $medias,
                            'documents' => $documentRepository->findAll(),
                            'form' => $form->createView(),
                        ]
        );
    }

    /**
     * @Route("/admin/medias/{id}", name="admin_medias_delete", methods={"POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Medias                     $medias
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(Request $request, Medias $medias): Response {
        if ($this->isCsrfTokenValid('delete' . $medias->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($medias);
            $entityManager->flush();
            $this->addFlash('success', 'Succès suppression');
        }

        return $this->redirectToRoute('admin_medias_list');
    }

}
