<?php


namespace App\Controller\Backend;

use App\Entity\ProjetRecherche;
use App\Form\ProjetRechercheType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Services\BreadcrumbsService;
use App\Repository\ProjetRechercheRepository;
use Symfony\Component\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Serializer\Annotation\Groups;

class ProjetRechercheController extends AbstractController
{

     /**
     * @Route("/api/Ressource/recherche", name="Ressource_index", methods={"GET"})
     */
    public function listerWebService(ProjetRechercheRepository $laboratoiresRepository, SerializerInterface $serializer): Response
    {
        $laboratoires = $laboratoiresRepository->findAll();
        $jsonContent = $serializer->serialize($laboratoires, 'json');
       // $laboratoires = $laboratoiresRepository->findAll();
        //$jsonContent = $serializer->serialize($laboratoires, 'json', ['groups' => ['programme_basic']]);
        return new Response($jsonContent, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }


    /**
     * @Route("/ProjetdeRecherche/list", name="ProjetRecherche_index", methods={"GET"})
     */
    public function index(): Response
    {
        $ProjetRecherches = $this->getDoctrine()->getRepository(ProjetRecherche::class)->findAll();

        return $this->render('backend/ProjetRecherche/index.html.twig', [
            'ProjetRecherches' => $ProjetRecherches,
        ]);
    }

    /**
     * @Route("/ProjetRecherche/new", name="ProjetRecherche_add", methods={"GET","POST"})
     */
    public function new(Request $request, SluggerInterface $slugger): Response
    {
        $ProjetRecherche = new ProjetRecherche();
        $form = $this->createForm(ProjetRechercheType::class, $ProjetRecherche);
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
                $ProjetRecherche->setImage($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($ProjetRecherche);
            $entityManager->flush();

            return $this->redirectToRoute('ProjetRecherche_index');
        }

        return $this->render('backend/ProjetRecherche/add.html.twig', [
            'ProjetRecherche' => $ProjetRecherche,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/Ressource/{id}/edit", name="ProjetRecherche_edit", methods={"GET","POST"})
     */
     public function edit(Request $request, ProjetRecherche $ProjetRecherche, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ProjetRechercheType::class, $ProjetRecherche);
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
                $ProjetRecherche->setImage($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($ProjetRecherche);
            $entityManager->flush();

            //return $this->redirectToRoute('ProjetRecherche_index');
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('ProjetRecherche_index');
        }

        return $this->render('backend/ProjetRecherche/edit.html.twig', [
            'ProjetRecherche' => $ProjetRecherche,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/Ressource/{id}", name="ProjetRecherche_delete", methods={"POST"})
     */
    public function delete(Request $request, ProjetRecherche $ProjetRecherche): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ProjetRecherche->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($ProjetRecherche);
            $entityManager->flush();
        }

        return $this->redirectToRoute('ProjetRecherche_index');
    }

  
}
