<?php

namespace SharengoCore\Service;

use Zend\Mail\Message;
use Zend\Mail\Transport\TransportInterface;
use Zend\Mime;
use Zend\Mvc\I18n\Translator;

class EmailService
{
    /**
     * @var \Zend\Mail\Transport\TransportInterface
     */
    private $emailTransport;

    /**
     * @var array
     */
    private $emailSettings;

    public function __construct(
        TransportInterface $emailTransport,
        array $emailSettings
    ) {
        $this->emailTransport = $emailTransport;
        $this->emailSettings = $emailSettings;
    }

    /**
     * sends an email with defaults parameters
     *
     * @param string $to recipient email address
     * @param string $subject email subject
     * @param string $content email body
     * @param array $attachments associative arrays with attachments
     *  the keys are the names of the attachments
     *  the values are the location of the attachments
     */
    public function sendEmail($to, $subject, $content, array $attachments)
    {
        $text = new Mime\Part($content);
        $text->type = Mime\Mime::TYPE_HTML;
        $text->charset = 'utf-8';

        $parts = [$text];

        foreach ($attachments as $name => $location) {
            $image = file_get_contents($location);
            $attachment = new Mime\Part($image);
            $attachment->type = Mime\Mime::TYPE_OCTETSTREAM;
            $attachment->disposition = Mime\Mime::DISPOSITION_ATTACHMENT;
            $attachment->encoding = Mime\Mime::ENCODING_BASE64;
            $attachment->filename = $name;
            $attachment->id = $name;

            $parts[] = $attachment;
        }

        $mimeMessage = new Mime\Message();
        $mimeMessage->setParts($parts);

        $mail = (new Message())
            ->setFrom($this->emailSettings['from'])
            ->setTo(strtolower($to))
            ->setSubject($subject)
            ->setReplyTo($this->emailSettings['replyTo'])
            ->setBcc($this->emailSettings['registrationBcc'])
            ->setBody($mimeMessage)
            ->setEncoding("UTF-8");
        $mail->getHeaders()->addHeaderLine('X-Mailer', $this->emailSettings['X-Mailer']);

        $this->emailTransport->send($mail);
    }
}
