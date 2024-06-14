<?php

namespace App\Command;

use App\Entity\User;
use App\Manager\EtudiantManager;
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

class StudentUpdatePasswordCommand extends Command
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
     * @var \App\Manager\EtudiantManager
     */
    private $etudiantManager;

    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $em,
        UserRepository $userRepository,
        EtudiantManager $etudiantManager
    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->em              = $em;
        $this->userRepository  = $userRepository;
        $this->etudiantManager = $etudiantManager;

        parent::__construct();
    }

    protected static $defaultName = 'app:student:updatePassword';

    protected function configure()
    {
        $this
            ->setDescription('Update student password')
            ->addArgument('login', InputArgument::REQUIRED, 'The login')
            ->addArgument('password', InputArgument::REQUIRED, 'The password')
            ->addOption('inactive', null, InputOption::VALUE_NONE, 'Set the student as inactive')
            ->setHelp(
                implode(
                    "\n", [
                            'The <info>app:student:updatePassword</info> command updates a student password:',
                            '<info>php %command.full_name% Martin GILBERT</info>',
                            'This interactive shell will ask you for an email and then a password.',
                            'You can alternatively specify the email and password as the second and third arguments:',
                            '<info>php %command.full_name% Martin GILBERt martin.gilbert@dev-fusion.com change_this_password</info>',
                            'You can create an inactive student (will not be able to log in):',
                            '<info>php %command.full_name% --inactive</info>',
                        ]
                )
            )
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questions = [];

        if (!$input->getArgument('login')) {
            $question = new Question('Please enter an login:');
            $question->setValidator(
                function ($login)
                {
                    if (empty($login)) {
                        throw new \Exception('login can not be empty');
                    }
                    if (!$this->user = $this->userRepository->findOneByLogin($login)) {
                        throw new \Exception('user not find');
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

        foreach ($questions as $name => $question) {
            $answer = $this->getHelper('question')->ask($input, $output, $question);
            $input->setArgument($name, $answer);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        try {
            $io = new SymfonyStyle($input, $output);
            $this->updateUserPassword($input);
            $io->success(
                sprintf('password updated for user with login=%s.', $input->getArgument('login'))
            );

        } catch (\Exception $e) {
            echo $e->getMessage() . '<br>';
            echo $e->getFile() . '<br>';
            echo $e->getLine() . '<br>';
        }

        return 0;
    }

    private function updateUserPassword(InputInterface $input)
    {
        $user = $this->user;
        $pwd   = $input->getArgument('password');
        $user->setPassword(
            $this->passwordEncoder->encodePassword(
                $user,
                $pwd
            )
        );
        $this->em->persist($user);
        $this->em->flush($user);

        return $user;
    }
}