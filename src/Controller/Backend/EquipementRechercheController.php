<?php


namespace App\Controller\Backend;

use App\Entity\EquipementRecherche;
use App\Form\EquipementRechercheType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Services\BreadcrumbsService;
use App\Repository\EquipementRechercheRepository;
use Symfony\Component\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Serializer\Annotation\Groups;

class EquipementRechercheController extends AbstractController
{

     /**
     * @Route("/api/equipement/recherche", name="equipement_index", methods={"GET"})
     */
    public function listerWebService(EquipementRechercheRepository $laboratoiresRepository, SerializerInterface $serializer): Response
    {
        $laboratoires = $laboratoiresRepository->findAll();
        $jsonContent = $serializer->serialize($laboratoires, 'json');
       // $laboratoires = $laboratoiresRepository->findAll();
        //$jsonContent = $serializer->serialize($laboratoires, 'json', ['groups' => ['programme_basic']]);
        return new Response($jsonContent, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }


    /**
     * @Route("/EquipementRecherche/list", name="EquipementRecherche_index", methods={"GET"})
     */
    public function index(): Response
    {
        $EquipementRecherches = $this->getDoctrine()->getRepository(EquipementRecherche::class)->findAll();

        return $this->render('backend/EquipementRecherche/index.html.twig', [
            'EquipementRecherches' => $EquipementRecherches,
        ]);
    }

    /**
     * @Route("/EquipementRecherche/new", name="admin_equipement_add", methods={"GET","POST"})
     */
    public function new(Request $request, SluggerInterface $slugger): Response
    {
        $EquipementRecherche = new EquipementRecherche();
        $form = $this->createForm(EquipementRechercheType::class, $EquipementRecherche);
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
                $EquipementRecherche->setImage($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($EquipementRecherche);
            $entityManager->flush();

            return $this->redirectToRoute('EquipementRecherche_index');
        }

        return $this->render('backend/EquipementRecherche/add.html.twig', [
            'EquipementRecherche' => $EquipementRecherche,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/equipement/{id}/edit", name="EquipementRecherche_edit", methods={"GET","POST"})
     */
     public function edit(Request $request, EquipementRecherche $EquipementRecherche, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(EquipementRechercheType::class, $EquipementRecherche);
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
                $EquipementRecherche->setImage($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($EquipementRecherche);
            $entityManager->flush();

            //return $this->redirectToRoute('EquipementRecherche_index');
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('EquipementRecherche_index');
        }

        return $this->render('backend/EquipementRecherche/edit.html.twig', [
            'EquipementRecherche' => $EquipementRecherche,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/equipement/{id}", name="EquipementRecherche_delete", methods={"POST"})
     */
    public function delete(Request $request, EquipementRecherche $EquipementRecherche): Response
    {
        if ($this->isCsrfTokenValid('delete'.$EquipementRecherche->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($EquipementRecherche);
            $entityManager->flush();
        }

        return $this->redirectToRoute('EquipementRecherche_index');
    }

  
}
