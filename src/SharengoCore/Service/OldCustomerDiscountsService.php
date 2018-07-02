<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Customers;
use SharengoCore\Entity\OldCustomerDiscount;
use SharengoCore\Entity\Repository\OldCustomerDiscountsRepository;

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
    
    /**
     * @var OldCustomerDiscountsRepository
     */
    private $oldCustomerDiscountsRepository;
    

    public function __construct(
        EntityManager $entityManager,
        EmailService $emailService,
        Url $urlHelper,
        Translator $translator,
        $host,
        $oldCustomerDiscountsRepository
    ) {
        $this->entityManager = $entityManager;
        $this->emailService = $emailService;
        $this->urlHelper = $urlHelper;
        $this->translator = $translator;
        $this->host = $host;
        $this->oldCustomerDiscountsRepository = $oldCustomerDiscountsRepository;
    }

    /**
     * Disable the discount because it's expire.
     * Set the customer discount rate to 0 and inser a new row in OldCustomerDiscount.
     * 
     * @param Customers $customer
     * @param type $persist
     * @param type $sendEmail
     * @throws \Exception
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

    /**
     * Renew the customer's discount after a year.
     *
     * @param Customers $customer
     * @param type $persist
     * @param type $sendEmail
     * @param type $newDiscount
     * @throws \Exception
     */
    public function renewCustomerDiscount(Customers $customer, $persist = true, $sendEmail = true, $newDiscount = 0)
    {
        $this->entityManager->beginTransaction();

        try {
            $discountRate = $customer->getDiscountRate();

            $oldDiscount = new OldCustomerDiscount(
                $customer,
                $discountRate,
                date_create()
            );

            $customer->setDiscountRate($newDiscount);
            if ($customer->hasDiscountStatus()) {
                $discountStatus = $customer->discountStatus();
                if($newDiscount == 0) {
                    $discountStatus = $discountStatus->updateStatus('0|0');
                } else {
                    $discountStatus = $discountStatus->updateStatus('9|'.$newDiscount);
                }
            }

            if ($persist) {
                $this->entityManager->persist($customer);
                $this->entityManager->persist($oldDiscount);
                if ($customer->hasDiscountStatus()) {
                    $this->entityManager->persist($discountStatus);
                }

                $this->entityManager->flush();
            }

            if ($sendEmail && $customer->getFirstPaymentCompleted()) {
                // we send the mail only to the customers who payed the first payment
                // the others have their discount cancelled without notifications
                $this->sendEmail($customer->getEmail(), $customer->getName(), $customer->getLanguage());
            }

            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();

            throw $e;
        }
    }

    /**
     * Send an email.
     * @param type $email
     * @param type $name
     * @param type $language
     */
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
            $mail->getSubject(),
            $content,
            $attachments
        );
    }

    /**
     * Notify that the discount is going to expire (next week).
     * @param type $customer
     */
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
    
    public function allOldDiscounts(Customers $customer) {
        return $this->oldCustomerDiscountsRepository->findBy([
            'customer' => $customer
        ]);
    }
}
