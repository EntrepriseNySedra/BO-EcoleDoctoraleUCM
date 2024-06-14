<?php


namespace App\Controller\Backend;

use App\Entity\OpportuniteBourse;
use App\Form\OpportuniteBourseType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Services\BreadcrumbsService;
use App\Repository\OpportuniteBourseRepository;
use Symfony\Component\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Serializer\Annotation\Groups;

class OpportuniteBourseController extends AbstractController
{

     /**
     * @Route("/api/opportuniteBourse", name="opportunite_api", methods={"GET"})
     */
    public function listerWebService(OpportuniteBourseRepository $laboratoiresRepository, SerializerInterface $serializer): Response
    {
        $laboratoires = $laboratoiresRepository->findAll();
        $jsonContent = $serializer->serialize($laboratoires, 'json');
       // $laboratoires = $laboratoiresRepository->findAll();
        //$jsonContent = $serializer->serialize($laboratoires, 'json', ['groups' => ['programme_basic']]);
        return new Response($jsonContent, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }


    /**
     * @Route("/OpportuniteBourse/list", name="OpportuniteBourse_index", methods={"GET"})
     */
    public function index(): Response
    {
        $OpportuniteBourses = $this->getDoctrine()->getRepository(OpportuniteBourse::class)->findAll();

        return $this->render('backend/OpportuniteBourse/index.html.twig', [
            'OpportuniteBourses' => $OpportuniteBourses,
        ]);
    }

    /**
     * @Route("/OpportuniteBourse/new", name="OpportuniteBourse_add", methods={"GET","POST"})
     */
    public function new(Request $request, SluggerInterface $slugger): Response
    {
        $OpportuniteBourse = new OpportuniteBourse();
        $form = $this->createForm(OpportuniteBourseType::class, $OpportuniteBourse);
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
                $OpportuniteBourse->setImage($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($OpportuniteBourse);
            $entityManager->flush();

            return $this->redirectToRoute('OpportuniteBourse_index');
        }

        return $this->render('backend/OpportuniteBourse/add.html.twig', [
            'OpportuniteBourse' => $OpportuniteBourse,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/OpportuniteBourse/{id}/edit", name="OpportuniteBourse_edit", methods={"GET","POST"})
     */
     public function edit(Request $request, OpportuniteBourse $OpportuniteBourse, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(OpportuniteBourseType::class, $OpportuniteBourse);
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
                $OpportuniteBourse->setImage($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($OpportuniteBourse);
            $entityManager->flush();

            //return $this->redirectToRoute('OpportuniteBourse_index');
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('OpportuniteBourse_index');
        }

        return $this->render('backend/OpportuniteBourse/edit.html.twig', [
            'OpportuniteBourse' => $OpportuniteBourse,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/OpportuniteBourse/{id}", name="OpportuniteBourse_delete", methods={"POST"})
     */
    public function delete(Request $request, OpportuniteBourse $OpportuniteBourse): Response
    {
        if ($this->isCsrfTokenValid('delete'.$OpportuniteBourse->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($OpportuniteBourse);
            $entityManager->flush();
        }

        return $this->redirectToRoute('OpportuniteBourse_index');
    }

  
}
