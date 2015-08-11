<?php

namespace SharengoCore\Service;

use Zend\Mail\Transport\InMemory as InMemoryTransport;
use Zend\Mail\Message;

class EmailServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EmailTransportInterface
     */
    private $emailTransport;

    /**
     * @var array
     */
    private $emailSettings;

    /**
     * @var EmailService
     */
    private $emailService;

    public function setUp()
    {
        $this->emailTransport = new InMemoryTransport();

        $this->emailSettings = [
            'from' => 'me@something.it',
            'replyTo' => 'otherMe@something.it',
            'registrationBcc' => 'anotherMe@something.it',
            'X-Mailer' => 'whoKnows'
        ];

        $this->emailService = new EmailService($this->emailTransport, $this->emailSettings);
    }

    public function testSendEmail()
    {
        $to = 'you@something.it';
        $subject = 'SUBJECT';
        $content = 'content';
        $attachments = [];

        $this->emailService->sendEmail($to, $subject, $content, $attachments);

        $email = $this->emailTransport->getLastMessage();

        $this->assertTrue($email->getFrom()->has($this->emailSettings['from']));
        $this->assertTrue($email->getTo()->has($to));
        $this->assertTrue($email->getBcc()->has($this->emailSettings['registrationBcc']));
        $this->assertTrue($email->getReplyTo()->has($this->emailSettings['replyTo']));
        $this->assertEquals($subject, $email->getSubject());
        $this->assertEquals($content, $email->getBodyText());
    }
}
