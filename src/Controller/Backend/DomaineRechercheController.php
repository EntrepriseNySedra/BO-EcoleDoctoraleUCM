<?php


namespace App\Controller\Backend;

use App\Entity\DomaineRecherche;
use App\Form\DomaineRechercheType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Services\BreadcrumbsService;
use App\Repository\DomaineRechercheRepository;
use Symfony\Component\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Serializer\Annotation\Groups;

class DomaineRechercheController extends AbstractController
{

     /**
     * @Route("/api/Ressource/recherche", name="Ressource_index", methods={"GET"})
     */
    public function listerWebService(DomaineRechercheRepository $laboratoiresRepository, SerializerInterface $serializer): Response
    {
        $laboratoires = $laboratoiresRepository->findAll();
        $jsonContent = $serializer->serialize($laboratoires, 'json');
       // $laboratoires = $laboratoiresRepository->findAll();
        //$jsonContent = $serializer->serialize($laboratoires, 'json', ['groups' => ['programme_basic']]);
        return new Response($jsonContent, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }


    /**
     * @Route("/DomaineRecherche/list", name="DomaineRecherche_index", methods={"GET"})
     */
    public function index(): Response
    {
        $DomaineRecherches = $this->getDoctrine()->getRepository(DomaineRecherche::class)->findAll();

        return $this->render('backend/DomaineRecherche/index.html.twig', [
            'DomaineRecherches' => $DomaineRecherches,
        ]);
    }

    /**
     * @Route("/DomaineRecherche/new", name="domaine_add", methods={"GET","POST"})
     */
    public function new(Request $request, SluggerInterface $slugger): Response
    {
        $DomaineRecherche = new DomaineRecherche();
        $form = $this->createForm(DomaineRechercheType::class, $DomaineRecherche);
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
                $DomaineRecherche->setImage($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($DomaineRecherche);
            $entityManager->flush();

            return $this->redirectToRoute('DomaineRecherche_index');
        }

        return $this->render('backend/DomaineRecherche/add.html.twig', [
            'DomaineRecherche' => $DomaineRecherche,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/domaineRecherche/{id}/edit", name="DomaineRecherche_edit", methods={"GET","POST"})
     */
     public function edit(Request $request, DomaineRecherche $DomaineRecherche, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(DomaineRechercheType::class, $DomaineRecherche);
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
                $DomaineRecherche->setImage($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($DomaineRecherche);
            $entityManager->flush();

            //return $this->redirectToRoute('DomaineRecherche_index');
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('DomaineRecherche_index');
        }

        return $this->render('backend/DomaineRecherche/edit.html.twig', [
            'DomaineRecherche' => $DomaineRecherche,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/DomaineRecherche/{id}", name="DomaineRecherche_delete", methods={"POST"})
     */
    public function delete(Request $request, DomaineRecherche $DomaineRecherche): Response
    {
        if ($this->isCsrfTokenValid('delete'.$DomaineRecherche->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($DomaineRecherche);
            $entityManager->flush();
        }

        return $this->redirectToRoute('DomaineRecherche_index');
    }

  
}
