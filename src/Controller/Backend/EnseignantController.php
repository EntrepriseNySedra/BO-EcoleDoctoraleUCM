<?php

namespace App\Controller\Backend;

use App\Entity\Enseignant;
use App\Form\EnseignantType;
use App\Manager\EnseignantManager;
use App\Manager\EnseignantMentionManager;
use App\Manager\MentionManager;
use App\Manager\NiveauManager;
use App\Services\BreadcrumbsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @package App\Controller\Backend
 * @Route("/admin/enseignant")
 */
class EnseignantController extends AbstractController
{
    /**
     * @Route("/", name="admin_enseignant_list", methods={"GET"})
     * @param \App\Manager\EnseignantManager $manager
     * @param \App\Services\BreadcrumbsService $breadcrumbsService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function list(BreadcrumbsService $breadcrumbsService, EnseignantManager $manager) : Response
    {

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Enseignant', $this->generateUrl('admin_enseignant_list'))
            ->add('Liste')
        ;

        return $this->render(
            'backend/enseignant/list.html.twig',
            [
                'enseignants'     => $manager->loadAll(),
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * @Route("/add", name="admin_enseignant_add", methods={"GET","POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Services\BreadcrumbsService          $breadcrumbsService
     * @param \App\Manager\EnseignantManager                $manager
     * @param \App\Manager\EnseignantMentionManager                $enseignantmentionmanager
     * @param \App\Manager\MentionManager                $mentionmanager
     * @param \App\Manager\NiveauManager                            $niveaumanager
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameter
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function add(
        Request                     $request, 
        BreadcrumbsService          $breadcrumbsService, 
        EnseignantManager           $manager,
        EnseignantMentionManager    $enseignantmentionmanager,
        MentionManager              $mentionmanager,
        NiveauManager               $niveaumanager,
        ParameterBagInterface       $parameter
        ) : Response
    {
        $enseignant = $manager->createObject();
        $form   = $this->createForm(
            EnseignantType::class,
            $enseignant
        );
        $form->handleRequest($request);
        $mentions = $mentionmanager->loadAll();
        $niveaux = $niveaumanager->loadAll();

        if ($form->isSubmitted() && $form->isValid()) {
            $cvFile        = $form->get('pathCv')->getData();
            $diplomeFile   = $form->get('pathDiploma')->getData();
            $mentionPosted = ($_POST["mention"]) ? $_POST["mention"] : "";
            $niveauPosted  = ($_POST["niveau"]) ? $_POST["niveau"] : "";
            
            $directory     = $parameter->get('enseignants_directory');
            $uploader      = new \App\Services\FileUploader($directory);
            $today         = new \DateTime();
            $fileDirectory = $enseignant->getLastName() . "-" . $today->getTimestamp();

            // Upload file
            if ($cvFile) {
                $cvFileDisplay = $uploader->upload($cvFile, $directory, $fileDirectory);
                $enseignant->setPathCv($fileDirectory . "/" . $cvFileDisplay["filename"]);
            }

            if ($diplomeFile) {
                $diplomeFileDisplay = $uploader->upload($diplomeFile, $directory, $fileDirectory);
                $enseignant->setPathDiploma($fileDirectory . "/" . $diplomeFileDisplay["filename"]);
            }
            
            $manager->save($enseignant);
            
            // $enseignantmention = $enseignantmentionmanager->createObject();
            // Mention
            // if(count($mentionPosted)!=0){
            //     foreach ($mentionPosted as $key => $value) {
            //         $resMention = $mentionmanager->loadOneBy(array("id" => $value));
            //         $enseignantmention->setEnseignant($enseignant);
            //         $enseignantmention->setMention($resMention);
            //     }
            // }
            
            // Niveau
            // if(count($niveauPosted)!=0){
            //     foreach ($niveauPosted as $key => $value) {
            //         $resNiveau = $niveaumanager->loadOneBy(array("id" => $value));
            //         $enseignantmention->setNiveau($resNiveau);
            //     }
            // }
            
            // if(count($mentionPosted)!=0 && count($niveauPosted)!=0){
            //     $enseignantmentionmanager->save($enseignantmention);
            // }
            return $this->redirectToRoute('admin_enseignant_list');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Enseignant', $this->generateUrl('admin_enseignant_list'))
            ->add('Ajout')
        ;

        return $this->render(
            'backend/enseignant/add.html.twig',
            [
                'enseignant'    => $enseignant,
                'mentions'      => $mentions,
                'niveaux'       => $niveaux,
                'form'          => $form->createView(),
                'breadcrumbs'   => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * @Route("/{id}/edit", name="admin_enseignant_edit", methods={"GET","POST"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Enseignant                        $enseignant
     * @param \App\Manager\EnseignantManager                $manager
     * @param \App\Manager\EnseignantMentionManager                $enseignantmentionmanager
     * @param \App\Manager\MentionManager                $mentionmanager
     * @param \App\Manager\NiveauManager                            $niveaumanager
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameter
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(
        Request $request, 
        Enseignant $enseignant,
        EnseignantManager           $manager,
        EnseignantMentionManager    $enseignantmentionmanager,
        MentionManager              $mentionmanager,
        NiveauManager               $niveaumanager,
        ParameterBagInterface $parameter
        ) : Response
    {
        $form = $this->createForm(EnseignantType::class, $enseignant);
        $form->handleRequest($request);
        $mentions = $mentionmanager->loadAll();
        $niveaux = $niveaumanager->loadAll();

        if ($form->isSubmitted() && $form->isValid()) {
            
            $cvFile        = $form->get('pathCv')->getData();
            $diplomeFile   = $form->get('pathDiploma')->getData();
            // $mentionPosted = ($_POST["mention"]) ? $_POST["mention"] : "";
            // $niveauPosted  = ($_POST["niveau"]) ? $_POST["niveau"] : "";
            
            $directory     = $parameter->get('enseignants_directory');
            $uploader      = new \App\Services\FileUploader($directory);
            $today         = new \DateTime();
            $fileDirectory = $enseignant->getLastName() . "-" . $today->getTimestamp();

            // Upload file
            if ($cvFile) {
                $cvFileDisplay = $uploader->upload($cvFile, $directory, $fileDirectory);
                $enseignant->setPathCv($fileDirectory . "/" . $cvFileDisplay["filename"]);
            }

            if ($diplomeFile) {
                $diplomeFileDisplay = $uploader->upload($diplomeFile, $directory, $fileDirectory);
                $enseignant->setPathDiploma($fileDirectory . "/" . $diplomeFileDisplay["filename"]);
            }
            $this->getDoctrine()->getManager()->flush();
            
            //$enseignantmention = $enseignantmentionmanager->createObject();
            // Mention
            // if(count($mentionPosted)!=0){
            //     foreach ($mentionPosted as $key => $value) {
            //         $resMention = $mentionmanager->loadOneBy(array("id" => $value));
            //         $enseignantmention->setEnseignant($enseignant);
            //         $enseignantmention->setMention($resMention);
            //     }
            // }
            
            // Niveau
            // if(count($niveauPosted)!=0){
            //     foreach ($niveauPosted as $key => $value) {
            //         $resNiveau = $niveaumanager->loadOneBy(array("id" => $value));
            //         $enseignantmention->setNiveau($resNiveau);
            //     }
            // }
            
            // if(count($mentionPosted)!=0 && count($niveauPosted)!=0){
            //     $enseignantmentionmanager->save($enseignantmention);
            // }

            return $this->redirectToRoute('admin_enseignant_list');
        }

        return $this->render(
            'backend/enseignant/edit.html.twig',
            [
                'enseignant'    => $enseignant,
                'mentions'      => $mentions,
                'niveaux'       => $niveaux,
                'form'          => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="admin_enseignant_delete", methods={"DELETE"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Enseignant                        $enseignant
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(Request $request, Enseignant $enseignant) : Response
    {
        if ($this->isCsrfTokenValid('delete' . $enseignant->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($enseignant);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_enseignant_list');
    }
}
