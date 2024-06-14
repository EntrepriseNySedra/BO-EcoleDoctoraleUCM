<?php

namespace App\Controller\Backend;

use App\Entity\TypePrestation;
use App\Entity\TypePrestationMention;
use App\Services\BreadcrumbsService;
use App\Form\TypePrestationType;
use App\Manager\MentionManager;
use App\Manager\TypePrestationManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class TypePrestationController
 *
 * @package App\Controller\Backend
 */
class TypePrestationController extends AbstractController
{
     /**
     * List function
     *
     * @Route("/admin/type-prestation/index", name="admin_type_prestation_index", methods={ "GET" })
     * @param \App\Services\BreadcrumbsService          $breadcrumbsService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(BreadcrumbsService $breadcrumbsService, TypePrestationManager $tpManager)
    {
        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Type de préstation')
        ;

        return $this->render(
            'backend/type-prestation/index.html.twig',
            [
                'list'   => $tpManager->loadAll(),
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Add function
     * 
     * @Route("admin/type-prestation/new",  name="admin_type_prestation_new", methods={ "GET", "POST" })
     * @param \App\Entity\TypePrestation                    $typePrestation
     * @param \App\Services\BreadcrumbsService              $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request     $request
     * @param \App\Manager\TypePrestationManager            $manager
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function new(TypePrestation $typePrestation = null, BreadcrumbsService $breadcrumbsService, Request $request, TypePrestationManager $manager, MentionManager $mentionManager)
    {
        $typePrestation = new TypePrestation();
        $mentions       = $mentionManager->loadBy([], ['nom' => 'ASC']);
        //Created form
        $form = $this->createForm(TypePrestationType::class, $typePrestation);
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted() && $form->isValid()) {
            $typePrestation->setCreatedAt(new \DateTime());
            $typePrestation->setUpdatedAt(new \DateTime());
            $em->persist($typePrestation);
            
            //Creation association TypePrestation/Mention
            $mentionIds = $request->get('type_prestation_mentions', array());
            foreach( $mentionIds as $mId  ) {
                $mention = $mentionManager->load($mId);
                $typePrestationMention = new typePrestationMention();
                $typePrestationMention->setTypePrestation($typePrestation);
                $typePrestationMention->setMention($mention);                
                $em->persist($typePrestationMention);
            }
            $em->flush();

            $this->addFlash('Infos', 'Ajouté avec succès');
            return $this->redirectToRoute('admin_type_prestation_index');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Type de préstation',$this->generateUrl('admin_type_prestation_index'))
            ->add('Ajout')
        ;

        return $this->render(
            'backend/type-prestation/new.html.twig',
            [
                'typePrestation'        => $typePrestation,
                'mentions'              => $mentions,
                'form'                  => $form->createView(),
                'breadcrumbs'           => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Edit function
     * 
     * @Route("/admin/type-prestation/{id}/edit", name="admin_type_prestation_edit", methods={ "GET", "POST" })
     * @param \App\Entity\TypePrestation                    $typePrestation
     * @param \App\Services\BreadcrumbsService              $breadcrumbsService
     * @param \Symfony\Component\HttpFoundation\Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(TypePrestation $typePrestation, BreadcrumbsService $breadcrumbsService, MentionManager $mentionManager, Request $request) : Response
    {
        $mentions       = $mentionManager->loadBy([], ['nom' => 'ASC']);
        $typePrestationMentions = $typePrestation->getTypePrestationMentions();
        $tSelectedMentions = array_map( 
                function($el){
                    return $el->getMention()->getId();
                },
                $typePrestationMentions->toArray()
            ); 
        $form = $this->createForm(TypePrestationType::class, $typePrestation);
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted() && $form->isValid()) {
            $typePrestation->setUpdatedAt(new \DateTime());
            $em->persist($typePrestation);

            //remove and recreate association
            $mentionIds = $request->get('type_prestation_mentions', array());
            $typePrestationMentions->clear();
            foreach( $mentionIds as $mId  ) {
                $mention = $mentionManager->load($mId);
                $typePrestationMention = new typePrestationMention();
                $typePrestationMention->setTypePrestation($typePrestation);
                $typePrestationMention->setMention($mention);                
                $em->persist($typePrestationMention);
            }
            $em->flush();

            $this->addFlash('Infos', 'Modifié avec succès');
            return $this->redirectToRoute('admin_type_prestation_index');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Type de préstation',$this->generateUrl('admin_type_prestation_index'))
            ->add('Modification')
        ;
        
        
        
        
        return $this->render(
            'backend/type-prestation/edit.html.twig',
            [
                'mentions'              => $mentions,
                'tSelectedMentions'     => $tSelectedMentions,
                'typePrestation'        => $typePrestation,
                'form'                  => $form->createView(),
                'breadcrumbs'           => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * Delete function
     * 
     * @Route("/admin/type-prestation/delete/{id}", name="admin_type_prestation_delete", methods={"DELETE"})
     * @param \App\Entity\TypePrestation                    $typePrestation
     * @param \Symfony\Component\HttpFoundation\Request     $request
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(TypePrestation $typePrestation, Request $request) : Response
    {
        if ($this->isCsrfTokenValid('delete' . $typePrestation->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($typePrestation);
            $entityManager->flush();
            $this->addFlash('Infos', 'Supprimé avec succès');
        }
        return $this->redirectToRoute('admin_type_prestation_index');
    }

}
