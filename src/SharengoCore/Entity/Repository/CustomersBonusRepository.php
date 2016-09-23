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

    public function checkUsedPromoCode(Customers $customer, PromoCodes $promoCode)
    {
        $dql = 'SELECT cb FROM \SharengoCore\Entity\CustomersBonus cb ' .
                   'WHERE cb.customer = :id AND cb.promocode = :code';

        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameters([
            'id'   => $customer->getId(),
            'code' => $promoCode->getId()
        ]);

        return $query->getResult();
    }
    
    /**
     * returns the bonuses for customer, date and type
     * @param Customers $customer
     * @param DateTime $date
     * @param string $type
     * @return CustomersBonus[]
     */
    public function getBonusesForCustomerIdAndDateInsertionAndType(Customers $customer, \DateTime $date, $type)
    {
        $em = $this->getEntityManager();
        $dql = 'SELECT cb FROM \SharengoCore\Entity\CustomersBonus cb '.
            'WHERE cb.validFrom >= :date1 '.
            'AND cb.validFrom < :date2 '.
            'AND cb.type = :type '.
            'AND cb.customer = :customer';
            //'ORDER BY cb.validTo ASC';
        
        //var_dump($dql);
        
        $date1 = $date->format('Y-m-d');
        $dateTo = new \DateTime($date1);
        $date2 = $dateTo->add(new \DateInterval('P1D'))->format('Y-m-d');
        
        //var_dump($date1);
        //var_dump($date2);

        $query = $em->createQuery($dql);        
        $query->setParameter('date1', $date1);
        $query->setParameter('date2', $date2);
        $query->setParameter('type', $type);
        $query->setParameter('customer', $customer);

        return $query->getResult();
    }
}
