<?php

namespace SharengoCore\Entity\Repository;

use SharengoCore\Entity\Customers;
use SharengoCore\Entity\PromoCodes;
use SharengoCore\Entity\Trips;
use SharengoCore\Service\PromoCodesService;

/**
 * Class CustomersBonusRepository
 * @package SharengoCore\Entity\Repository
 */
class CustomersBonusRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * returns the bonuses appliable to a given trip
     *
     * @var Trips $trip
     * @return CustomersBonus[]
     */
    public function getBonusesForTrip(Trips $trip)
    {
        $em = $this->getEntityManager();
        $dql = 'SELECT cb FROM \SharengoCore\Entity\CustomersBonus cb '.
            'WHERE cb.active = true '.
            'AND cb.validFrom <= :tripEnd '.
            'AND cb.validTo >= :tripBeginning '.
            'AND cb.residual > 0 '.
            'AND cb.customer = :customer '.
            'ORDER BY cb.validTo ASC';

        $query = $em->createQuery($dql);
        $query->setParameter('customer', $trip->getCustomer());
        $query->setParameter('tripBeginning', $trip->getTimestampBeginning());
        $query->setParameter('tripEnd', $trip->getTimestampEnd());

        return $query->getResult();
    }

    public function checkUsedPromoCode(Customers $I_customer, PromoCodes $I_promoCode)
    {
        $s_query = 'SELECT cb FROM \SharengoCore\Entity\CustomersBonus cb ' .
                   'WHERE cb.customer = :id AND cb.promocode = :code';

        $I_query = $this->getEntityManager()->createQuery($s_query);
        $I_query->setParameters([
            'id'   => $I_customer->getId(),
            'code' => $I_promoCode->getId()
        ]);

        return $I_query->getResult();
    }
}
