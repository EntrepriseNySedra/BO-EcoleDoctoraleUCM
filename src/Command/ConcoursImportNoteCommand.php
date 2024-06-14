<?php

namespace App\Command;

use App\Entity\ConcoursNotes;
use App\Entity\Mention;
use App\Manager\ConcoursManager;
use App\Manager\ConcoursCandidatureManager;
use App\Manager\ConcoursMatiereManager;
use App\Manager\MentionManager;
use App\Manager\NiveauManager;
use App\Manager\ParcoursManager;
use App\Tools\ParseCSV;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;

class ConcoursImportNoteCommand extends Command
{
    static $FILE_IMPORT = 'concours_notes.csv';

    protected static $defaultName = 'app:concours:import_note';

    protected static $defaultDescription = 'Import concours note from csv file';

    /**
     * @var \Symfony\Component\Stopwatch\Stopwatch
     */
    private $stopwatch;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;
    private $doctrine;
    private $em;

    /**
     * @var \App\Manager\ConcoursManager
     */
    private $concoursManager;

    /**
     * @var \App\Manager\ConcoursCandidatureManager
     */
    private $concCandidatureManager;

    private $concoursMatiereManager;

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
        MentionManager $mentionManager,
        NiveauManager $niveauManager,
        ParcoursManager $parcoursManager,
        ConcoursCandidatureManager $concCandidatureManager,
        ConcoursManager $concoursManager,
        ConcoursMatiereManager $concoursMatiereManager
    ) {
        parent::__construct();

        $this->stopwatch                = $stopwatch;
        $this->container                = $container;
        $this->concCandidatureManager   = $concCandidatureManager;
        $this->concoursManager          = $concoursManager;
        $this->concoursMatiereManager   = $concoursMatiereManager;
        $this->mentionManager           = $mentionManager;
        $this->niveauManager            = $niveauManager;
        $this->parcoursManager          = $parcoursManager;
        $this->container                = $container;
        $this->doctrine  = $this->container->get('doctrine');
        $this->em  = $this->doctrine->getManager();
        //dump($this->doctrine);die;
    }

    /**
     * @return void
     */
    protected function configure() : void
    {
        $this->addArgument('filename', InputArgument::REQUIRED, 'Enter file name:');
        $this->setDescription(self::$defaultDescription)
             ->setHelp(
                 implode(
                     "\n", [
                             'The <info>app:concours:import_note</info> command import concours notes from a csv file'
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

        $this->stopwatch->start('app:concours:import_note');

        $this->importData($input, $output);

        // Script end
        $stopwatchEvent = $this->stopwatch->stop('app:concours:import_note');

        // End Log
        $logComment = $this->getStopWatchReport($stopwatchEvent);
        $output->writeln('');

        $output->writeln(
            sprintf(
                '<info>%s</info> : <comment>%s</comment>',
                'Script - "Import notes successfully done"', $logComment
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
        try {
            $csvFile              = $this->container
                                        ->getParameter('twig.default_path') . '/import/concours/notes/' . $fileName;
            if(!is_file($csvFile)) $output->writeln('File not found.');
            $csv                  = new ParseCSV();
            $csv->delimiter       = ';';
            $csv->input_encoding  = 'utf-8';
            $csv->output_encoding = 'utf-8';
            $csv->parse($csvFile);

            // Getting parameters
            $nbItems = count($csv->data);

            // Initializes the progress bar
            $progressBar = new ProgressBar($output, $nbItems);
            $progressBar->setFormat('debug');
            $progressBar->start();
            foreach ($csv->data as $k => $rows) {      
                // dump(floatval($rows['philosophie']));          
                $progressBar->advance();

                $itemKeys = array_keys($rows);
                $immatricule = trim($rows['matricule']);
                $concoursId = trim($rows['concours_id']);
                $mentionDimunitif = trim($rows['mention']);
                // $matiere = trim($rows['matiere']);
                $candidat = $this->concCandidatureManager->loadOneby([
                    'immatricule' => $immatricule
                ], []);

                $concours = $this->concoursManager->load($concoursId);
                $mention = $this->mentionManager->loadOneBy(
                    [
                        'diminutif' => $mentionDimunitif
                    ]
                );
                foreach($itemKeys as $keyName) {
                    $matiere = $this->concoursMatiereManager->loadOneby(
                        [
                            'libelle' => utf8_encode(strval($keyName)),
                            'concours' => $concours,
                            'mention' => $mention
                        ], []
                    );
                    if($candidat && $matiere) {
                        $today = new \DateTime();
                        $note = new ConcoursNotes();
                        $note->setConcoursCandidature ($candidat);
                        $note->setConcoursMatiere ($matiere);
                        $note->setConcours ($concours);
                        $note->setNote (  floatval( str_replace(',', '.', $rows[$keyName]))   );
                        $note->setCreatedAt ($today);
                        $note->setUpdatedAt ($today);
                        $this->em->persist($note);
                    }
                }                            
            }
            $this->em->flush();
        } catch (\Exception $e) {
            $output->writeln('');
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
        }
    }
}
