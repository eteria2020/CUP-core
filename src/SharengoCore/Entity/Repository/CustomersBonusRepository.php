<?php

namespace SharengoCore\Entity\Repository;

use SharengoCore\Entity\Customers;

/**
 * Class CustomersBonusRepository
 * @package SharengoCore\Entity\Repository
 */
class CustomersBonusRepository extends \Doctrine\ORM\EntityRepository
{
    public function getTotalBonusResidualByUser(Customers $I_customer)
    {
        $s_query =  'SELECT SUM(cb.residual) AS residual ' .
            'FROM \SharengoCore\Entity\CustomersBonus cb ' .
            'WHERE cb.customer = :id ' .
            'AND CURRENT_TIMESTAMP() > cb.validFrom AND CURRENT_TIMESTAMP() < cb.validTo ';

        $I_query = $this->getEntityManager()->createQuery($s_query);
        $I_query->setParameter('id', $I_customer->getId());

        return $I_query->getSingleScalarResult();
    }
}
