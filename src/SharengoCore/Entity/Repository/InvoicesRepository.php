<?php

namespace SharengoCore\Entity\Repository;

class InvoicesRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param Customers $customer
     * @return mixed
     */
    public function findDistinctDatesForCustomerByMonth($customer)
    {
        $em = $this->getEntityManager();

        $dql = "SELECT DISTINCT i.invoiceDate / 100
        FROM \SharengoCore\Entity\Invoices i
        WHERE i.customer = :customer
        ORDER BY i.invoiceDate / 100 DESC";

        $query = $em->createQuery($dql);
        $query->setParameter('customer', $customer);

        return $query->getResult();
    }

    public function findInvoicesByCustomerWithDate($customer, $date)
    {
        $em = $this->getEntityManager();

        $dql = "SELECT i
        FROM \SharengoCore\Entity\Invoices i
        WHERE i.customer = :customer
        AND i.invoiceDate = :invoiceDate";

        $query = $em->createQuery($dql);
        $query->setParameter('customer', $customer);
        $query->setParameter('invoiceDate', $date);

        return $query->getResult();
    }

    public function findInvoicesByCustomerWithDateNoDay($customer, $date)
    {
        $em = $this->getEntityManager();

        $dql = "SELECT i
        FROM \SharengoCore\Entity\Invoices i
        WHERE i.customer = :customer
        AND i.invoiceDate <= :dateMax
        AND i.invoiceDate >= :dateMin";

        $query = $em->createQuery($dql);
        $query->setParameter('customer', $customer);
        $query->setParameter('dateMin', $date * 100);
        $query->setParameter('dateMax', ($date + 1) * 100);

        return $query->getResult();
    }
}
