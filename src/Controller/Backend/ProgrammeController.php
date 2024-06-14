<?php

namespace App\Controller\Backend;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Programme;
use App\Repository\ProgrammeRepository;
use App\Services\BreadcrumbsService;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ProgrammeType;
use Symfony\Component\Serializer\SerializerInterface;
use App\Manager\ProgrammeManager;
use Symfony\Component\String\Slugger\SluggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Serializer\Annotation\Groups;


class ProgrammeController extends AbstractController
{
     /**
     * Listing events
     *
     * @Route("/admin/programmes/list", name="admin_programme_list", methods={ "GET" })
     * @param \App\Repository\ProgrammeRepository $programmeRepository
     * @param \App\Services\BreadcrumbsService $breadcrumbsService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function lister(BreadcrumbsService $breadcrumbsService, ProgrammeRepository $programmeRepository)
    {

        $programmes = $programmeRepository->findAll();
        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('programmes', $this->generateUrl('admin_programme_list'))
            ->add('Liste')
        ;

        return $this->render(
            'backend/programme/list.html.twig',
            [
                'programmes'  => $programmes,
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

     /**
     * @Route("/admin/programmes/{id}", name="admin_programme_delete", methods={"POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Programme    $programme
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(Request $request, Programme $programme) : Response
    {
        if ($this->isCsrfTokenValid('delete' . $programme->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($programme);
            $entityManager->flush();
            $this->addFlash('success', 'Succès suppression');
        }

        return $this->redirectToRoute('admin_programme_list');
    }



    /**
     * @Route("/programmes/ajout", name="admin_programmes_add", methods={"GET","POST"})
     */
    public function new(Request $request, SluggerInterface $slugger): Response
    {
        $Programme = new Programme();
        $form = $this->createForm(ProgrammeType::class, $Programme);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image_programmes')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // Utilisez le service Slugger pour générer un nom de fichier sûr
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();
    
                try {
                    $imageFile->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // gérer l'erreur si quelque chose se produit lors de l'upload du fichier
                }
    
                // mettez à jour le champ 'image' de l'entité pour stocker le nom du fichier
                $Programme->setImageProgrammes($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($Programme);
            $entityManager->flush();

            return $this->redirectToRoute('admin_programme_list');
        }

        return $this->render(
            'backend/programme/add.html.twig',
            [
                'Programme'      => $Programme,
                'form'          => $form->createView(),
            ]
        );
    }


    /**
     * @Route("/api/programmes", name="programmes_index", methods={"GET"})
     */
    public function listerWebService(ProgrammeRepository $programmeRepository, SerializerInterface $serializer): Response
    {
        //$programmes = $programmeRepository->findAllProgrammesWithMention();
        //$jsonContent = $serializer->serialize($programmes, 'json');
        $programmes = $programmeRepository->findAll();
        $jsonContent = $serializer->serialize($programmes, 'json');
        // $Partenariats = $PartenariatsRepository->findAll();
         //$jsonContent = $serializer->serialize($Partenariats, 'json', ['groups' => ['programme_basic']]);
         return new Response($jsonContent, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * @Route("/api/programmes/{id}", name="programmes_show", methods={"GET"})
     */
    public function show(Programme $programme, SerializerInterface $serializer): Response
    {
        $jsonContent = $serializer->serialize($programme, 'json');

        return new Response($jsonContent, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * @Route("/api/programmes/create", name="programmes_create", methods={"POST"})
     */
    public function create(Request $request, SerializerInterface $serializer): Response
    {
        $data = $request->getContent();
        $programme = $serializer->deserialize($data, Programme::class, 'json');

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($programme);
        $entityManager->flush();

        return new Response(null, Response::HTTP_CREATED);
    }

    /**
     * @Route("/Programme/{id}/edit", name="Programme_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Programme $Programme, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ProgrammeType::class, $Programme);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image_programmes')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // Utilisez le service Slugger pour générer un nom de fichier sûr
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();
    
                try {
                    $imageFile->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // gérer l'erreur si quelque chose se produit lors de l'upload du fichier
                }
    
                // mettez à jour le champ 'image' de l'entité pour stocker le nom du fichier
                $Programme->setImageProgrammes($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($Programme);
            $entityManager->flush();

            //return $this->redirectToRoute('Programme_index');
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_programme_list');
        }

        return $this->render('backend/programme/edit.html.twig', [
            'Programme' => $Programme,
            'form' => $form->createView(),
        ]);
    }
}
