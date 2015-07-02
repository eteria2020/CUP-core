<?php

namespace SharengoCore\Entity\Repository;

use SharengoCore\Entity\Customers;
use SharengoCore\Entity\PromoCodes;

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

    public function checkUsedPromoCode(Customers $I_customer, PromoCodes $I_promoCode)
    {
        $s_query =  'SELECT cb FROM \SharengoCore\Entity\CustomersBonus cb ' .
            'WHERE cb.customer = :id AND cb.promocode = :code ' ;

        $I_query = $this->getEntityManager()->createQuery($s_query);
        $I_query->setParameters([
            'id'   => $I_customer->getId(),
            'code' => $I_promoCode->getId()
        ]);

        return $I_query->getResult();
    }
}
