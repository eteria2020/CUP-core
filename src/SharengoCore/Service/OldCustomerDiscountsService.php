<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Customers;
use SharengoCore\Entity\OldCustomerDiscount;

use Doctrine\ORM\EntityManager;
use Zend\View\Helper\Url;
use Zend\I18n\Translator\TranslatorInterface as Translator;

class OldCustomerDiscountsService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var EmailService
     */
    private $emailService;

    /**
     * @var Url
     */
    private $urlHelper;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var string
     */
    private $host;

    public function __construct(
        EntityManager $entityManager,
        EmailService $emailService,
        Url $urlHelper,
        Translator $translator,
        $host
    ) {
        $this->entityManager = $entityManager;
        $this->emailService = $emailService;
        $this->urlHelper = $urlHelper;
        $this->translator = $translator;
        $this->host = $host;
    }

    /**
     * @param Customers
     */
    public function disableCustomerDiscount(Customers $customer, $persist = true, $sendEmail = true)
    {
        $this->entityManager->beginTransaction();

        try {
            $discountRate = $customer->getDiscountRate();

            $oldDiscount = new OldCustomerDiscount(
                $customer,
                $discountRate,
                date_create()
            );

            $customer->setDiscountRate(0);
            if ($customer->hasDiscountStatus()) {
                $discountStatus = $customer->discountStatus();
                $discountStatus = $discountStatus->updateStatus('0|0');
            }

            if ($persist) {
                $this->entityManager->persist($customer);
                $this->entityManager->persist($oldDiscount);
                if ($customer->hasDiscountStatus()) {
                    $this->entityManager->persist($discountStatus);
                }
                
                $this->entityManager->flush();
            }

//            if ($sendEmail && $customer->getFirstPaymentCompleted()) {
//                // we send the mail only to the customers who payed the first payment
//                // the others have their discount cancelled without notifications
//                $this->sendEmail($customer->getEmail(), $customer->getName(), $customer->getLanguage());
//            }

            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();

            throw $e;
        }
    }

    private function sendEmail($email, $name, $language)
    {
        $urlHelper = $this->urlHelper;
        $mail = $this->emailService->getMail(18, $language);
        $content = sprintf(
            $mail->getContent(),
            $name
        );
        //file_get_contents(__DIR__.'/../../../view/emails/disable_discount_it-IT.html'),
        $attachments = [
            //'banner.jpg' => $this->host . '/images/banner_discount.jpg'
        ];

        $this->emailService->sendEmail(
            $email,
            $mail->getSubject(), //'OGGI PUOI RICALCOLARE LO SCONTO CHE TI SPETTA',
            $content,
            $attachments
        );
    }

    public function notifyCustomer($customer)
    {
        $mail = $this->emailService->getMail(17, $customer->getLanguage());
        $content = sprintf(
            $mail->getContent(),
            $customer->getName()
        );
        //file_get_contents(__DIR__.'/../../../view/emails/notify_disable_discount_it-IT.html'),
        $attachments = [
            //'banner.jpg' => $this->host . '/images/banner_notify_discount.jpg'
        ];

        $this->emailService->sendEmail(
            $customer->getEmail(),
            $mail->getSubject(), //'Tra una settimana ri-scopri lo sconto che ti spetta',
            $content,
            $attachments
        );
    }
}
