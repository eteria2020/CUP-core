<?php

namespace SharengoCore\Entity\Repository;
use Doctrine\ORM\Query;
use SharengoCore\Entity\Invoices;


/**
 * CardsRepository
 *
 */
class BonusPackagePaymentRepository extends \Doctrine\ORM\EntityRepository
{

    public function findBonusPackagePaymentByInvoice(Invoices $invoice) {
        $em = $this->getEntityManager();

        $dql = "SELECT ep
            FROM SharengoCore\Entity\BonusPackagePayment bpp
            WHERE bpp.invoice = :invoice";

        $query = $em->createQuery($dql);

        $query->setParameter('invoice', $invoice);

        return $query->getResult();
    }

}