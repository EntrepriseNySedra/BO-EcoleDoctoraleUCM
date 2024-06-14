<?php

namespace App\Command;

use App\Entity\User;
use App\Manager\ProfilManager;
use App\Manager\RoleManager;
use App\Manager\UserManager;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserCreateCommand extends Command
{

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var \App\Manager\RoleManager
     */
    private $roleManager;

    /**
     * @var \App\Manager\ProfilManager
     */
    private $profilManager;

    /**
     * @var \App\Manager\UserManager
     */
    private $userManager;

    /**
     * UserCreateCommand constructor.
     *
     * @param \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface $passwordEncoder
     * @param \Doctrine\ORM\EntityManagerInterface                                  $em
     * @param \App\Repository\UserRepository                                        $userRepository
     * @param \App\Manager\RoleManager                                              $roleManager
     * @param \App\Manager\ProfilManager                                            $profilManager
     * @param \App\Manager\UserManager                                              $userManager
     */
    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        UserRepository $userRepository,
        RoleManager $roleManager,
        ProfilManager $profilManager,
        UserManager $userManager
    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->userRepository  = $userRepository;
        $this->roleManager     = $roleManager;
        $this->profilManager   = $profilManager;
        $this->userManager     = $userManager;

        parent::__construct();
    }

    protected static $defaultName = 'app:user:create';

    protected function configure()
    {
        $this
            ->setDescription('Create a user.')
            ->addArgument('firstname', InputArgument::REQUIRED, 'The firstname')
            ->addArgument('lastname', InputArgument::REQUIRED, 'The lastname')
            ->addArgument('email', InputArgument::REQUIRED, 'The email')
            ->addArgument('login', InputArgument::REQUIRED, 'The login')
            ->addArgument('password', InputArgument::REQUIRED, 'The password')
            ->addArgument('role', InputArgument::REQUIRED, 'The role (ex: ROLE_ADMIN)')
            ->setHelp(
                implode(
                    "\n", [
                            'The <info>app:user:create</info> command creates a user:',
                            '<info>php %command.full_name% Martin GILBERT</info>',
                            'This interactive shell will ask you for an email and then a password.',
                            'You can alternatively specify the email and password as the second and third arguments:',
                            '<info>php %command.full_name% Martin GILBERt martin.gilbert@dev-fusion.com change_this_password</info>',
                        ]
                )
            )
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questions = [];

        if (!$input->getArgument('firstname')) {
            $question = new Question('Please enter the firstname:');
            $question->setValidator(
                function ($firstname)
                {
                    if (empty($firstname)) {
                        throw new \Exception('Firstname can not be empty');
                    }

                    return $firstname;
                }
            );
            $questions['firstname'] = $question;
        }

        if (!$input->getArgument('lastname')) {
            $question = new Question('Please enter the lastname:');
            $question->setValidator(
                function ($lastname)
                {
                    if (empty($lastname)) {
                        throw new \Exception('Lastname can not be empty');
                    }

                    return $lastname;
                }
            );
            $questions['lastname'] = $question;
        }

        if (!$input->getArgument('email')) {
            $question = new Question('Please enter an email:');
            $question->setValidator(
                function ($email)
                {
                    if (empty($email)) {
                        throw new \Exception('Email can not be empty');
                    }
                    if ($this->userRepository->findOneByEmail($email)) {
                        throw new \Exception('Email is already used');
                    }

                    return $email;
                }
            );
            $questions['email'] = $question;
        }

        if (!$input->getArgument('login')) {
            $question = new Question('Please enter an login:');
            $question->setValidator(
                function ($login)
                {
                    if (empty($login)) {
                        throw new \Exception('login can not be empty');
                    }
                    if ($this->userRepository->findOneByLogin($login)) {
                        throw new \Exception('login is already used');
                    }

                    return $login;
                }
            );
            $questions['login'] = $question;
        }

        if (!$input->getArgument('password')) {
            $question = new Question('Please choose a password:');
            $question->setValidator(
                function ($password)
                {
                    if (empty($password)) {
                        throw new \Exception('Password can not be empty');
                    }

                    return $password;
                }
            );
            $question->setHidden(true);
            $questions['password'] = $question;
        }

        if (!$input->getArgument('role')) {
            $question = new Question('Please enter a role (ex: ROLE_ADMIN):');
            $question->setValidator(
                function ($password)
                {
                    if (empty($password)) {
                        throw new \Exception('Role can not be empty');
                    }

                    return $password;
                }
            );
            $questions['role'] = $question;
        }

        foreach ($questions as $name => $question) {
            $answer = $this->getHelper('question')->ask($input, $output, $question);
            $input->setArgument($name, $answer);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $io    = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $user  = new User();
        $user
            ->setFirstname($input->getArgument('firstname'))
            ->setLastname($input->getArgument('lastname'))
            ->setEmail($email)
            ->setLogin($input->getArgument('login'))
        ;

        $user->setPassword(
            $this->passwordEncoder->encodePassword(
                $user,
                $input->getArgument('password')
            )
        );

        $user->setStatus(1);

        // check role
        $roleName = 'user';
        $roleCode = 'ROLE_USER';
        $role     = $this->roleManager->getRepository()->findOneByCode($input->getArgument('role'));
        if (!$role) {
            $roles = explode('_', $input->getArgument('role'));
            if (!empty($roles[1])) {
                $roleName = $roles[1];
                $roleCode = $input->getArgument('role');
                $role     = $this->roleManager->create(ucfirst($roleName), mb_strtoupper($roleCode));
            } else {
                $role = $this->roleManager->getRepository()->findOneByCode($roleCode);
                if (!$role) {
                    $role = $this->roleManager->create(ucfirst($roleName), mb_strtoupper($roleCode));
                }
            }
        }

        // check profil
        $profil = $this->profilManager->findOneByRoleCode($roleCode);
        if (!$profil) {
            $profil = $this->profilManager->create(ucfirst($roleName), $role);
        }

        $user->setProfil($profil);

        $this->userManager->save($user);

        $io->success(
            sprintf(
                'Created user with login=%s and password=%s.',
                $input->getArgument('login'),
                $input->getArgument('password')
            )
        );

        return 0;
    }
}
