<?php

namespace App\Controller\Frontend;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\KernelInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

use App\Repository\DiscussionRepository;
use App\Repository\DiscussionCommentaireRepository;
use App\Entity\Discussion;
use App\Entity\DiscussionCommentaire;
use App\Entity\Profil;

use Knp\Component\Pager\PaginatorInterface;

use App\Form\DiscussionType;
use App\Form\ DiscussionCommentaireType;

/**
 * Description of DiscussionController.php.
 *
 * @package App\Controller\Frontend
 * @IsGranted({"ROLE_ENSEIGNANT", "ROLE_ETUDIANT"})
 */
class DiscussionController extends AbstractController
{
    /**
     * @Route("/discussion", name="frontend_discussion_index", methods={"GET"})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(
        Request             $request,
        DiscussionRepository $discussionRepo,
        PaginatorInterface $paginator
    ) {
        $page = $request->query->getInt('page', 1);
        $user             =   $this->getUser();
        $list =  $discussionRepo->findBy([], ['created_at' => 'DESC']);
        $profilName =$user->getProfil()->getName();      
        switch($profilName){
            case Profil::ENSEIGNANT:
                $profilTplName =strtolower(Profil::ENSEIGNANT_TPL_NAME);     
                break;
            default:
                $profilTplName =strtolower($profilName);           
                break;
        }        
        $pagination = $paginator->paginate(
            $list, // Vos données à paginer
            $page, // Numéro de la page
            $itemsPerPage=5 // Nombre d'éléments par page
        );

        return $this->render(
            'frontend/' . $profilTplName . '/discussion/index.html.twig',
            [
                'data' => $pagination
            ]
        );
    }

    /**
     * @Route("/discussion/new", name="frontend_discussion_new", methods={"GET", "POST"})
     * @return \Symfony\Component\HttpFoundation\Response
     * @IsGranted({"ROLE_ENSEIGNANT"})
     */
    public function new(
        Request             $request,
        DiscussionRepository $discussionRepo
    ) {
        $user             =   $this->getUser();
        $discussion = new Discussion();      
        $discussion->setAuteur($user);
        $form = $this->createForm(
            DiscussionType::class,
            $discussion,
            []
        );   
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $discussion->setStatus(Discussion::STATUS_CREATED);
            $em->persist($discussion);
            $em->flush();

            return $this->redirectToRoute('frontend_discussion_index');
        }
        return $this->render(
            'frontend/teacher/discussion/new.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/discussion/{id}/commentaires", name="frontend_discussion_comment_index", methods={"GET", "POST"})     
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Discussion                    $discussion
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function commentaires(
        Request                         $request,
        Discussion                      $discussion,
        DiscussionCommentaireRepository $discussionCommentRepo,
        PaginatorInterface              $paginator
    ) {
        $page = $request->query->getInt('page', 1);
        $user             =   $this->getUser();
        $list =  $discussionCommentRepo->findBy(['discussion' => $discussion], ['created_at' => 'DESC']);
        $profilName =$user->getProfil()->getName();      
        switch($profilName){
            case Profil::ENSEIGNANT:
                $profilTplName =strtolower(Profil::ENSEIGNANT_TPL_NAME);     
                break;
            default:
                $profilTplName =strtolower($profilName);           
                break;
        }        
        $pagination = $paginator->paginate(
            $list, // Vos données à paginer
            $page, // Numéro de la page
            $itemsPerPage=5 // Nombre d'éléments par page
        );

        $newComment = new DiscussionCommentaire();
        $newComment->setAuteur($user);
        $newComment->setDiscussion($discussion);
        $form = $this->createForm(
            DiscussionCommentaireType::class,
            $newComment,
            []
        );   
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $newComment->setStatus(DiscussionCommentaire::STATUS_CREATED);
            $em->persist($newComment);
            $em->flush();

            return $this->redirectToRoute('frontend_discussion_comment_index', ['id' => $discussion->getId()]);
        }

        return $this->render(
            'frontend/' . $profilTplName . '/discussion/commentaire/index.html.twig',
            [
                'discussion' => $discussion,
                'form' => $form->createView(),
                'data' => $pagination
            ]
        );
    }

}