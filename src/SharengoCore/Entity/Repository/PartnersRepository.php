<?php

namespace SharengoCore\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\Partners;

class PartnersRepository extends EntityRepository
{
    /**
     * Check if customer belog to a specific partner
     * 
     * @param Partners $partner
     * @param Customers $customer
     * @return boolean
     */
    public function isBelongCustomerPartner(Partners $partner, Customers $customer)
    {
        $result = false;

        $em = $this->getEntityManager();
        $query = $em->createQuery('SELECT count(pc.id) ".
            "FROM \SharengoCore\Entity\PartnersCustomers pc ".
            "WHERE pc.partner = :partner AND pc.customer = :customer AND pc.disabledTs IS NULL');

        $query->setParameter('partner', $partner);
        $query->setParameter('customer', $customer);

        if($query->getSingleScalarResult()>0) {
            $result = true;
        }
        return $result;
    }

    /**
     * Deactivate link between partner and customer, and disable the contract width partner
     * 
     * @param Partners $partner
     * @param Customers $customer
     * @return type
     */
    public function deactivatePartnerCustomer(Partners $partner, Customers $customer) {
        $dql = "UPDATE \SharengoCore\Entity\PartnersCustomers pc " .
                "SET pc.disabledTs = :disabledTs " .
                "WHERE disabledTs IS NULL AND pc.partner = :partner AND pc.customer = :customer";

        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('disabledTs', new \DateTime("now"));
        $query->setParameter('partner', $partner);
        $query->setParameter('customer', $customer);
        $query->execute();

        $dql = "UPDATE \Cartasi\Entity\Contracts co " .
                "SET po.disabledDate = :disabledDate " .
                "WHERE disabledDate IS NULL AND co.partner = :partner AND co.customer = :customer";

        $query->setParameter('disabledDate', new \DateTime("now"));
        $query->setParameter('partner', $partner);
        $query->setParameter('customer', $customer);

        return $query->execute();
    }
}
