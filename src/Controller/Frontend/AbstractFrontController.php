<?php

namespace App\Controller\Frontend;

use App\Services\Mailer;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Description of AbstractFrontController.php.
 *
 * @package App\Controller\Frontend
 * @author Joelio
 */
class AbstractFrontController extends AbstractController
{

    /**
     * @var \App\Services\Mailer
     */
    private $mailer;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /** @var string */
    private $emailTpl;

    /** @var array */
    private $emailTplParams = [];

    /** @var string */
    private $emailSubject;

    /** @var string */
    private $sendTo;

    /** @var array */
    private $files = [];

    /**
     * AbstractFrontController constructor.
     *
     * @param \App\Services\Mailer     $mailer
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        Mailer $mailer,
        LoggerInterface $logger
    ) {
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    /**
     * Send email
     */
    public function sendMail()
    {
        try {
            $siteConfig   = $this->getParameter('site');
            $mailerConfig = $this->getParameter('mailer');

            $params = [
                "sender"      => $mailerConfig['smtp_username'],
                "pwd"         => $mailerConfig['smtp_password'],
                "sendTo"      => $this->sendTo,
                "subject"     => $this->emailSubject,
                "senderName"  => $siteConfig['name'],
                "senderEmail" => $siteConfig['contact_email'],
            ];

            $tplParams = [
                'site' => $siteConfig
            ];
            $html      = $this->renderView(
                $this->emailTpl,
                array_merge($this->emailTplParams, $tplParams)
            );

            // Send email
            $this->mailer->sendMail($params, $html, $this->files);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * @param string $emailTpl
     */
    public function setEmailTpl(string $emailTpl) : void
    {
        $this->emailTpl = $emailTpl;
    }

    /**
     * @param array $emailTplParams
     */
    public function setEmailTplParams(array $emailTplParams) : void
    {
        $this->emailTplParams = $emailTplParams;
    }

    /**
     * @param string $emailSubject
     */
    public function setEmailSubject(?string $emailSubject) : void
    {
        $this->emailSubject = $emailSubject;
    }

    /**
     * @param string $sendTo
     */
    public function setSendTo(string $sendTo) : void
    {
        $this->sendTo = $sendTo;
    }

    /**
     * @param array $files
     */
    public function setFiles(array $files) : void
    {
        $this->files = $files;
    }
}