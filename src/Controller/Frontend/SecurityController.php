<?php

namespace App\Controller\Frontend;

use App\Entity\User;
use App\Form\ResetPasswordType;
use App\Manager\UserManager;
use App\Services\Mailer;
use App\Repository\UserRepository;
use App\Services\TokenGenrator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils, Request $request) : Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
            'frontend/security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]
        );
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException(
            'This method can be blank - it will be intercepted by the logout key on your firewall.'
        );
    }

    /**
     * @Route("/recuperation-mot-de-passe", name="app_reset_password_index")
     * @param \App\Manager\UserManager                  $userManager
     * @param \App\Repository\UserRepository            $userRepository
     * @param \App\Services\Mailer                      $mailer
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function resetPasswordIndex(
        UserManager $userManager,
        UserRepository $userRepository,
        Mailer $mailer,
        Request $request
    ) {
        $requestEmail = $request->get('email');

        if ($requestEmail) {
            /**@var User $user */
            $user = $userRepository->findOneByEmail($requestEmail);

            if ($user) {
                $confirmationToken = TokenGenrator::generateToken();

                $siteConfig   = $this->getParameter('site');
                $mailerConfig = $this->getParameter('mailer');

                $params = [
                    "sender"      => $mailerConfig['smtp_username'],
                    "pwd"         => $mailerConfig['smtp_password'],
                    "sendTo"      => $user->getEmail(),
                    "subject"     => 'Regénération de mot de passe',
                    "senderName"  => $siteConfig['name'],
                    "senderEmail" => $siteConfig['contact_email'],
                ];

                $url = $this->generateUrl(
                    'app_reset_password', [
                    'token' => $confirmationToken,
                ], UrlGeneratorInterface::ABSOLUTE_URL
                );

                $html = $this->renderView(
                    "frontend/security/email-password-recovery.html.twig",
                    [
                        "fullname" => $user->getFullname(),
                        "message"  => 'Vous avez demandé à récuperer votre mot de passe. Suivez ce <strong><a href="' . $url . '">ce lien</a></strong> pour créer un nouveau mot de passe ou cliquez le boutton ci-dessous',
                        "site"     => $siteConfig,
                    ]
                );

                // Send email
                $mailer->sendMail($params, $html);

                // Save user
                $user->setConfirmationToken($confirmationToken);
                $userManager->save($user);

                $this->addFlash(
                    'success',
                    'Un lien de reinitialisation de mot de passe vous a été envoyé vers votre adresse "' . $requestEmail . '"'
                );
                $requestEmail = null;

                // return $this->redirectToRoute('app_login');
            } else {
                $this->addFlash(
                    'error', "Désolé! Nous n'avons pas pu trouver votre compte.\n Veuillez vérifier votre adresse email"
                );
            }
        }

        return $this->render(
            'frontend/security/reset-password-index.html.twig',
            [
                'email' => $requestEmail,
            ]
        );
    }

    /**
     * @Route("/reset-password/{token}", name="app_reset_password")
     */
    public function resetPassword(
        Request $request,
        UserManager $userManager,
        UserRepository $userRepository,
        UserPasswordEncoderInterface $passwordEncoder,
        string $token
    ) {
        /**@var User $user */
        $user = $userRepository->findOneByConfirmationToken($token);

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(ResetPasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Encrypt Password
            $password = $request->request->get('reset_password')['password']['first'];
            $hash     = $passwordEncoder->encodePassword($user, $password);

            $user
                ->setPassword($hash)
                ->setConfirmationToken(null)
            ;

            // Save User changes
            $userManager->save($user);

            return $this->redirectToRoute('front_student_login');
        }

        return $this->render(
            'frontend/security/reset-password.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }
}
