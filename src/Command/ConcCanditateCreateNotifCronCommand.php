<?php

namespace App\Command;

use App\Entity\ConcoursCandidature;
use App\Entity\Civility;
use App\Entity\Etudiant;
use App\Entity\Mention;
use App\Entity\Niveau;

use App\Manager\ConcoursCandidatureManager;
use App\Manager\ConcoursConfigManager;
use App\Services\Mailer;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\DependencyInjection\ContainerInterface; // <- Add this

class ConcCanditateCreateNotifCronCommand extends Command
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

    public function __construct(
        ConcoursCandidatureManager  $concCandidatureManager,
        EntityManagerInterface      $em,
        ParameterBagInterface $parameter,
        \Twig\Environment $template,
        ConcoursConfigManager           $concoursConfManager,
        Mailer $mailer
    ) {
        
        $this->em                = $em;
        $this->parameter = $parameter;
        $this->mailer = $mailer;
        $this->concCandidatureManager = $concCandidatureManager;
        $this->concoursConfManager = $concoursConfManager;
        $this->template = $template;
        parent::__construct();
    }

    protected static $defaultName = 'app:cron:test';

    protected function configure()
    {
        $this
            ->setDescription('Generate student matricule.')
            ->setHelp(
                implode(
                    "\n", [
                            'The <info>app:cron:test</info> command generate student matricule'
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
        dump(date('d/m/Y H:i:s'));
        $siteConfig = $this->parameter->get('site');
        $mailerConfig = $this->parameter->get('mailer');  
        $concoursConf = $this->concoursConfManager->loadOneBy([]);
        $concoursAnneeUniv = $concoursConf->getAnneeUniversitaire();
        $candidatures = $this->concCandidatureManager->loadby(
            [
                'anneeUniversitaire' => $concoursAnneeUniv,
                'status' => ConcoursCandidature::STATUS_CREATED,
                'email_notification' => NULL
            ]
        );
        try {
            foreach($candidatures as $candidature) {        
                $params = [
                    "sender"      => $mailerConfig['smtp_username'],
                    "pwd"         => $mailerConfig['smtp_password'],
                    "sendTo"      => [$candidature->getEmail(), 'onja.rails@gmail.com'],
                    "subject"     => 'Confirmation candidature',
                    "senderName"  => $siteConfig['name'],
                    "senderEmail" => $siteConfig['contact_email'],
                ];
                $html = $this->template->render(
                    "frontend/concours/email-confirmation-candidature.html.twig",
                    [
                        'fullname' => $candidature->getFullname(),
                        'site'     => $siteConfig
                    ]
                );
                // Send email
                //$this->mailer->sendMail($params, $html);
                $candidature->setEmailNotification(ConcoursCandidature::NOTIFIED);
                $this->em->persist($candidature);
            }
            $this->em->flush();
            $io = new SymfonyStyle($input, $output);
            $io->success(
                "Create candidature notification sent"
            );
        } catch (\Exception $e) {
            echo $e->getMessage() . '/n';
            echo $e->getFile() . '<br>';
            echo $e->getLine() . '<br>';
        }

        return 0;
    }

}