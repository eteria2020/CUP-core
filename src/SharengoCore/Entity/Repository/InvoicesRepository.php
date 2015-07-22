<?php

namespace SharengoCore\Entity\Repository;

class InvoicesRepository extends \Doctrine\ORM\EntityRepository
{

    /**
     * @param \SharengoCore\Entity\Customers $customer
     * @return mixed
     */
    public function findByCustomerFirstPayment($customer)
    {
        $em = $this->getEntityManager();

        $dql = "SELECT i FROM \SharengoCore\Entity\Invoices i
            WHERE i.customer = :customerId
            AND i.type = 'FIRST_PAYMENT'";

        $query = $em->createQuery($dql);
        $query->setParameter('customerId', $customer->getId());
        return $query->getResult();
    }
}
