<?php

namespace App\Command;

use App\Entity\ConcoursCandidature;
use App\Entity\Mention;
use App\Entity\Niveau;

use App\Manager\ConcoursCandidatureManager;
use App\Services\AnneeUniversitaireService;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Question\Question;

class ConcoursMatriculeGenerateCommand extends Command
{

    /**
     * @var AnneeUniversitaireService
     */
    private $anneeUnivService;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var concCandidatureManager
     */
    private $concCandidatureManager;

    public function __construct(
        AnneeUniversitaireService   $anneeUnivService,
        ConcoursCandidatureManager  $concCandidatureManager,
        EntityManagerInterface      $em
    ) {
        
        $this->em                = $em;
        $this->anneeUnivService = $anneeUnivService;
        $this->concCandidatureManager = $concCandidatureManager;
        parent::__construct();
    }

    protected static $defaultName = 'app:concours:generate_matricule';

    protected function configure()
    {
        $this
            ->setDescription('Generate candidature matricule.')
            // ->addArgument('firstname', InputArgument::REQUIRED, 'The firstname')
            // ->addArgument('lastname', InputArgument::REQUIRED, 'The lastname')
            // ->addArgument('email', InputArgument::REQUIRED, 'The email')
            // ->addArgument('login', InputArgument::REQUIRED, 'The login')
            // ->addArgument('password', InputArgument::REQUIRED, 'The password')
            // ->addArgument('mention', InputArgument::REQUIRED, 'Id of mention')
            // ->addOption('inactive', null, InputOption::VALUE_NONE, 'Set the teacher as inactive')
            ->setHelp(
                implode(
                    "\n", [
                            'The <info>app:concours:generate_matricule</info> command generate concours matricule'
                        ]
                )
            )
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questions = [];

        // if (!$input->getArgument('firstname')) {
        //     $question = new Question('Please enter the firstname:');
        //     $question->setValidator(
        //         function ($firstname)
        //         {
        //             if (empty($firstname)) {
        //                 throw new \Exception('Firstname can not be empty');
        //             }

        //             return $firstname;
        //         }
        //     );
        //     $questions['firstname'] = $question;
        // }

        // foreach ($questions as $name => $question) {
        //     $answer = $this->getHelper('question')->ask($input, $output, $question);
        //     $input->setArgument($name, $answer);
        // }
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        try {
            $io = new SymfonyStyle($input, $output);

            $this->generateMatricule($input);

            $io->success(
                sprintf(
                    'Matricule regenerate'
                )
            );

        } catch (\Exception $e) {
            echo $e->getMessage() . '<br>';
            echo $e->getFile() . '<br>';
            echo $e->getLine() . '<br>';
        }

        return 0;
    }

    private function generateMatricule(InputInterface $input)
    {
        $candidatRepo = $this->em->getRepository(ConcoursCandidature::class);
        // $email   = $input->getArgument('email');

        $currentAnneUniv = $this->anneeUnivService->getCurrent();
        $candidatesAll = $this->concCandidatureManager->findAllByAnneeUniv($currentAnneUniv);        
        $initMentionDiminutif = "";
        $matNumlength = 4;
        $matriculeNumber = "";
        $currentMatricule = 1;
        foreach($candidatesAll as $candidate) {
            $currentCandidate = $this->concCandidatureManager->load($candidate['candidatureId']);
            $numberPrefix = "";
            if($candidate['diminutif'] != $initMentionDiminutif) {
                $currentMatricule = 1;
                $matriculeNumber = "";
            }
            $initMentionDiminutif = $candidate['diminutif'];
            $lastIdLen=strlen($currentMatricule);
            for($index=$lastIdLen; $lastIdLen<$matNumlength; $lastIdLen++) {
                $numberPrefix .= "0";
            }
            $matriculeNumber = $initMentionDiminutif . $numberPrefix .$currentMatricule;
            dump($matriculeNumber);
            $currentCandidate->setImmatricule($matriculeNumber);
            $this->em->persist($currentCandidate);
            $currentMatricule++;
        }

        $this->em->flush();
    }

    private function generateMatriculeM1(InputInterface $input)
    {
        $candidatRepo = $this->em->getRepository(ConcoursCandidature::class);
        // $email   = $input->getArgument('email');

        $currentAnneUniv = $this->anneeUnivService->getCurrent();
        $candidatesAll = $this->concCandidatureManager->findAllByAnneeUniv($currentAnneUniv);        
        $initMentionDiminutif = "";
        $matNumlength = 4;
        $matriculeNumber = "";
        $currentMatricule = 1;
        foreach($candidatesAll as $candidate) {
            $currentCandidate = $this->concCandidatureManager->load($candidate['candidatureId']);
            if($currentCandidate->getNiveau()->getLibelle() === Niveau::M1_CODE) {
                do {
                    $numberPrefix = "";
                    if($candidate['diminutif'] != $initMentionDiminutif) {
                        $currentMatricule = 1;
                        $matriculeNumber = "";
                    }
                    $initMentionDiminutif = $candidate['diminutif'];
                    $lastIdLen=strlen($currentMatricule);
                    for($index=$lastIdLen; $lastIdLen<$matNumlength; $lastIdLen++) {
                        $numberPrefix .= "0";
                    }
                    $matriculeNumber = $initMentionDiminutif . $numberPrefix .$currentMatricule;
                    $candidatesFromMention = $candidatRepo->findBy(
                        [
                            'immatricule' => $matriculeNumber, 'anneeUniversitaire' => $currentAnneUniv
                        ], []);
                    $currentMatricule++;
                } while(count($candidatesFromMention) > 0);
                dump($matriculeNumber);
                $currentCandidate->setImmatricule($matriculeNumber);
                $this->em->persist($currentCandidate);
                $currentMatricule++;
            }
        }

        $this->em->flush();
    }
}