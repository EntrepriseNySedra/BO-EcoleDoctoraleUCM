<?php


namespace App\Controller\Backend;

use App\Entity\Partenariat;
use App\Form\PartenariatType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Repository\PartenariatRepository;
use App\Services\BreadcrumbsService;
use Symfony\Component\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Serializer\Annotation\Groups;

class PartenariatController extends AbstractController
{

     /**
     * @Route("/api/Partenariat", name="Partenariats_index", methods={"GET"})
     */
    public function listerWebService(PartenariatRepository $PartenariatsRepository, SerializerInterface $serializer): Response
    {
        $Partenariats = $PartenariatsRepository->findAll();
        $jsonContent = $serializer->serialize($Partenariats, 'json');
       // $Partenariats = $PartenariatsRepository->findAll();
        //$jsonContent = $serializer->serialize($Partenariats, 'json', ['groups' => ['programme_basic']]);
        return new Response($jsonContent, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    
    /**
     * @Route("/Partenariat/list", name="Partenariat_index", methods={"GET"})
     */
    public function index(): Response
    {
        $Partenariats = $this->getDoctrine()->getRepository(Partenariat::class)->findAll();

        return $this->render('backend/Partenariat/index.html.twig', [
            'Partenariats' => $Partenariats,
        ]);
    }

    /**
     * @Route("/Partenariat/new", name="Partenariat_add", methods={"GET","POST"})
     */
    public function new(Request $request, SluggerInterface $slugger): Response
    {
        $Partenariat = new Partenariat();
        $form = $this->createForm(PartenariatType::class, $Partenariat);
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
                $Partenariat->setImage($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($Partenariat);
            $entityManager->flush();

            return $this->redirectToRoute('Partenariat_index');
        }

        return $this->render('backend/Partenariat/add.html.twig', [
            'Partenariat' => $Partenariat,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/partenariat/{id}/edit", name="Partenariat_edit", methods={"GET","POST"})
     */
     public function edit(Request $request, Partenariat $Partenariat, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(PartenariatType::class, $Partenariat);
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
                $Partenariat->setImage($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($Partenariat);
            $entityManager->flush();

            //return $this->redirectToRoute('Partenariat_index');
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('Partenariat_index');
        }

        return $this->render('backend/Partenariat/edit.html.twig', [
            'Partenariat' => $Partenariat,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/partenariat_delete/{id}", name="Partenariat_delete", methods={"POST"})
     */
    public function delete(Request $request, Partenariat $Partenariat): Response
    {
        if ($this->isCsrfTokenValid('delete'.$Partenariat->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($Partenariat);
            $entityManager->flush();
        }

        return $this->redirectToRoute('Partenariat_index');
    }

  
}
