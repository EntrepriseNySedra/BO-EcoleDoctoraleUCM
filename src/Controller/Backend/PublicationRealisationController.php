<?php


namespace App\Controller\Backend;

use App\Entity\PublicationRealisation;
use App\Form\PublicationRealisationType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Services\BreadcrumbsService;
use App\Repository\PublicationRealisationRepository;
use Symfony\Component\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Serializer\Annotation\Groups;

class PublicationRealisationController extends AbstractController
{

     /**
     * @Route("/api/domaine/recherche", name="domaine_index", methods={"GET"})
     */
    public function listerWebService(PublicationRealisationRepository $laboratoiresRepository, SerializerInterface $serializer): Response
    {
        $laboratoires = $laboratoiresRepository->findAll();
        $jsonContent = $serializer->serialize($laboratoires, 'json');
       // $laboratoires = $laboratoiresRepository->findAll();
        //$jsonContent = $serializer->serialize($laboratoires, 'json', ['groups' => ['programme_basic']]);
        return new Response($jsonContent, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }


    /**
     * @Route("/PublicationRealisation/list", name="PublicationRealisation_index", methods={"GET"})
     */
    public function index(): Response
    {
        $PublicationRealisations = $this->getDoctrine()->getRepository(PublicationRealisation::class)->findAll();

        return $this->render('backend/PublicationRealisation/index.html.twig', [
            'PublicationRealisations' => $PublicationRealisations,
        ]);
    }

    /**
     * @Route("/PublicationRealisation/new", name="PublicationRealisation_add", methods={"GET","POST"})
     */
    public function new(Request $request, SluggerInterface $slugger): Response
    {
        $PublicationRealisation = new PublicationRealisation();
        $form = $this->createForm(PublicationRealisationType::class, $PublicationRealisation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

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
                $PublicationRealisation->setImage($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($PublicationRealisation);
            $entityManager->flush();

            return $this->redirectToRoute('PublicationRealisation_index');
        }

        return $this->render('backend/PublicationRealisation/add.html.twig', [
            'PublicationRealisation' => $PublicationRealisation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/PublicationRealisation/{id}/edit", name="PublicationRealisation_edit", methods={"GET","POST"})
     */
     public function edit(Request $request, PublicationRealisation $PublicationRealisation, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(PublicationRealisationType::class, $PublicationRealisation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

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
                $PublicationRealisation->setImage($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($PublicationRealisation);
            $entityManager->flush();

            //return $this->redirectToRoute('PublicationRealisation_index');
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('PublicationRealisation_index');
        }

        return $this->render('backend/PublicationRealisation/edit.html.twig', [
            'PublicationRealisation' => $PublicationRealisation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/PublicationRealisation/{id}", name="PublicationRealisation_delete", methods={"POST"})
     */
    public function delete(Request $request, PublicationRealisation $PublicationRealisation): Response
    {
        if ($this->isCsrfTokenValid('delete'.$PublicationRealisation->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($PublicationRealisation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('PublicationRealisation_index');
    }

  
}
