<?php

namespace App\Command;

use App\Entity\User;
use App\Manager\EnseignantManager;
use App\Manager\ProfilManager;
use App\Manager\RoleManager;
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

class TeacherCreateCommand extends Command
{

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var EntityManagerInterface
     */
    private $em;

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
     * @var \App\Manager\EnseignantManager
     */
    private $enseignantManager;

    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $em,
        UserRepository $userRepository,
        RoleManager $roleManager,
        ProfilManager $profilManager,
        EnseignantManager $enseignantManager
    ) {
        $this->passwordEncoder   = $passwordEncoder;
        $this->em                = $em;
        $this->userRepository    = $userRepository;
        $this->roleManager       = $roleManager;
        $this->profilManager     = $profilManager;
        $this->enseignantManager = $enseignantManager;

        parent::__construct();
    }

    protected static $defaultName = 'app:teacher:create';

    protected function configure()
    {
        $this
            ->setDescription('Create a teacher.')
            ->addArgument('firstname', InputArgument::REQUIRED, 'The firstname')
            ->addArgument('lastname', InputArgument::REQUIRED, 'The lastname')
            ->addArgument('email', InputArgument::REQUIRED, 'The email')
            ->addArgument('login', InputArgument::REQUIRED, 'The login')
            ->addArgument('password', InputArgument::REQUIRED, 'The password')
            ->addArgument('address', InputArgument::OPTIONAL, 'The address')
            ->addArgument('phone', InputArgument::OPTIONAL, 'The phone')
            ->addOption('inactive', null, InputOption::VALUE_NONE, 'Set the teacher as inactive')
            ->setHelp(
                implode(
                    "\n", [
                            'The <info>app:teacher:create</info> command creates a teacher:',
                            '<info>php %command.full_name% Martin GILBERT</info>',
                            'This interactive shell will ask you for an email and then a password.',
                            'You can alternatively specify the email and password as the second and third arguments:',
                            '<info>php %command.full_name% Martin GILBERt martin.gilbert@dev-fusion.com change_this_password</info>',
                            'You can create an inactive teacher (will not be able to log in):',
                            '<info>php %command.full_name% --inactive</info>',
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

        if (!$input->getArgument('address')) {
            $question = new Question('Please choose an address:');
            $questions['address'] = $question;
        }

        if (!$input->getArgument('phone')) {
            $question = new Question('Please enter a phone number:');
            $questions['phone'] = $question;
        }

        foreach ($questions as $name => $question) {
            $answer = $this->getHelper('question')->ask($input, $output, $question);
            $input->setArgument($name, $answer);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        try {
            $io = new SymfonyStyle($input, $output);

            $user = $this->createUser($input);

            $this->createTeacher($user, $input);

            $io->success(
                sprintf(
                    'Created teacher with login=%s and password=%s.', $input->getArgument('login'),
                    $input->getArgument('password')
                )
            );

        } catch (\Exception $e) {
            echo $e->getMessage() . '<br>';
            echo $e->getFile() . '<br>';
            echo $e->getLine() . '<br>';
        }

        return 0;
    }

    private function createUser(InputInterface $input)
    {
        $email = $input->getArgument('email');
        $login = $input->getArgument('login');
        $pwd   = $input->getArgument('password');
        $user  = new User();
        $user
            ->setFirstname($input->getArgument('firstname'))
            ->setLastname($input->getArgument('lastname'))
            ->setEmail($email)
            ->setLogin($login)
        ;

        $user->setPassword(
            $this->passwordEncoder->encodePassword(
                $user,
                $pwd
            )
        );

        $user->setStatus(1);

        // check role
        $role = $this->roleManager->getRepository()->findOneByCode('ROLE_ENSEIGNANT');
        if (!$role) {
            $role = $this->roleManager->create('Enseignant', 'ROLE_ENSEIGNANT');
        }

        // check profil
        $profil = $this->profilManager->findOneByRoleCode('ROLE_ENSEIGNANT');
        if (!$profil) {
            $profil = $this->profilManager->create('Enseignant', $role);
        }

        $user->setProfil($profil);

        $this->em->persist($user);
        $this->em->flush($user);

        return $user;
    }

    /**
     * @param \App\Entity\User                                $user
     * @param \Symfony\Component\Console\Input\InputInterface $input
     *
     * @return \App\Entity\Enseignant
     */
    private function createTeacher(User $user, InputInterface $input)
    {
        // create teacher
        /** @var \App\Entity\Enseignant $teacher */
        $teacher = $this->enseignantManager->createObject();
        $teacher->setAddress($input->getArgument('address'));
        $teacher->setPhone($input->getArgument('phone'));
        $teacher->setFirstName($user->getFirstName());
        $teacher->setLastName($user->getLastName());
        $teacher->setEmail($user->getEmail());
        $teacher->setUser($user);

        $this->em->persist($teacher);
        $this->em->flush($teacher);

        return $teacher;
    }
}