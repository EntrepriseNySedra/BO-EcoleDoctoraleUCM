<?php


namespace App\Controller\Backend;

use App\Entity\Bibliotheque;
use App\Form\BibliothequeType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Repository\BibliothequeRepository;
use App\Services\BreadcrumbsService;
use Symfony\Component\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Serializer\Annotation\Groups;

class BibliothequeController extends AbstractController
{

     /**
     * @Route("/api/Bibliotheque", name="Bibliotheques_index", methods={"GET"})
     */
    public function listerWebService(BibliothequeRepository $BibliothequesRepository, SerializerInterface $serializer): Response
    {
        $Bibliotheques = $BibliothequesRepository->findAll();
        $jsonContent = $serializer->serialize($Bibliotheques, 'json');
       // $Bibliotheques = $BibliothequesRepository->findAll();
        //$jsonContent = $serializer->serialize($Bibliotheques, 'json', ['groups' => ['programme_basic']]);
        return new Response($jsonContent, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    
    /**
     * @Route("/Bibliotheque/list", name="Bibliotheque_index", methods={"GET"})
     */
    public function index(): Response
    {
        $Bibliotheques = $this->getDoctrine()->getRepository(Bibliotheque::class)->findAll();

        return $this->render('backend/Bibliotheque/index.html.twig', [
            'Bibliotheques' => $Bibliotheques,
        ]);
    }

    /**
     * @Route("/Bibliotheque/new", name="Bibliotheque_add", methods={"GET","POST"})
     */
    public function new(Request $request, SluggerInterface $slugger): Response
    {
        $Bibliotheque = new Bibliotheque();
        $form = $this->createForm(BibliothequeType::class, $Bibliotheque);
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
                $Bibliotheque->setImage($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($Bibliotheque);
            $entityManager->flush();

            return $this->redirectToRoute('Bibliotheque_index');
        }

        return $this->render('backend/Bibliotheque/add.html.twig', [
            'Bibliotheque' => $Bibliotheque,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/biblio/{id}/edit", name="Bibliotheque_edit", methods={"GET","POST"})
     */
     public function edit(Request $request, Bibliotheque $Bibliotheque, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(BibliothequeType::class, $Bibliotheque);
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
                $Bibliotheque->setImage($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($Bibliotheque);
            $entityManager->flush();

            //return $this->redirectToRoute('Bibliotheque_index');
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('Bibliotheque_index');
        }

        return $this->render('backend/Bibliotheque/edit.html.twig', [
            'Bibliotheque' => $Bibliotheque,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/labo_delete/{id}", name="Bibliotheque_delete", methods={"POST"})
     */
    public function delete(Request $request, Bibliotheque $Bibliotheque): Response
    {
        if ($this->isCsrfTokenValid('delete'.$Bibliotheque->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($Bibliotheque);
            $entityManager->flush();
        }

        return $this->redirectToRoute('Bibliotheque_index');
    }

  
}
