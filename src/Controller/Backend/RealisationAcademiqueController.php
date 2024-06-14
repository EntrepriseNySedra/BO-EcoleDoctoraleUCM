<?php


namespace App\Controller\Backend;

use App\Entity\RealisationAcademique;
use App\Form\RealisationAcademiqueType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Services\BreadcrumbsService;
use App\Repository\RealisationAcademiqueRepository;
use Symfony\Component\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Serializer\Annotation\Groups;

class RealisationAcademiqueController extends AbstractController
{

     /**
     * @Route("/api/domaine/recherche", name="domaine_index", methods={"GET"})
     */
    public function listerWebService(RealisationAcademiqueRepository $laboratoiresRepository, SerializerInterface $serializer): Response
    {
        $laboratoires = $laboratoiresRepository->findAll();
        $jsonContent = $serializer->serialize($laboratoires, 'json');
       // $laboratoires = $laboratoiresRepository->findAll();
        //$jsonContent = $serializer->serialize($laboratoires, 'json', ['groups' => ['programme_basic']]);
        return new Response($jsonContent, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }


    /**
     * @Route("/RealisationAcademique/list", name="RealisationAcademique_index", methods={"GET"})
     */
    public function index(): Response
    {
        $RealisationAcademiques = $this->getDoctrine()->getRepository(RealisationAcademique::class)->findAll();

        return $this->render('backend/RealisationAcademique/index.html.twig', [
            'RealisationAcademiques' => $RealisationAcademiques,
        ]);
    }

    /**
     * @Route("/RealisationAcademique/new", name="RealisationAcademique_add", methods={"GET","POST"})
     */
    public function new(Request $request, SluggerInterface $slugger): Response
    {
        $RealisationAcademique = new RealisationAcademique();
        $form = $this->createForm(RealisationAcademiqueType::class, $RealisationAcademique);
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
                $RealisationAcademique->setImage($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($RealisationAcademique);
            $entityManager->flush();

            return $this->redirectToRoute('RealisationAcademique_index');
        }

        return $this->render('backend/RealisationAcademique/add.html.twig', [
            'RealisationAcademique' => $RealisationAcademique,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/RealisationAcademique/{id}/edit", name="RealisationAcademique_edit", methods={"GET","POST"})
     */
     public function edit(Request $request, RealisationAcademique $RealisationAcademique, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(RealisationAcademiqueType::class, $RealisationAcademique);
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
                $RealisationAcademique->setImage($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($RealisationAcademique);
            $entityManager->flush();

            //return $this->redirectToRoute('RealisationAcademique_index');
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('RealisationAcademique_index');
        }

        return $this->render('backend/RealisationAcademique/edit.html.twig', [
            'RealisationAcademique' => $RealisationAcademique,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/RealisationAcademique/{id}", name="RealisationAcademique_delete", methods={"POST"})
     */
    public function delete(Request $request, RealisationAcademique $RealisationAcademique): Response
    {
        if ($this->isCsrfTokenValid('delete'.$RealisationAcademique->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($RealisationAcademique);
            $entityManager->flush();
        }

        return $this->redirectToRoute('RealisationAcademique_index');
    }

  
}
