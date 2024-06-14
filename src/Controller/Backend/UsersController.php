<?php

namespace App\Controller\Backend;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\UsersType;
use App\Manager\UserManager;
use App\Manager\MentionManager;
use App\Services\BreadcrumbsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Description of UsersController.php.
 * @Route("/admin/users")
 *
 * @package App\Controller\Backend
 */
class UsersController extends AbstractController
{

    /**
     * @var \App\Manager\MentionManager
     */
    private $userManager;

    /**
     * @var \App\Manager\UserManager
     */
    private $mentionManager;

    /**
     * UsersController constructor.
     *
     * @param \App\Manager\UserManager $userManager
     */
    public function __construct(UserManager $userManager,MentionManager $mentionManager)
    {
        $this->userManager      = $userManager;
        $this->mentionManager   = $mentionManager;
    }

    /**
     * @Route("/", name="admin_users_list", methods={"GET"})
     * @param \App\Services\BreadcrumbsService $breadcrumbsService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function list(BreadcrumbsService $breadcrumbsService) : Response
    {

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Utilisateurs', $this->generateUrl('admin_users_list'))
            ->add('Liste')
        ;

        // list user
        $users = $this->userManager->loadAll();

        return $this->render(
            'backend/users/list.html.twig',
            [
                'users'       => $users,
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * @Route("/add", name="admin_users_add", methods={"GET","POST"})
     * @param \Symfony\Component\HttpFoundation\Request                             $request
     * @param \App\Services\BreadcrumbsService                                      $breadcrumbsService
     * @param \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface $passwordEncoder
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function add(
        Request $request,
        BreadcrumbsService $breadcrumbsService,
        UserPasswordEncoderInterface $passwordEncoder
    ) : Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userDatas = $request->request->get('registration_form');
            $password  = $userDatas['plainPassword'];
            $password  = $passwordEncoder->encodePassword(
                $user,
                $password
            );
            $user->setPassword($password);
            $user->setStatus(true);
            //dump($user);die;
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            //$this->userManager->save($user);

            return $this->redirectToRoute('admin_users_list');
        }

        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Utilisateurs', $this->generateUrl('admin_users_list'))
            ->add('Ajout')
        ;

        return $this->render(
            'backend/users/add.html.twig',
            [
                'user'        => $user,
                'form'        => $form->createView(),
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * @Route("/{id}/edit", name="admin_users_edit", methods={"GET","POST"})
     * @param \Symfony\Component\HttpFoundation\Request                             $request
     * @param \App\Entity\User                                                      $user
     * @param \App\Services\BreadcrumbsService                                      $breadcrumbsService
     * @param \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface $passwordEncoder
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function edit(
        Request $request,
        User $user,
        BreadcrumbsService $breadcrumbsService,
        UserPasswordEncoderInterface $passwordEncoder
    ) : Response {
        $form = $this->createForm(
            RegistrationFormType::class,
            $user,
            [
                'user' => $user,
                'em'   => $this->getDoctrine()->getManager()
            ]
        );
        // dump($user);die;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userDatas = $request->request->get('registration_form');
            $password  = $userDatas['plainPassword'];
            if (!empty($password)) {
                $password = $passwordEncoder->encodePassword(
                    $user,
                    $password
                );
                $user->setPassword($password);
            }
            $employe = $form->getData()->getEmploye();
            $user->setEmploye($employe);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            
            return $this->redirectToRoute(
                'admin_users_list',
                [
                    'id' => $user->getId(),
                ]
            );
        }
        // begin manage breadcrumbs
        $breadcrumbsService
            ->add('Utilisateurs', $this->generateUrl('admin_users_list'))
            ->add('Modification')
        ;

        return $this->render(
            'backend/users/edit.html.twig',
            [
                'user'        => $user,
                'form'        => $form->createView(),
                'breadcrumbs' => $breadcrumbsService->getBreadcrumbs(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="admin_users_delete", methods={"DELETE"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\User                          $user
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(
        Request $request,
        User $user
    ) : Response {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_users_list');
    }
}
