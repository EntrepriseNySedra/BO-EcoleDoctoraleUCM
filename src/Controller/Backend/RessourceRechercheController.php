<?php


namespace App\Controller\Backend;

use App\Entity\RessourceRecherche;
use App\Form\RessourceRechercheType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Services\BreadcrumbsService;
use App\Repository\RessourceRechercheRepository;
use Symfony\Component\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Serializer\Annotation\Groups;

class RessourceRechercheController extends AbstractController
{

     /**
     * @Route("/api/Ressource/recherche", name="Ressource_index", methods={"GET"})
     */
    public function listerWebService(RessourceRechercheRepository $laboratoiresRepository, SerializerInterface $serializer): Response
    {
        $laboratoires = $laboratoiresRepository->findAll();
        $jsonContent = $serializer->serialize($laboratoires, 'json');
       // $laboratoires = $laboratoiresRepository->findAll();
        //$jsonContent = $serializer->serialize($laboratoires, 'json', ['groups' => ['programme_basic']]);
        return new Response($jsonContent, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }


    /**
     * @Route("/RessourceRecherche/list", name="RessourceRecherche_index", methods={"GET"})
     */
    public function index(): Response
    {
        $RessourceRecherches = $this->getDoctrine()->getRepository(RessourceRecherche::class)->findAll();

        return $this->render('backend/RessourceRecherche/index.html.twig', [
            'RessourceRecherches' => $RessourceRecherches,
        ]);
    }

    /**
     * @Route("/RessourceRecherche/new", name="admin_Ressource_add", methods={"GET","POST"})
     */
    public function new(Request $request, SluggerInterface $slugger): Response
    {
        $RessourceRecherche = new RessourceRecherche();
        $form = $this->createForm(RessourceRechercheType::class, $RessourceRecherche);
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
                $RessourceRecherche->setImage($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($RessourceRecherche);
            $entityManager->flush();

            return $this->redirectToRoute('RessourceRecherche_index');
        }

        return $this->render('backend/RessourceRecherche/add.html.twig', [
            'RessourceRecherche' => $RessourceRecherche,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/Ressource/{id}/edit", name="RessourceRecherche_edit", methods={"GET","POST"})
     */
     public function edit(Request $request, RessourceRecherche $RessourceRecherche, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(RessourceRechercheType::class, $RessourceRecherche);
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
                $RessourceRecherche->setImage($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($RessourceRecherche);
            $entityManager->flush();

            //return $this->redirectToRoute('RessourceRecherche_index');
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('RessourceRecherche_index');
        }

        return $this->render('backend/RessourceRecherche/edit.html.twig', [
            'RessourceRecherche' => $RessourceRecherche,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/Ressource/{id}", name="RessourceRecherche_delete", methods={"POST"})
     */
    public function delete(Request $request, RessourceRecherche $RessourceRecherche): Response
    {
        if ($this->isCsrfTokenValid('delete'.$RessourceRecherche->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($RessourceRecherche);
            $entityManager->flush();
        }

        return $this->redirectToRoute('RessourceRecherche_index');
    }

  
}
