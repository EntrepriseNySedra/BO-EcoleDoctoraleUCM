<?php


namespace App\Controller\Backend;

use App\Entity\BourseDistinction;
use App\Form\BourseDistinctionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Services\BreadcrumbsService;
use App\Repository\BourseDistinctionRepository;
use Symfony\Component\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Serializer\Annotation\Groups;

class BourseDistinctionController extends AbstractController
{

     /**
     * @Route("/api/Ressource/BourseDistinction", name="Ressource_index", methods={"GET"})
     */
    public function listerWebService(BourseDistinctionRepository $laboratoiresRepository, SerializerInterface $serializer): Response
    {
        $laboratoires = $laboratoiresRepository->findAll();
        $jsonContent = $serializer->serialize($laboratoires, 'json');
       // $laboratoires = $laboratoiresRepository->findAll();
        //$jsonContent = $serializer->serialize($laboratoires, 'json', ['groups' => ['programme_basic']]);
        return new Response($jsonContent, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }


    /**
     * @Route("/BourseDistinction/list", name="BourseDistinction_index", methods={"GET"})
     */
    public function index(): Response
    {
        $BourseDistinctions = $this->getDoctrine()->getRepository(BourseDistinction::class)->findAll();

        return $this->render('backend/BourseDistinction/index.html.twig', [
            'BourseDistinctions' => $BourseDistinctions,
        ]);
    }

    /**
     * @Route("/BourseDistinction/new", name="BourseDistinction_add", methods={"GET","POST"})
     */
    public function new(Request $request, SluggerInterface $slugger): Response
    {
        $BourseDistinction = new BourseDistinction();
        $form = $this->createForm(BourseDistinctionType::class, $BourseDistinction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           
            

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($BourseDistinction);
            $entityManager->flush();

            return $this->redirectToRoute('BourseDistinction_index');
        }

        return $this->render('backend/BourseDistinction/add.html.twig', [
            'BourseDistinction' => $BourseDistinction,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/BourseDistinction/{id}/edit", name="BourseDistinction_edit", methods={"GET","POST"})
     */
     public function edit(Request $request, BourseDistinction $BourseDistinction, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(BourseDistinctionType::class, $BourseDistinction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


          

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($BourseDistinction);
            $entityManager->flush();

            //return $this->redirectToRoute('BourseDistinction_index');
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('BourseDistinction_index');
        }

        return $this->render('backend/BourseDistinction/edit.html.twig', [
            'BourseDistinction' => $BourseDistinction,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/BourseDistinction/{id}", name="BourseDistinction_delete", methods={"POST"})
     */
    public function delete(Request $request, BourseDistinction $BourseDistinction): Response
    {
        if ($this->isCsrfTokenValid('delete'.$BourseDistinction->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($BourseDistinction);
            $entityManager->flush();
        }

        return $this->redirectToRoute('BourseDistinction_index');
    }

  
}
