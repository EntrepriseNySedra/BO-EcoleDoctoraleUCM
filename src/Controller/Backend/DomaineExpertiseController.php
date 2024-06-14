<?php


namespace App\Controller\Backend;

use App\Entity\DomaineExpertise;
use App\Form\DomaineExpertiseType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Services\BreadcrumbsService;
use App\Repository\DomaineExpertiseRepository;
use Symfony\Component\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Serializer\Annotation\Groups;

class DomaineExpertiseController extends AbstractController
{

     /**
     * @Route("/api/domaine/recherche", name="domaine_index", methods={"GET"})
     */
    public function listerWebService(DomaineExpertiseRepository $laboratoiresRepository, SerializerInterface $serializer): Response
    {
        $laboratoires = $laboratoiresRepository->findAll();
        $jsonContent = $serializer->serialize($laboratoires, 'json');
       // $laboratoires = $laboratoiresRepository->findAll();
        //$jsonContent = $serializer->serialize($laboratoires, 'json', ['groups' => ['programme_basic']]);
        return new Response($jsonContent, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }


    /**
     * @Route("/DomaineExpertise/list", name="DomaineExpertise_index", methods={"GET"})
     */
    public function index(): Response
    {
        $DomaineExpertises = $this->getDoctrine()->getRepository(DomaineExpertise::class)->findAll();

        return $this->render('backend/DomaineExpertise/index.html.twig', [
            'DomaineExpertises' => $DomaineExpertises,
        ]);
    }

    /**
     * @Route("/DomaineExpertise/new", name="domaineexpertise_add", methods={"GET","POST"})
     */
    public function new(Request $request, SluggerInterface $slugger): Response
    {
        $DomaineExpertise = new DomaineExpertise();
        $form = $this->createForm(DomaineExpertiseType::class, $DomaineExpertise);
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
                $DomaineExpertise->setImage($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($DomaineExpertise);
            $entityManager->flush();

            return $this->redirectToRoute('DomaineExpertise_index');
        }

        return $this->render('backend/DomaineExpertise/add.html.twig', [
            'DomaineExpertise' => $DomaineExpertise,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/Ressource/{id}/edit", name="DomaineExpertise_edit", methods={"GET","POST"})
     */
     public function edit(Request $request, DomaineExpertise $DomaineExpertise, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(DomaineExpertiseType::class, $DomaineExpertise);
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
                $DomaineExpertise->setImage($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($DomaineExpertise);
            $entityManager->flush();

            //return $this->redirectToRoute('DomaineExpertise_index');
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('DomaineExpertise_index');
        }

        return $this->render('backend/DomaineExpertise/edit.html.twig', [
            'DomaineExpertise' => $DomaineExpertise,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/Ressource/{id}", name="DomaineExpertise_delete", methods={"POST"})
     */
    public function delete(Request $request, DomaineExpertise $DomaineExpertise): Response
    {
        if ($this->isCsrfTokenValid('delete'.$DomaineExpertise->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($DomaineExpertise);
            $entityManager->flush();
        }

        return $this->redirectToRoute('DomaineExpertise_index');
    }

  
}
