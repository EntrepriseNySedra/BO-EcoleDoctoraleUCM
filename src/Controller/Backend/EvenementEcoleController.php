<?php


namespace App\Controller\Backend;

use App\Entity\EvenementEcole;
use App\Form\EvenementEcoleType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Services\BreadcrumbsService;
use App\Repository\EvenementEcoleRepository;
use Symfony\Component\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Serializer\Annotation\Groups;

class EvenementEcoleController extends AbstractController
{

     /**
     * @Route("/api/evenementEcole", name="event_api", methods={"GET"})
     */
    public function listerWebService(EvenementEcoleRepository $laboratoiresRepository, SerializerInterface $serializer): Response
    {
        $laboratoires = $laboratoiresRepository->findAll();
        $jsonContent = $serializer->serialize($laboratoires, 'json');
       // $laboratoires = $laboratoiresRepository->findAll();
        //$jsonContent = $serializer->serialize($laboratoires, 'json', ['groups' => ['programme_basic']]);
        return new Response($jsonContent, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }


    /**
     * @Route("/EvenementEcole/list", name="EvenementEcole_index", methods={"GET"})
     */
    public function index(): Response
    {
        $EvenementEcoles = $this->getDoctrine()->getRepository(EvenementEcole::class)->findAll();

        return $this->render('backend/EvenementEcole/index.html.twig', [
            'EvenementEcoles' => $EvenementEcoles,
        ]);
    }

    /**
     * @Route("/EvenementEcole/new", name="EvenementEcole_add", methods={"GET","POST"})
     */
    public function new(Request $request, SluggerInterface $slugger): Response
    {
        $EvenementEcole = new EvenementEcole();
        $form = $this->createForm(EvenementEcoleType::class, $EvenementEcole);
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
                $EvenementEcole->setImage($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($EvenementEcole);
            $entityManager->flush();

            return $this->redirectToRoute('EvenementEcole_index');
        }

        return $this->render('backend/EvenementEcole/add.html.twig', [
            'EvenementEcole' => $EvenementEcole,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/EvenementEcole/{id}/edit", name="EvenementEcole_edit", methods={"GET","POST"})
     */
     public function edit(Request $request, EvenementEcole $EvenementEcole, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(EvenementEcoleType::class, $EvenementEcole);
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
                $EvenementEcole->setImage($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($EvenementEcole);
            $entityManager->flush();

            //return $this->redirectToRoute('EvenementEcole_index');
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('EvenementEcole_index');
        }

        return $this->render('backend/EvenementEcole/edit.html.twig', [
            'EvenementEcole' => $EvenementEcole,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/EvenementEcole/{id}", name="EvenementEcole_delete", methods={"POST"})
     */
    public function delete(Request $request, EvenementEcole $EvenementEcole): Response
    {
        if ($this->isCsrfTokenValid('delete'.$EvenementEcole->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($EvenementEcole);
            $entityManager->flush();
        }

        return $this->redirectToRoute('EvenementEcole_index');
    }

  
}
