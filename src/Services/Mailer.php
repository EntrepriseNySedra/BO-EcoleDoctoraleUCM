<?php

/**
 * Service that can be used to send email using basic swift mailer transport
 */

namespace App\Services;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class Mailer
{
    /**
     * @var array $emailConfig
     */
    private $emailConfig;

    private $_params;

    /**
     * Mailer constructor
     *
     * @param ParameterBagInterface $params
     */
    public function __construct(ParameterBagInterface $params)
    {
        $this->emailConfig = $params->get('mailer');
        $this->_params     = $params;
    }

    public function sendMail(array $mailInfo, string $html, array $files = [], array $cc = [], array $bcc = [])
    {
        $emailConfig = $this->emailConfig;

        $smtpHost     = $emailConfig['smtp_host'];
        $smtpPort     = $emailConfig['smtp_port'];
        $smtpCert     = $emailConfig['smtp_cert'];
        $smtpUsername = $emailConfig['smtp_username'];
        $smtpPassword = $emailConfig['smtp_password'];

        $https['ssl']['verify_peer']      = false;
        $https['ssl']['verify_peer_name'] = false;

        $transport = (new \Swift_SmtpTransport($smtpHost, $smtpPort, $smtpCert))->setStreamOptions($https);

        if (!empty($smtpUsername) && !empty($smtpPassword)) {
            $transport->setUsername($smtpUsername)
                      ->setPassword($smtpPassword)
            ;
        }

        $swiftMailer = new \Swift_Mailer($transport);

        $senderEmail = 'contact@ucm.mg';
        if (!empty($mailInfo['senderEmail'])) {
            $senderEmail = $mailInfo['senderEmail'];
        }

        $message = (new \Swift_Message($mailInfo['subject']))
            ->setFrom([$senderEmail => $mailInfo['senderName']])
            ->setTo($this->_params->get('delivery_addresses') ? : $mailInfo['sendTo'])
            ->setBody($html, 'text/html')
        ;

        if (!empty($cc)) {
            foreach ($cc as $email) {
                $message->addCc($this->_params->get('delivery_addresses') ? : $email);
            }
        }

        if (!empty($bcc)) {
            foreach ($bcc as $email) {
                $message->addBcc($this->_params->get('delivery_addresses') ? : $email);
            }
        }

        foreach ($files as $file) {
            $message->attach(\Swift_Attachment::fromPath($file));
        }

        $swiftMailer->send($message);
    }
}
