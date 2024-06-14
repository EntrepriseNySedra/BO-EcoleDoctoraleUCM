<?php

namespace App\Command;

use App\Entity\Civility;
use App\Entity\Etudiant;
use App\Entity\Mention;
use App\Entity\Niveau;

use App\Manager\EtudiantManager;
use App\Services\AnneeUniversitaireService;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Question\Question;

class StudentMatriculeGenerateCommand extends Command
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
     * @var $studentManager
     */
    private $studentManager;

    public function __construct(
        AnneeUniversitaireService   $anneeUnivService,
        EtudiantManager  $studentManager,
        EntityManagerInterface      $em
    ) {
        
        $this->em                = $em;
        $this->anneeUnivService = $anneeUnivService;
        $this->studentManager = $studentManager;
        parent::__construct();
    }

    protected static $defaultName = 'app:student:generate_matricule';

    protected function configure()
    {
        $this
            ->setDescription('Generate student matricule.')
            ->setHelp(
                implode(
                    "\n", [
                            'The <info>app:student:generate_matricule</info> command generate student matricule'
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
        $anneeUniv = $this->anneeUnivService->getCurrent()->getAnnee() - 1;
        $anneeUnivDiminutif = substr($anneeUniv, 2);
        $studentAll = $this->studentManager->loadBy([], ['createdAt' => 'ASC']);    
        $initMentionDiminutif = "";
        $matricule = "";
        $currentMatNumber = 6000;
        $matListTemp = [];
        foreach($studentAll as $student) {
            $studentMention = substr($student->getMention()->getDiminutif(), 0, 2);
            $studentGender = "F";
            if(strtolower(trim(Civility::MONSIEUR) ) === strtolower(trim($student->getCivility()->getLibelle())))
                $studentGender = "H";
            do {
                if($studentMention != $initMentionDiminutif) {
                    $matricule = "";
                    $currentMatNumber = 6000;
                }
                $matricule = $studentMention . $anneeUnivDiminutif . $studentGender . $currentMatNumber;
                $matriculeTemp = $studentMention . $anneeUnivDiminutif . $currentMatNumber;
                // $dulicateMatricules = $this->studentManager->loadBy(
                //     [
                //         'immatricule' => $matricule
                //     ], []);
                $initMentionDiminutif = $studentMention;
                $currentMatNumber++;
            //} while(count($dulicateMatricules) > 0);
            } while(in_array($matriculeTemp, $matListTemp));
            $matListTemp[] = $matriculeTemp;
            dump($matricule);
            $student->setImmatricule($matricule);
            $this->em->persist($student);
        }

        $this->em->flush();
    }

    private function generateMatriculeOld(InputInterface $input)
    {
        $studentAll = $this->studentManager->loadBy([], ['createdAt' => 'ASC']);    
        $matNumlength = 9;
        $matricule = "";
        $matriculeNumber = 5999;
        foreach($studentAll as $student) {
            $matriculeOffset = "";
            $studentMentionDiminutif = $student->getMention()->getDiminutif();
            $currentNumLen=strlen($matriculeNumber);
            for($index=$currentNumLen; $currentNumLen<$matNumlength; $currentNumLen++) {
                $matriculeOffset .= "0";
            }
            $matricule = $studentMentionDiminutif . $matriculeOffset .$matriculeNumber;
            $student->setImmatricule($matricule);
            $this->em->persist($student);
            $matriculeNumber++;
        }

        $this->em->flush();
    }
}