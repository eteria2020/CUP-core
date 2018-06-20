<?php

namespace SharengoCore\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\Partners;


class PartnersRepository extends EntityRepository
{
    public function isBelongCustomerPartner(Partners $partner, Customers $customer)
    {
        $result = false;

        if(is_null($partner) || is_null($customer)) {
            return $result;
        }

        $em = $this->getEntityManager();
        $query = $em->createQuery('SELECT count(*) FROM partners_customers WHERE partner_id='.$partner->getId().' AND customer_id='.$customer->getId().' AND disabled_ts IS NULL ');

        if($query->getSingleScalarResult()>0) {
            $result = true;
        }
        return $result;
    }
}
