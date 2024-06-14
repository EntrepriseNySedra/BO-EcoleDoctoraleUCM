<?php

namespace App\Controller\Backend;

use App\Entity\Mention;
use App\Services\BreadcrumbsService;
use App\Form\MentionType;
use App\Manager\MentionManager;
use App\Manager\DepartementManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use \Psr\Container\ContainerInterface;
use Cocur\Slugify\Slugify;

/**
 * Class MentionController
 *
 * @package App\Controller\Backend
 */
class MentionController extends AbstractController
{
    /**
     * Add function
     * 
     * @Route("admin/formation/mention/new", name="admin_rf_mention_new", methods={ "GET", "POST" })
     * @param \App\Entity\Mention                           $mention
     * @param \App\Services\BreadcrumbsService              $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request     $request
     * @param \App\Manager\MentionManager $manager
     * @param \App\Manager\DepartementManager $manager
     * @param \Psr\Container\ContainerInterface             $container
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addAction(
            Mention $mention = null, 
            BreadcrumbsService $breadcrumbsService, 
            Request $request, 
            MentionManager $manager, 
            ContainerInterface $container)
    {
        if(!$mention)
        {
            $mention = new Mention();
        }

        //Created form
        $form = $this->createForm(MentionType::class, $mention);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mention->setCreatedAt(new \DateTime());
            $file = $form->get('file')->getData();
            // Upload file
            if ($file) {
                $directory      = $container->getParameter('mention_directory');
                $uploader       = new \App\Services\FileUploader($directory);
                $fileDirectory = $mention->getUuid();
                $file_dispaly   = $uploader->upload($file,$directory,$fileDirectory);
                $mention->setPath($file_dispaly["filename"]);
            }
            $slugify = new Slugify();
            $slug = $slugify->slugify($mention->getNom());
            $mention->setSlug($slug);
            $manager->save($mention);
            $this->addFlash('Infos', 'Ajouté avec succès');
            
            return $this->redirectToRoute('admin_rf_mention_liste');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Formations')
            ->add('Mention',$this->generateUrl('admin_rf_mention_liste'))
            ->add('Ajout')
        ;

        return $this->render(
            'backend/mention/add.html.twig',
            [
                'mention'      => $mention,
                'departement'  => $manager->loadAll(),
                'formMention'  => $form->createView(),
                'breadcrumbs'  => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Edit function
     * 
     * @Route("/admin/formation/mention/{id}/edit", name="admin_rf_mention_edit", methods={ "GET", "POST" })
     * @param \App\Entity\Mention                           $mention
     * @param \App\Services\BreadcrumbsService              $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request     $request
     * @param \App\Manager\DepartementManager $manager
     * @param \Psr\Container\ContainerInterface             $container
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(
            Mention $mention, 
            BreadcrumbsService $breadcrumbsService, 
            Request $request, 
            DepartementManager $manager,
            ContainerInterface $container) : Response
    {
        $form = $this->createForm(MentionType::class, $mention);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mention->setUpdatedAt(new \DateTime());
            $file = $form->get('file')->getData();
            // Upload file
            if ($file) {
                $directory      = $container->getParameter('mention_directory');
                $uploader       = new \App\Services\FileUploader($directory);
                $fileDirectory = $mention->getUuid();
                $file_dispaly   = $uploader->upload($file,$directory,$fileDirectory);
                $mention->setPath($file_dispaly["filename"]);
            }
            $slugify = new Slugify();
            $slug = $slugify->slugify($mention->getNom());
            $mention->setSlug($slug);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('Infos', 'Modifié avec succès');
            return $this->redirectToRoute('admin_rf_mention_liste');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Formations')
            ->add('Mention',$this->generateUrl('admin_rf_mention_liste'))
            ->add('Modification')
        ;

        return $this->render(
            'backend/mention/edit.html.twig',
            [
                'mention'      => $mention,
                'departement'  => $manager->loadAll(),
                'formMention'  => $form->createView(),
                'breadcrumbs'  => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Delete function
     * 
     * @Route("/admin/formation/mention/delete/{id}", name="admin_rf_mention_delete", methods={"DELETE"})
     * @param \App\Entity\Mention                       $mention
     * @param \Symfony\Component\HttpFoundation\Request $request
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteMention(Mention $mention, Request $request) : Response
    {
        if ($this->isCsrfTokenValid('delete' . $mention->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($mention);
            $entityManager->flush();
            $this->addFlash('Infos', 'Supprimé avec succès');
        }
        return $this->redirectToRoute('admin_rf_mention_liste');
    }

    /**
     * Detail function
     * 
     * @Route("/admin/formation/mention/{id}/detail", name="admin_rf_mention_detail", methods={ "GET", "POST" })
     * @param \App\Entity\Mention                           $mention
     * @param \App\Services\BreadcrumbsService              $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailAction(Mention $mention, BreadcrumbsService $breadcrumbsService, Request $request) : Response
    {
        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Formations')
            ->add('Mention',$this->generateUrl('admin_rf_mention_liste'))
            ->add('Detail')
        ;

        return $this->render(
            'backend/mention/detail.html.twig',
            [
                'mention'      => $mention,
                'breadcrumbs'  => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * List function
     *
     * @Route("/admin/formation/mention/liste", name="admin_rf_mention_liste", methods={ "GET" })
     * @param \App\Services\BreadcrumbsService          $breadcrumbsService
     * @param \App\Manager\MentionManager $manager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(BreadcrumbsService $breadcrumbsService, MentionManager $manager)
    {
        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Formations')
            ->add('Mention')
        ;

        return $this->render(
            'backend/mention/list.html.twig',
            [
                'mentions'    => $manager->loadAll(),
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }
}
