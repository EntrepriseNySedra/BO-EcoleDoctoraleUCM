<?php


namespace App\Controller\Backend;

use App\Entity\Professorat;
use App\Form\ProfessoratType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Services\BreadcrumbsService;
use App\Repository\ProfessoratRepository;
use Symfony\Component\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Serializer\Annotation\Groups;

class ProfessoratController extends AbstractController
{

     /**
     * @Route("/api/professorat", name="professorat_index", methods={"GET"})
     */
    public function listerWebService(ProfessoratRepository $laboratoiresRepository, SerializerInterface $serializer): Response
    {
        $laboratoires = $laboratoiresRepository->findAll();
        $jsonContent = $serializer->serialize($laboratoires, 'json');
       // $laboratoires = $laboratoiresRepository->findAll();
        //$jsonContent = $serializer->serialize($laboratoires, 'json', ['groups' => ['programme_basic']]);
        return new Response($jsonContent, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }


    /**
     * @Route("/professorat/list", name="Professorat_index", methods={"GET"})
     */
    public function index(): Response
    {
        $Professorats = $this->getDoctrine()->getRepository(Professorat::class)->findAll();

        return $this->render('backend/Professorat/index.html.twig', [
            'Professorats' => $Professorats,
        ]);
    }

    /**
     * @Route("/Professorat/new", name="Professorat_add", methods={"GET","POST"})
     */
    public function new(Request $request, SluggerInterface $slugger): Response
    {
        $Professorat = new Professorat();
        $form = $this->createForm(ProfessoratType::class, $Professorat);
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
                $Professorat->setImage($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($Professorat);
            $entityManager->flush();

            return $this->redirectToRoute('Professorat_index');
        }

        return $this->render('backend/Professorat/add.html.twig', [
            'Professorat' => $Professorat,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/professorat/{id}/edit", name="Professorat_edit", methods={"GET","POST"})
     */
     public function edit(Request $request, Professorat $Professorat, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ProfessoratType::class, $Professorat);
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
                $Professorat->setImage($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($Professorat);
            $entityManager->flush();

            //return $this->redirectToRoute('Professorat_index');
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('Professorat_index');
        }

        return $this->render('backend/Professorat/edit.html.twig', [
            'Professorat' => $Professorat,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/professorat/{id}", name="Professorat_delete", methods={"POST"})
     */
    public function delete(Request $request, Professorat $Professorat): Response
    {
        if ($this->isCsrfTokenValid('delete'.$Professorat->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($Professorat);
            $entityManager->flush();
        }

        return $this->redirectToRoute('Professorat_index');
    }

  
}
