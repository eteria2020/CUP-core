<?php

namespace SharengoCore\Entity\Repository;

class InvoicesRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param Customers $customer
     * @return mixed
     */
    public function findDistinctDatesForCustomer($customer)
    {
        $em = $this->getEntityManager();

        $dql = "SELECT DISTINCT (i.invoiceDate/100)
        FROM \SharengoCore\Entity\Invoices i
        WHERE i.customer = :customer";

        $query = $em->createQuery($dql);
        $query->setParameter('customer', $customer);

        return $query->getResult();
    }
}
