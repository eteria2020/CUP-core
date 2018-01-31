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

        $date1 = $date->format('Y-m-d');
        $dateTo = new \DateTime($date1);
        $date2 = $dateTo->add(new \DateInterval('P1D'))->format('Y-m-d');

        $query = $em->createQuery($dql);
        $query->setParameter('date1', $date1);
        $query->setParameter('date2', $date2);
        $query->setParameter('type', $type);
        $query->setParameter('customer', $customer);

        return $query->getResult();
    }

    public function getBonusPoisAssigned($carplate)
    {
        $time = date_create(date("Y-m-d H:i:s"));
        $dql =  "SELECT cb FROM \SharengoCore\Entity\CustomersBonus cb ".
                "WHERE cb.insertTs >= :time ".
                "AND SUBSTRING(cb.description, 52 ,7) = :carplate"; //with substring get last 7 char (carplate)
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('time',  date_sub($time, date_interval_create_from_date_string('24 hours')));
        $query->setParameter('carplate', $carplate);
        return $query->getResult();
    }
    
    public function getWomenBonusPackage($customer) {
        $now = date("Y-m-d");
        $timeStart = date_create($now. "00:00:00");
        $timeEnd = date_create($now. "23:59:59");
        
        $em = $this->getEntityManager();
        $dql = "SELECT cb FROM \SharengoCore\Entity\CustomersBonus cb ".
                "WHERE cb.insertTs >= :timeStart AND cb.insertTs <= :timeEnd ".
                "AND cb.customer = :customer AND cb.description = 'Night Voucher da 30 minuti' ";
        $query = $em->createQuery($dql);
        $query->setParameter('timeStart', $timeStart);
        $query->setParameter('timeEnd', $timeEnd);
        $query->setParameter('customer', $customer);
        
        return $query->getResult();
    }

    public function getWelcomeBonusPackage($customer) {
        $em = $this->getEntityManager();
        $dql = "SELECT cb FROM \SharengoCore\Entity\CustomersBonus cb ".
               "WHERE cb.customer = :customer AND cb.description = 'Pacchetto di benvenuto' ";
        $query = $em->createQuery($dql);
        $query->setParameter('customer', $customer);
        return $query->getResult();
    }
    
    public function getCustomerBonusNivea() {
        $em = $this->getEntityManager();
        $dql = "SELECT c " .
                "FROM \SharengoCore\Entity\Customers c " .
                "WHERE 1=1 " .
                "AND c.insertedTs >= :start " .
                "AND c.insertedTs  < :end " .
                "AND c.id IN ( " .
                    "SELECT cu.id " .
                    "FROM \SharengoCore\Entity\Trips t " .
                    "JOIN \SharengoCore\Entity\Customers cu WITH t.customer = cu.id " .
                    "WHERE t.timestampBeginning >= :start " .
                    "AND t.timestampBeginning < :end ".
                    ") " .
                "AND c.id NOT IN ( ". 
                    "SELECT cus.id " .
                    "FROM \SharengoCore\Entity\CustomersBonus cb " .
                    "JOIN \SharengoCore\Entity\Customers cus WITH cb.customer = cus.id " .
                    "WHERE cb.description = :description " .
                    ") ";
        $query = $em->createQuery($dql);
        
        $query->setParameter('start', '2018-02-01');
        $query->setParameter('end', '2018-05-01');
        $query->setParameter('description', 'Courtesy of NIVEA');
        
        return $query->getResult();
    }
}
