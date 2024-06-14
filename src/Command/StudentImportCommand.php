<?php

namespace App\Command;

use App\Entity\Mention;
use App\Entity\User;
use App\Manager\CivilityManager;
use App\Manager\EtudiantManager;
use App\Manager\MentionManager;
use App\Manager\NiveauManager;
use App\Manager\ParcoursManager;
use App\Manager\ProfilManager;
use App\Manager\RoleManager;
use App\Manager\UserManager;
use App\Tools\ParseCSV;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;
use Symfony\Component\Filesystem\Filesystem;

class StudentImportCommand extends Command
{
    static $FILE_IMPORT = 'L1DROIT.csv';

    protected static $defaultName = 'app:student:import';

    protected static $defaultDescription = 'Import student from csv file';

    /**
     * @var \Symfony\Component\Stopwatch\Stopwatch
     */
    private $stopwatch;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \App\Manager\UserManager
     */
    private $userManager;

    /**
     * @var \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var \App\Manager\RoleManager
     */
    private $roleManager;

    /**
     * @var \App\Manager\ProfilManager
     */
    private $profilManager;

    /**
     * @var \App\Manager\EtudiantManager
     */
    private $etudiantManager;

    /**
     * @var \App\Manager\MentionManager
     */
    private $mentionManager;

    /**
     * @var \App\Manager\NiveauManager
     */
    private $niveauManager;

    /**
     * @var \App\Manager\ParcoursManager
     */
    private $parcoursManager;

    /**
     * @var \App\Manager\CivilityManager
     */
    private $civilityManager;

    public function __construct(
        Stopwatch $stopwatch,
        ContainerInterface $container,
        UserManager $userManager,
        UserPasswordEncoderInterface $passwordEncoder,
        RoleManager $roleManager,
        ProfilManager $profilManager,
        EtudiantManager $etudiantManager,
        MentionManager $mentionManager,
        NiveauManager $niveauManager,
        ParcoursManager $parcoursManager,
        CivilityManager $civilityManager
    ) {
        parent::__construct();

        $this->stopwatch       = $stopwatch;
        $this->container       = $container;
        $this->userManager     = $userManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->roleManager     = $roleManager;
        $this->profilManager   = $profilManager;
        $this->etudiantManager = $etudiantManager;
        $this->mentionManager  = $mentionManager;
        $this->niveauManager   = $niveauManager;
        $this->parcoursManager = $parcoursManager;
        $this->civilityManager = $civilityManager;
    }

    /**
     * @return void
     */
    protected function configure() : void
    {
        $this->addArgument('filename', InputArgument::REQUIRED, 'Enter file name');
        $this->setDescription(self::$defaultDescription)
             ->setHelp(
                 implode(
                     "\n", [
                             'The <info>app:student:import</info> command import students from a csv file'
                         ]
                 )
             )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        gc_enable();

        // Script begin
        $output->writeln('Import start.');

        $this->stopwatch->start('app:student:import');

        $this->importData($input, $output);

        // Script end
        $stopwatchEvent = $this->stopwatch->stop('app:student:import');

        // End Log
        $logComment = $this->getStopWatchReport($stopwatchEvent);
        $output->writeln('');

        $output->writeln(
            sprintf(
                '<info>%s</info> : <comment>%s</comment>',
                'Script - "Import student successfully done"', $logComment
            )
        );

        gc_collect_cycles();
    }

    /**
     * Get the stopwatch - Report.
     *
     * @param StopwatchEvent $stopwatchEvent
     *
     * @return string
     */
    protected function getStopWatchReport(StopwatchEvent $stopwatchEvent)
    {
        $executionTime = $stopwatchEvent->getDuration() / 1000;
        $memoryUsage   = $stopwatchEvent->getMemory() / (1024 * 1024);

        $reportStopWatch = sprintf('Execution time: %ss - Memory usage: %s Mo', $executionTime, $memoryUsage);

        return $reportStopWatch;
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    private function importData(InputInterface $input, OutputInterface $output)
    {
        $fileName = $input->getArgument('filename');
        //try {
            $importDirData = $this->container->getParameter('twig.default_path') . '/import/etudiant';
            if(!is_dir($importDirData)){
                $filesystem = new Filesystem();
                $filesystem->mkdir($importDirData);
            }
            $csvFile              = $importDirData . '/' . $fileName;
            $csv                  = new ParseCSV();
            $csv->delimiter       = ',';
            $csv->input_encoding  = 'utf-8';
            $csv->output_encoding = 'utf-8';
            $csv->parse($csvFile);

            // Getting parameters
            $nbItems = count($csv->data);
            // Initializes the progress bar
            $progressBar = new ProgressBar($output, $nbItems);
            $progressBar->setFormat('debug');
            $progressBar->start();
            $total = 0;
            foreach ($csv->data as $k => $rows) {
                $progressBar->advance();
                $mentionDimunitif = trim($rows['mention']);
                $mention          = $this->mentionManager->getRepository()->findOneByDiminutif($mentionDimunitif);
                if ($mention) {
                    
                    $exist = $this->checkExistingData($rows);
                    if($exist) ++$total;
                    // $user = $this->createUser($rows);
                    // $this->createStudent($user, $rows, $mention);
                }
            }
            dump($total);
        // } catch (\Exception $e) {
        //     $output->writeln('');
        //     $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
        // }
    }

    private function checkExistingData(array $data) {
        $nom = $data['nom'];
        $prenom = $data['prenom'];
        $matricule = $data['matricule'];
        $etudiant = $this->etudiantManager->loadOneBy(['immatricule' => $matricule]);
        if(!$etudiant && $matricule) {
            dump('NOT EXIST '. $matricule . ' ' . $data['mention'] . ' ' . $data['niveau'] . ' ' . $data['status']. ' ' . $nom. ' ' . $prenom);
            return true;
        }

        return false;
    }

    /**
     * @param array $data
     *
     * @return \App\Entity\User
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function createUser(array $data)
    {
        // $email = $data['email'];
        //emailize nom + prenom
        $email = $data['email'];
        if ($user = $this->userManager->getRepository()->findOneByEmail($email)) {
            dump($user->getEmail() . ' exist');
            //return $user;
        } else {
            $user  = new User();
        }
        $login = $data['matricule'];
        // $pwd   = $data['mot_de_passe'];
        $pwd   = '123456789';
        $user
            ->setFirstname($data['first_name'])
            ->setLastname($data['last_name'])
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
        $user->setFromImport(1);

        // check role
        $role = $this->roleManager->getRepository()->findOneByCode('ROLE_ETUDIANT');
        if (!$role) {
            $role = $this->roleManager->create('Etudiant', 'ROLE_ETUDIANT');
        }
        // check profil
        $profil = $this->profilManager->findOneByRoleCode('ROLE_ETUDIANT');
        if (!$profil) {
            $profil = $this->profilManager->create('Etudiant', $role);
        }
        $user->setProfil($profil);
        $this->userManager->save($user);

        return $user;
    }

    /**
     * @param \App\Entity\User    $user
     * @param array               $data
     * @param \App\Entity\Mention $mention
     *
     * @return \App\Entity\Etudiant
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function createStudent(User $user, array $data, Mention $mention)
    {
        // create student
        /** @var \App\Entity\Etudiant $student */
        if($student = $this->etudiantManager->loadOneBy(['user' => $user])){
            dump('student exist en base'. $student->getFirstName());
            //return $student;
        } else {
            $student = $this->etudiantManager->createObject();
        }
        $student->setStatus(1);
        $student->setFirstName($user->getFirstName());
        $student->setLastName($user->getLastName());
        $student->setEmail($user->getEmail());
        $student->setUser($user);
        $student->setImmatricule(trim($data['matricule']));
        // $student->setAddress(trim($data['address']));
        // $student->setReligion(trim($data['religion']));
        // $student->setCinNum(trim($data['cin_num']));
        // $student->setNationality(trim($data['nationality']));
        // $student->setPhone(trim($data['phone']));
        // $student->setBirthPlace(trim($data['lieu_de_naissance']));

        // mentions
        $student->setMention($mention);
        // niveau
        $niveau = $this->niveauManager->getRepository()->findOneByCode(trim($data['niveau']));
        if ($niveau) {
            $student->setNiveau($niveau);
        }
        // parcours
        $parcoursDiminutif = $data['parcours'] ?? 'PAR';
        $parcours          = $this->parcoursManager->getRepository()->findOneByDiminutif(trim($parcoursDiminutif));
        if ($parcours) {
            $student->setParcours($parcours);
        }

        // civilite
        $civilityAliases = [
            'M'        => 'MR',
            'MR'       => 'MR',
            'Mlle'     => 'MLLE',
            'Monsieur' => 'MR',
            'Mme'      => 'MME'
        ];
        $civilityParam   = isset($data['civility']) && !empty(trim($data['civility'])) ? trim($data['civility']) : 'MR';
        $civilityAlias   = $civilityAliases[$civilityParam] ?? trim($data['civility']);
        $civility        = $this->civilityManager->getRepository()->findOneByAlias($civilityAlias);
        if ($civility) {
            $student->setCivility($civility);     
        }      
        $this->etudiantManager->save($student);

        return $student;
    }
}
