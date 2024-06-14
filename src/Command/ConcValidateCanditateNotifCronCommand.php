<?php

namespace App\Command;

use App\Entity\ConcoursCandidature;
use App\Entity\Civility;
use App\Entity\Etudiant;
use App\Entity\Mention;
use App\Entity\Niveau;
use App\Entity\Roles;
use App\Entity\User;

use App\Manager\ConcoursCandidatureManager;
use App\Manager\ConcoursConfigManager;
use App\Manager\ConcoursEmploiDuTempsManager;
use App\Manager\ConcoursManager;
use App\Manager\ProfilManager;
use App\Manager\RoleManager;
use App\Manager\UserManager;
use App\Services\Mailer;
use App\Services\RandomUidService;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\DependencyInjection\ContainerInterface; // <- Add this

class ConcValidateCanditateNotifCronCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var $studentManager
     */
    private $studentManager;

    /**
     * $parameter
     */
    private $parameter;
    private $template;
    private $container;
    private $mailer;
    private $concCandidatureManager;
    private $userManager;
    private $roleManager;
    private $profileManager;
    private $passwordEncoder;
    private $emploiDuTempsManager;
    private $concoursConfManager;
    private $concoursManager;

    public function __construct(
        UserManager                     $userManager,
        RoleManager                     $roleManager,
        ProfilManager                   $profileManager,
        ConcoursCandidatureManager      $concCandidatureManager,
        EntityManagerInterface          $em,
        ParameterBagInterface           $parameter,
        \Twig\Environment               $template,
        Mailer                          $mailer,
        UserPasswordEncoderInterface    $passwordEncoder,
        ConcoursEmploiDuTempsManager    $emploiDuTempsManager,
        ConcoursConfigManager           $concoursConfManager,
        ConcoursManager                 $concoursManager
    ) {
        
        $this->em                = $em;
        $this->parameter = $parameter;
        $this->mailer = $mailer;
        $this->concCandidatureManager = $concCandidatureManager;
        $this->template = $template;
        $this->userManager = $userManager;
        $this->roleManager = $roleManager;
        $this->profileManager = $profileManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->emploiDuTempsManager = $emploiDuTempsManager;
        $this->concoursConfManager = $concoursConfManager;
        $this->concoursManager = $concoursManager;
        parent::__construct();
    }

    protected static $defaultName = 'app:cron:validate_candidate';

    protected function configure()
    {
        $this
            ->setDescription('Generate student matricule.')
            ->setHelp(
                implode(
                    "\n", [
                            'The <info>app:cron:validate_candidate</info> command validate concours candidature'
                        ]
                )
            )
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questions = [];
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        date_default_timezone_set('Africa/Nairobi');
        dump('Validate Notification');
        dump(date('d/m/Y H:i:s'));
        $siteConfig = $this->parameter->get('site');
        $mailerConfig = $this->parameter->get('mailer');  
        $concoursConf = $this->concoursConfManager->loadOneBy([]);
        $concoursAnneeUniv = $concoursConf->getAnneeUniversitaire();

        $candidatures = $this->concCandidatureManager->loadby(
            [
                'anneeUniversitaire' => $concoursAnneeUniv,
                'status' => [
                    ConcoursCandidature::STATUS_SU_VALIDATED,
                    ConcoursCandidature::STATUS_DISAPPROVED
                ],
                'email_notification' => NULL
            ]
        );
        //dump($candidatures);die;
        try {
            foreach($candidatures as $candidature) {                       

                $emailTplParams = [
                    'fullname'  => $candidature->getFullname(),
                    'motif'     => nl2br($candidature->getMotif())
                ];
                if ($candidature->getStatus() == ConcoursCandidature::STATUS_DISAPPROVED) { // refused
                    $emailTpl = "frontend/concours/email-refus-candidature.html.twig";
                } else { // accepted
                    $emailTpl = "frontend/concours/email-validation-candidature.html.twig";
                    $password = 'UCM' . strtoupper(RandomUidService::generateUid(6));
                    $emailTplParams['email'] = $candidature->getEmail();
                    $emailTplParams['password'] = $password;
                    
                    // modif sedra
                    $emailTplParams['mention'] = $candidature->getMention();
                    $emailTplParams['immatricule'] = $candidature->getImmatricule();
                    


                    /** @var \App\Entity\Concours $concours */
                    $tConcoursFilters = [
                            'annee_universitaire' => $concoursAnneeUniv,
                            'mention' => $candidature->getMention(),
                            'niveau'  => $candidature->getNiveau()];
                    if($candidature->getParcours()) $tConcoursFilters['parcours'] = $candidature->getParcours();
                    $concours      = $this->concoursManager->loadOneBy($tConcoursFilters, []);       
                    $emploiDuTemps = [];
                    if($concours) {
                        $emploiDuTemps = $this->emploiDuTempsManager->getByConcoursIdAndMentionId(
                            $concours->getId(), $candidature->getMention()->getId()
                        );    
                    }
                    // Retrieve the HTML generated in our twig file
                    $html = $this->template->render(
                        'frontend/concours/email-candidature-convocation.html.twig',
                        [
                            'candidature'   => $candidature,
                            'emploiDuTemps' => $emploiDuTemps,
                            'concours'      => $concours,
                            'fullname'      => $candidature->getFullname()
                        ]
                    );           
                    $user = $this->userManager->getRepository()->findOneByEmail($candidature->getEmail());
                    $this->createUser($candidature, $this->passwordEncoder, $this->roleManager, $this->profileManager, $this->userManager, $password, $user);
                }
                          
                $siteConfig   = $this->parameter->get('site');
                $mailerConfig = $this->parameter->get('mailer');
                $params = [
                    "sender"      => $mailerConfig['smtp_username'],
                    "pwd"         => $mailerConfig['smtp_password'],
                    "sendTo"      => [$candidature->getEmail(), 'onja.rails@gmail.com'],
                    "subject"     => 'Candidature concours',
                    "senderName"  => $siteConfig['name'],
                    "senderEmail" => $siteConfig['contact_email'],
                ];
                $emailTplParams['site'] = $siteConfig;
                $html = $this->template->render(
                    $emailTpl,
                    $emailTplParams
                );
                // Send email
                $candidature->setEmailNotification(ConcoursCandidature::NOTIFIED);
                $this->em->persist($candidature);
                //$this->mailer->sendMail($params, $html);
            }
            $this->em->flush();
            $io = new SymfonyStyle($input, $output);
            $io->success(
                "Notification sent"
            );
        } catch (\Exception $e) {
            echo $e->getMessage() . '/n';
            echo $e->getFile() . '<br>';
            echo $e->getLine() . '<br>';
        }

        return 0;
    }

    /**
     * @param \App\Entity\ConcoursCandidature                                       $candidature
     * @param \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface $passwordEncoder
     * @param \App\Manager\RoleManager                                              $roleManager
     * @param \App\Manager\ProfilManager                                            $profilManager
     * @param \App\Manager\UserManager                                              $userManager
     * @param string                                                                $password
     * @param                                                                       $user
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function createUser(
        ConcoursCandidature $candidature,
        UserPasswordEncoderInterface $passwordEncoder,
        RoleManager $roleManager,
        ProfilManager $profilManager,
        UserManager $userManager,
        string $password,
        $user
    )  {
        $roleEtudiant = Roles::ROLE_ETUDIANT;
        $user = !$user ? new User() : $user;
        $user
            ->setFirstname($candidature->getFirstName())
            ->setLastname($candidature->getLastName() ? $candidature->getLastName() : "")
            ->setEmail($candidature->getEmail())
            ->setLogin($candidature->getEmail())
        ;
        $user->setPassword(
            $passwordEncoder->encodePassword(
                $user,
                $password
            )
        );
        $user->setStatus(User::STATUS_ENABLED);

        // check role
        $role = $roleManager->getRepository()->findOneByCode($roleEtudiant);
        if (!$role) {
            $role = $roleManager->create('Etudiant', $roleEtudiant);
        }
        // check profil
        $profil = $profilManager->findOneByRoleCode($roleEtudiant);
        if (!$profil) {
            $profil = $profilManager->create('Etudiant', $role);
        }
        $user->setProfil($profil);
        $userManager->save($user);
    }

}