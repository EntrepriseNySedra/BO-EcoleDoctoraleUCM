<?php


namespace App\Controller\Backend;

use App\Entity\ArticleEcole;
use App\Form\ArticleEcoleType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Services\BreadcrumbsService;
use App\Repository\ArticleEcoleRepository;
use Symfony\Component\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Serializer\Annotation\Groups;

class ArticleEcoleController extends AbstractController
{

     /**
     * @Route("/api/pagearticle", name="webservice_articles", methods={"GET"})
     */
    public function listerWebServiceArticle(ArticleEcoleRepository $laboratoiresRepository, SerializerInterface $serializer): Response
    {
        $laboratoires = $laboratoiresRepository->findAll();
        $jsonContent = $serializer->serialize($laboratoires, 'json');
       // $laboratoires = $laboratoiresRepository->findAll();
        //$jsonContent = $serializer->serialize($laboratoires, 'json', ['groups' => ['programme_basic']]);
        return new Response($jsonContent, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * @Route("/ArticleEcole/list", name="ArticleEcole_index", methods={"GET"})
     */
    public function index(): Response
    {
        $ArticleEcoles = $this->getDoctrine()->getRepository(ArticleEcole::class)->findAll();

        return $this->render('backend/ArticleEcole/list.html.twig', [
            'ArticleEcoles' => $ArticleEcoles,
        ]);
    }

    /**
     * @Route("/ArticleEcole/new", name="ArticleEcole_add", methods={"GET","POST"})
     */
    public function new(Request $request, SluggerInterface $slugger): Response
    {
        $ArticleEcole = new ArticleEcole();
        $form = $this->createForm(ArticleEcoleType::class, $ArticleEcole);
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
                $ArticleEcole->setImage($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($ArticleEcole);
            $entityManager->flush();

            return $this->redirectToRoute('ArticleEcole_index');
        }

        return $this->render('backend/ArticleEcole/add.html.twig', [
            'ArticleEcole' => $ArticleEcole,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/ArticleEcole/{id}/edit", name="ArticleEcole_edit", methods={"GET","POST"})
     */
     public function edit(Request $request, ArticleEcole $ArticleEcole, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ArticleEcoleType::class, $ArticleEcole);
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
                $ArticleEcole->setImage($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($ArticleEcole);
            $entityManager->flush();

            //return $this->redirectToRoute('ArticleEcole_index');
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('ArticleEcole_index');
        }

        return $this->render('backend/ArticleEcole/edit.html.twig', [
            'ArticleEcole' => $ArticleEcole,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/ArticleEcole/{id}", name="ArticleEcole_delete", methods={"POST"})
     */
    public function delete(Request $request, ArticleEcole $ArticleEcole): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ArticleEcole->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($ArticleEcole);
            $entityManager->flush();
        }

        return $this->redirectToRoute('ArticleEcole_index');
    }

  
}
