<?php


namespace App\Controller\Backend;

use App\Entity\SubventionRecherche;
use App\Form\SubventionRechercheType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Services\BreadcrumbsService;
use App\Repository\SubventionRechercheRepository;
use Symfony\Component\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Serializer\Annotation\Groups;

class SubventionRechercheController extends AbstractController
{

     /**
     * @Route("/api/subvention", name="subvention_index", methods={"GET"})
     */
    public function listerWebService(SubventionRechercheRepository $laboratoiresRepository, SerializerInterface $serializer): Response
    {
        $laboratoires = $laboratoiresRepository->findAll();
        $jsonContent = $serializer->serialize($laboratoires, 'json');
       // $laboratoires = $laboratoiresRepository->findAll();
        //$jsonContent = $serializer->serialize($laboratoires, 'json', ['groups' => ['programme_basic']]);
        return new Response($jsonContent, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }


    /**
     * @Route("/Subvention/list", name="SubventionRecherche_index", methods={"GET"})
     */
    public function index(): Response
    {
        $SubventionRecherches = $this->getDoctrine()->getRepository(SubventionRecherche::class)->findAll();

        return $this->render('backend/SubventionRecherche/index.html.twig', [
            'SubventionRecherches' => $SubventionRecherches,
        ]);
    }

    /**
     * @Route("/SubventionRecherche/new", name="SubventionRecherche_add", methods={"GET","POST"})
     */
    public function new(Request $request, SluggerInterface $slugger): Response
    {
        $SubventionRecherche = new SubventionRecherche();
        $form = $this->createForm(SubventionRechercheType::class, $SubventionRecherche);
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
                $SubventionRecherche->setImage($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($SubventionRecherche);
            $entityManager->flush();

            return $this->redirectToRoute('SubventionRecherche_index');
        }

        return $this->render('backend/SubventionRecherche/add.html.twig', [
            'SubventionRecherche' => $SubventionRecherche,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/Subvention/{id}/edit", name="SubventionRecherche_edit", methods={"GET","POST"})
     */
     public function edit(Request $request, SubventionRecherche $SubventionRecherche, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(SubventionRechercheType::class, $SubventionRecherche);
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
                $SubventionRecherche->setImage($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($SubventionRecherche);
            $entityManager->flush();

            //return $this->redirectToRoute('SubventionRecherche_index');
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('SubventionRecherche_index');
        }

        return $this->render('backend/SubventionRecherche/edit.html.twig', [
            'SubventionRecherche' => $SubventionRecherche,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/Subvention/{id}", name="SubventionRecherche_delete", methods={"POST"})
     */
    public function delete(Request $request, SubventionRecherche $SubventionRecherche): Response
    {
        if ($this->isCsrfTokenValid('delete'.$SubventionRecherche->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($SubventionRecherche);
            $entityManager->flush();
        }

        return $this->redirectToRoute('SubventionRecherche_index');
    }

  
}
