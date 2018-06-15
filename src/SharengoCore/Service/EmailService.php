<?php

namespace SharengoCore\Service;

use Doctrine\ORM\EntityManager;
use Zend\Mail\Message;
use Zend\Mail\Transport\TransportInterface;
use Zend\Mime;
use Zend\Mvc\I18n\Translator;
use SharengoCore\Entity\Mails;
use SharengoCore\Entity\Customers;

use SharengoCore\Entity\Repository\MailsRepository;

class EmailService
{
    /**
     * @var EntityManager
     */
    private $entityManager;
    /**
     * @var \Zend\Mail\Transport\TransportInterface
     */
    private $emailTransport;
    
    /**
     * @var array
     */
    private $emailSettings;
    
     /**
     * @var MailsRepository
     */
    private $mailsRepository;
    
    public function __construct(
        EntityManager $entityManager,
        TransportInterface $emailTransport,   
        array $emailSettings,  
        MailsRepository $mailsRepository
    ) {
        $this->entityManager = $entityManager;
        $this->emailTransport = $emailTransport;
        $this->emailSettings = $emailSettings;
        $this->mailsRepository = $mailsRepository;
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
    public function sendEmail($to, $subject, $content, array $attachments = [])
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

        if (is_array($to)) {
            $to = array_map('strtolower', $to);
        } else {
            $to = strtolower($to);
        }

        $mail = (new Message())
            ->setFrom($this->emailSettings['from'])
            ->setTo($to)
            ->setSubject($subject)
            ->setReplyTo($this->emailSettings['replyTo'])
            ->setBcc($this->emailSettings['registrationBcc'])
            ->setBody($mimeMessage)
            ->setEncoding("UTF-8");
        $mail->getHeaders()->addHeaderLine('X-Mailer', $this->emailSettings['X-Mailer']);

        $this->emailTransport->send($mail);
    }
    
    /*
     * get Mail from database
     *
     * @param integer $category
     * @param string $language
     * 
     * return Mails $mail
     */
    
    public function getMail($category, $language) {
        $mail = $this->mailsRepository->findMails($category, $language);
        $find = count($mail);
        
        if ($find == 0) { 
            $mail = $this->mailsRepository->findMails($category, "it");
        }
        return $mail[0];
    }

}
