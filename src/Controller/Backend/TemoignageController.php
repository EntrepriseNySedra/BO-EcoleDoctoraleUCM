<?php


namespace App\Controller\Backend;

use App\Entity\Temoignage;
use App\Form\TemoignageType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Services\BreadcrumbsService;
use App\Repository\TemoignageRepository;
use Symfony\Component\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Serializer\Annotation\Groups;

class TemoignageController extends AbstractController
{

     /**
     * @Route("/api/domaine/recherche", name="domaine_index", methods={"GET"})
     */
    public function listerWebService(TemoignageRepository $laboratoiresRepository, SerializerInterface $serializer): Response
    {
        $laboratoires = $laboratoiresRepository->findAll();
        $jsonContent = $serializer->serialize($laboratoires, 'json');
       // $laboratoires = $laboratoiresRepository->findAll();
        //$jsonContent = $serializer->serialize($laboratoires, 'json', ['groups' => ['programme_basic']]);
        return new Response($jsonContent, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }


    /**
     * @Route("/Temoignage/list", name="Temoignage_index", methods={"GET"})
     */
    public function index(): Response
    {
        $Temoignages = $this->getDoctrine()->getRepository(Temoignage::class)->findAll();

        return $this->render('backend/Temoignage/index.html.twig', [
            'Temoignages' => $Temoignages,
        ]);
    }

    /**
     * @Route("/Temoignage/new", name="Temoignage_add", methods={"GET","POST"})
     */
    public function new(Request $request, SluggerInterface $slugger): Response
    {
        $Temoignage = new Temoignage();
        $form = $this->createForm(TemoignageType::class, $Temoignage);
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
                $Temoignage->setImage($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($Temoignage);
            $entityManager->flush();

            return $this->redirectToRoute('Temoignage_index');
        }

        return $this->render('backend/Temoignage/add.html.twig', [
            'Temoignage' => $Temoignage,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/Temoignage/{id}/edit", name="Temoignage_edit", methods={"GET","POST"})
     */
     public function edit(Request $request, Temoignage $Temoignage, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(TemoignageType::class, $Temoignage);
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
                $Temoignage->setImage($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($Temoignage);
            $entityManager->flush();

            //return $this->redirectToRoute('Temoignage_index');
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('Temoignage_index');
        }

        return $this->render('backend/Temoignage/edit.html.twig', [
            'Temoignage' => $Temoignage,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/Temoignage/{id}", name="Temoignage_delete", methods={"POST"})
     */
    public function delete(Request $request, Temoignage $Temoignage): Response
    {
        if ($this->isCsrfTokenValid('delete'.$Temoignage->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($Temoignage);
            $entityManager->flush();
        }

        return $this->redirectToRoute('Temoignage_index');
    }

  
}
