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

        if(is_null($partner) || is_null($customer)) {
            return $result;
        }

        $em = $this->getEntityManager();
        $query = $em->createQuery('SELECT count(pc.id) FROM \SharengoCore\Entity\PartnersCustomers pc WHERE pc.partner = :partner AND pc.customer = :customer AND pc.disabledTs IS NULL');

        $query->setParameter('partner', $partner);
        $query->setParameter('customer', $customer);

        if($query->getSingleScalarResult()>0) {
            $result = true;
        }
        return $result;
    }
}
