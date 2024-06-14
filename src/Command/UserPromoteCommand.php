<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UserPromoteCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'app:user:promote';

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $om;

    /**
     * UserPromoteCommand constructor.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $om
     */
    public function __construct(EntityManagerInterface $om)
    {
        $this->om = $om;

        parent::__construct();
    }

    /**
     *
     */
    protected function configure()
    {
        $this
            ->setDescription('Promote a user by adding him a new roles.')
            ->addArgument('email', InputArgument::REQUIRED, 'Email address of the user you want to promote.')
            ->addArgument('roles', InputArgument::REQUIRED, 'The roles you want to add to the user.')
        ;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $io    = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $roles = $input->getArgument('roles');

        $userRepository = $this->om->getRepository(User::class);
        /** @var User $user */
        $user           = $userRepository->findOneByEmail($email);

        if ($user) {
            $user->addRoles($roles);
            $this->om->flush();

            $io->success('The roles has been successfully added to the user.');
        } else {
            $io->error('There is no user with that email address.');
        }

        return 0;
    }
}
