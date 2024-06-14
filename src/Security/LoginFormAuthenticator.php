<?php

namespace App\Security;

use App\Entity\ConcoursCandidature;
use App\Entity\User;
use App\Entity\Etudiant;
use App\Entity\Inscription;
use App\Entity\AnneeUniversitaire;
use App\Manager\InscriptionManager;
use App\Manager\AnneeUniversitaireManager;
use App\Manager\ConcoursCandidatureManager;
use App\Services\AnneeUniversitaireService;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator implements PasswordAuthenticatedInterface
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private $entityManager;

    private $urlGenerator;

    private $csrfTokenManager;

    private $passwordEncoder;

    private $doctrineManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator,
        CsrfTokenManagerInterface $csrfTokenManager,
        UserPasswordEncoderInterface $passwordEncoder,
        ManagerRegistry $doctrineManager
    ) {
        $this->entityManager    = $entityManager;
        $this->urlGenerator     = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder  = $passwordEncoder;
        $this->doctrineManager  = $doctrineManager;
    }

    public function supports(Request $request)
    {
        return self::LOGIN_ROUTE === $request->attributes->get('_route')
               && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        $credentials = [
            'email'      => $request->request->get('email'),
            'password'   => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['email']
        )
        ;

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['login' => $credentials['email']]);

        if (!$user) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Username could not be found.');
        }

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function getPassword($credentials) : ?string
    {
        return $credentials['password'];
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request                            $request
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @param string                                                               $providerKey
     *
     * @return JsonResponse|RedirectResponse|Response|null
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        $providerKey
    ) {
        //dump("ok");die;
        $userRoles = $token->getUser()->getRoles();
       //Display Mac Address         
       //dump($_SERVER['REMOTE_ADDR']);die;
        // if ( !count(array_intersect(['ROLE_SSRS', 'ROLE_ADMIN', 'ROLE_CHEFMENTION', 'ROLE_RECTEUR', 'ROLE_ENSEIGNANT'], $userRoles)) > 0 ) {
        //    $redirectUrl = $this->urlGenerator->generate('app_logout');
        //    $response = [
        //             'success'     => 1,
        //             'redirectUrl' => $redirectUrl,
        //         ];

        //     return new JsonResponse($response);
        // }

        if ($request->isXmlHttpRequest()) {       
            $redirectUrl = $this->urlGenerator->generate('home');
            if (in_array('ROLE_ETUDIANT', $userRoles)) {
                $user = $token->getUser();
                $etudiantManager    = $this->entityManager->getRepository(Etudiant::class);
                $inscriptionManager = new InscriptionManager($this->entityManager, Inscription::class);
                $anneeUnivManager   = new AnneeUniversitaireManager($this->entityManager, AnneeUniversitaire::class);
                $concCandidatureManager   = new ConcoursCandidatureManager($this->entityManager, ConcoursCandidature::class);

                $anneeUnivService   = new AnneeUniversitaireService($anneeUnivManager);
                $currentAnneUniv    = $anneeUnivService->getCurrent();
                if(!$currentAnneUniv){
                    $appLogoutUrl = $this->urlGenerator->generate('app_logout');
                    return new JsonResponse(['success' => 1, 'redirectUrl' => $appLogoutUrl]);
                }
                $currentEtudiant   = $etudiantManager->findOneBy(['user' => $user]);

                if (!$currentEtudiant) {
                    //si admis 
                    $candidat = $concCandidatureManager->loadOneBy(['email' => $user->getEmail(), 'resultat' => ConcoursCandidature::RESULT_ADMITTED]);
                    //+6dump($candidat);die;
                    // dump($candidat);die;
                    if($candidat) {
                        $redirectUrl = $this->urlGenerator->generate('front_student_first_inscription');
                    }
                    else {
                        $appLogoutUrl = $this->urlGenerator->generate('app_logout');
                        return new JsonResponse(['success' => 1, 'redirectUrl' => $appLogoutUrl]);
                    }
                } else {                    
                    $etudiantStatus = $currentEtudiant->getStatus();
                    // if ($etudiantStatus == Etudiant::STATUS_DENIED_ECOLAGE) {
                    //     $redirectUrl = $this->urlGenerator->generate('front_student_ecolage_denied');
                    // }
                    $inscriptionStatus = $inscriptionManager->getByStudentIdAndCollegeYear(
                        $currentEtudiant->getId(),
                        $currentAnneUniv->getId()
                    );

                    switch($inscriptionStatus) {
                        case Inscription::STATUS_VALIDATED:
                            $redirectUrl = $this->urlGenerator->generate('front_student_classes');
                            break;
                        case Inscription::STATUS_CREATED:
                            $redirectUrl = $this->urlGenerator->generate('app_logout');
                            break;
                        default:
                            $redirectUrl = $this->urlGenerator->generate('front_student_re_inscription');
                            break;
                    }
                }
            }
            if (in_array('ROLE_ENSEIGNANT', $userRoles)) {
                $redirectUrl = $this->urlGenerator->generate('frontend_teacher_index');
            }
            // if (in_array('ROLE_SCOLARITE', $token->getUser()->getRoles())) {
            if ( count(array_intersect(['ROLE_SCOLARITE', 'ROLE_SSRS'], $userRoles)) > 0 ) {
                $redirectUrl = $this->urlGenerator->generate('front_scolarite_candidature');
            }
            if ( count(array_intersect(['ROLE_ASSISTANT', 'ROLE_ADMIN_ASS'], $userRoles)) > 0 ) {
            //if (in_array('ROLE_ASSISTANT', $userRoles)) {
                $redirectUrl = $this->urlGenerator->generate('frontend_assistant_index');
            }
            if (in_array('ROLE_ADMIN', $userRoles)) {
                $redirectUrl = $this->urlGenerator->generate('admin_dashboard');
            }
            if (in_array('ROLE_CHEFMENTION', $userRoles)) {
                $redirectUrl = $this->urlGenerator->generate('front_chefmention_presence_enseignant_index');
            }
            if (in_array('ROLE_DOYEN', $userRoles)) {
                $redirectUrl = $this->urlGenerator->generate('front_doyen_gestion_emploi_du_temps');
            }
            if (in_array('ROLE_RECTEUR', $userRoles)) {
                $redirectUrl = $this->urlGenerator->generate('front_recteur_gestion_emploi_du_temps');
            }
            if (in_array('ROLE_SG', $userRoles)) {
                $redirectUrl = $this->urlGenerator->generate('front_sg_gestion_emploi_du_temps');
            }
            if (in_array('ROLE_DIR_PINC', $userRoles)) {
                $redirectUrl = $this->urlGenerator->generate('index');
            }
            if ( count(array_intersect(['ROLE_COMPTABLE', 'ROLE_RF'], $userRoles)) > 0 ) {
                $redirectUrl = $this->urlGenerator->generate('front_comptable_presence_enseignant_index');
            }
            if (in_array('ROLE_RVN', $userRoles)) {
                $redirectUrl = $this->urlGenerator->generate('front_rvn_absence_index');
            }
            $response = [
                'success'     => 1,
                'redirectUrl' => $redirectUrl,
            ];
            //dump($response);die;
            return new JsonResponse($response);
        } else {
            // default redirection
            $route = 'home';
            // If is a admin or super admin we redirect to the backoffice area
            if (in_array('ROLE_ADMIN', $userRoles, true) || in_array('ROLE_SUPER_ADMIN', $userRoles, true)) {
                $route = 'admin_dashboard';
            } // otherwise, if is a commercial user we redirect to the crm area
            // elseif (in_array('ROLE_SCOLARITE', $roles, true)) {
            if ( count(array_intersect(['ROLE_SCOLARITE', 'ROLE_SSRS'], $userRoles)) > 0 ) {
                $route = 'front_scolarite_candidature';
            } elseif (in_array('ROLE_ASSISTANT', $userRoles, true)) {
                $route = 'frontend_assistant_index';
            } elseif (in_array('ROLE_CHEFMENTION', $userRoles, true)) {
                $route = 'front_chefmention_presence_enseignant_index';
            } elseif (in_array('ROLE_DOYEN', $userRoles, true)) {
                $route = 'front_doyen_gestion_emploi_du_temps';
            } elseif (in_array('ROLE_RECTEUR', $userRoles, true)) {
                $route = 'front_recteur_gestion_emploi_du_temps';
            } elseif (in_array('ROLE_SG', $userRoles, true)) {
                $route = 'front_sg_presence_enseignant_index';
            } elseif ( count(array_intersect(['ROLE_COMPTABLE', 'ROLE_RF'], $userRoles)) > 0 ) {
                $route = 'front_comptable_presence_enseignant_index';
            } elseif (in_array('ROLE_ENSEIGNANT', $userRoles, true)) {
                $route = 'frontend_teacher_index';
            } elseif (in_array('ROLE_RVN', $userRoles, true)) {
                $route = 'front_rvn_absence_index';
            }

            return new RedirectResponse($this->urlGenerator->generate($route));
        }
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($request->isXmlHttpRequest()) {
            $response = [
                'success' => 0,
                'message' => 'Identifiant ou mot de passe incorrect !'
            ];

            return new JsonResponse($response);
        } else {
            return parent::onAuthenticationFailure($request, $exception);
        }
    }

    protected function getLoginUrl()
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
