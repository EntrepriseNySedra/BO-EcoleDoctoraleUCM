<?php


namespace App\Controller\Backend;

use App\Entity\Laboratoire;
use App\Form\LaboratoireType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Repository\LaboratoireRepository;
use App\Services\BreadcrumbsService;
use Symfony\Component\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Serializer\Annotation\Groups;

class LaboratoireController extends AbstractController
{

     /**
     * @Route("/api/laboratoire", name="laboratoires_index", methods={"GET"})
     */
    public function listerWebService(LaboratoireRepository $laboratoiresRepository, SerializerInterface $serializer): Response
    {
        $laboratoires = $laboratoiresRepository->findAll();
        $jsonContent = $serializer->serialize($laboratoires, 'json');
       // $laboratoires = $laboratoiresRepository->findAll();
        //$jsonContent = $serializer->serialize($laboratoires, 'json', ['groups' => ['programme_basic']]);
        return new Response($jsonContent, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    
    /**
     * @Route("/laboratoire/list", name="laboratoire_index", methods={"GET"})
     */
    public function index(): Response
    {
        $laboratoires = $this->getDoctrine()->getRepository(Laboratoire::class)->findAll();

        return $this->render('backend/laboratoire/index.html.twig', [
            'laboratoires' => $laboratoires,
        ]);
    }

    /**
     * @Route("/laboratoire/new", name="admin_labo_add", methods={"GET","POST"})
     */
    public function new(Request $request, SluggerInterface $slugger): Response
    {
        $laboratoire = new Laboratoire();
        $form = $this->createForm(LaboratoireType::class, $laboratoire);
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
                $laboratoire->setImage($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($laboratoire);
            $entityManager->flush();

            return $this->redirectToRoute('laboratoire_index');
        }

        return $this->render('backend/laboratoire/add.html.twig', [
            'laboratoire' => $laboratoire,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="laboratoire_edit", methods={"GET","POST"})
     */
     public function edit(Request $request, Laboratoire $laboratoire, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(LaboratoireType::class, $laboratoire);
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
                $laboratoire->setImage($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($laboratoire);
            $entityManager->flush();

            //return $this->redirectToRoute('laboratoire_index');
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('laboratoire_index');
        }

        return $this->render('backend/laboratoire/edit.html.twig', [
            'laboratoire' => $laboratoire,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/labo_delete/{id}", name="laboratoire_delete", methods={"POST"})
     */
    public function delete(Request $request, Laboratoire $laboratoire): Response
    {
        if ($this->isCsrfTokenValid('delete'.$laboratoire->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($laboratoire);
            $entityManager->flush();
        }

        return $this->redirectToRoute('laboratoire_index');
    }

  
}
