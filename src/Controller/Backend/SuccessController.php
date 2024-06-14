<?php


namespace App\Controller\Backend;

use App\Entity\Success;
use App\Form\SuccessType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Services\BreadcrumbsService;
use App\Repository\SuccessRepository;
use Symfony\Component\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Serializer\Annotation\Groups;

class SuccessController extends AbstractController
{

     /**
     * @Route("/api/success", name="success_storie", methods={"GET"})
     */
    public function listerWebService(SuccessRepository $laboratoiresRepository, SerializerInterface $serializer): Response
    {
        $laboratoires = $laboratoiresRepository->findAll();
        $jsonContent = $serializer->serialize($laboratoires, 'json');
       // $laboratoires = $laboratoiresRepository->findAll();
        //$jsonContent = $serializer->serialize($laboratoires, 'json', ['groups' => ['programme_basic']]);
        return new Response($jsonContent, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }


    /**
     * @Route("/Success/list", name="Success_index", methods={"GET"})
     */
    public function index(): Response
    {
        $Successs = $this->getDoctrine()->getRepository(Success::class)->findAll();

        return $this->render('backend/Success/index.html.twig', [
            'Successs' => $Successs,
        ]);
    }

    /**
     * @Route("/Success/new", name="Success_add", methods={"GET","POST"})
     */
    public function new(Request $request, SluggerInterface $slugger): Response
    {
        $Success = new Success();
        $form = $this->createForm(SuccessType::class, $Success);
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
                $Success->setImage($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($Success);
            $entityManager->flush();

            return $this->redirectToRoute('Success_index');
        }

        return $this->render('backend/Success/add.html.twig', [
            'Success' => $Success,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/Success/{id}/edit", name="Success_edit", methods={"GET","POST"})
     */
     public function edit(Request $request, Success $Success, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(SuccessType::class, $Success);
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
                $Success->setImage($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($Success);
            $entityManager->flush();

            //return $this->redirectToRoute('Success_index');
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('Success_index');
        }

        return $this->render('backend/Success/edit.html.twig', [
            'Success' => $Success,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/Success/{id}", name="Success_delete", methods={"POST"})
     */
    public function delete(Request $request, Success $Success): Response
    {
        if ($this->isCsrfTokenValid('delete'.$Success->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($Success);
            $entityManager->flush();
        }

        return $this->redirectToRoute('Success_index');
    }

  
}
