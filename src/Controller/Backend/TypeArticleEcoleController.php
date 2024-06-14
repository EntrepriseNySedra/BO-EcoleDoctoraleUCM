<?php


namespace App\Controller\Backend;

use App\Entity\TypeArticleEcole;
use App\Form\TypeArticleEcoleType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Services\BreadcrumbsService;
use App\Repository\TypeArticleEcoleRepository;
use Symfony\Component\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Serializer\Annotation\Groups;

class TypeArticleEcoleController extends AbstractController
{

     /**
     * @Route("/api/domaine/recherche", name="domaine_index", methods={"GET"})
     */
    public function listerWebService(TypeArticleEcoleRepository $laboratoiresRepository, SerializerInterface $serializer): Response
    {
        $laboratoires = $laboratoiresRepository->findAll();
        $jsonContent = $serializer->serialize($laboratoires, 'json');
       // $laboratoires = $laboratoiresRepository->findAll();
        //$jsonContent = $serializer->serialize($laboratoires, 'json', ['groups' => ['programme_basic']]);
        return new Response($jsonContent, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }


    /**
     * @Route("/TypeArticleEcole/list", name="TypeArticleEcole_index", methods={"GET"})
     */
    public function index(): Response
    {
        $TypeArticleEcoles = $this->getDoctrine()->getRepository(TypeArticleEcole::class)->findAll();

        return $this->render('backend/TypeArticleEcole/index.html.twig', [
            'TypeArticleEcoles' => $TypeArticleEcoles,
        ]);
    }

    /**
     * @Route("/TypeArticleEcole/new", name="TypeArticleEcole_add", methods={"GET","POST"})
     */
    public function new(Request $request, SluggerInterface $slugger): Response
    {
        $TypeArticleEcole = new TypeArticleEcole();
        $form = $this->createForm(TypeArticleEcoleType::class, $TypeArticleEcole);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($TypeArticleEcole);
            $entityManager->flush();

            return $this->redirectToRoute('TypeArticleEcole_index');
        }

        return $this->render('backend/TypeArticleEcole/add.html.twig', [
            'TypeArticleEcole' => $TypeArticleEcole,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/TypeArticleEcole/{id}/edit", name="TypeArticleEcole_edit", methods={"GET","POST"})
     */
     public function edit(Request $request, TypeArticleEcole $TypeArticleEcole, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(TypeArticleEcoleType::class, $TypeArticleEcole);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($TypeArticleEcole);
            $entityManager->flush();

            //return $this->redirectToRoute('TypeArticleEcole_index');
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('TypeArticleEcole_index');
        }

        return $this->render('backend/TypeArticleEcole/edit.html.twig', [
            'TypeArticleEcole' => $TypeArticleEcole,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/TypeArticleEcole/{id}", name="TypeArticleEcole_delete", methods={"POST"})
     */
    public function delete(Request $request, TypeArticleEcole $TypeArticleEcole): Response
    {
        if ($this->isCsrfTokenValid('delete'.$TypeArticleEcole->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($TypeArticleEcole);
            $entityManager->flush();
        }

        return $this->redirectToRoute('TypeArticleEcole_index');
    }

  
}
