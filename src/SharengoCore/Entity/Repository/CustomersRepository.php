<?php

namespace SharengoCore\Entity\Repository;

use SharengoCore\Entity\Customers;
use SharengoCore\Entity\CustomersBonus;
use SharengoCore\Entity\TripPayments;

class CustomersRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param string $field
     * @param string $value
     * @return Customers[]
     */
    public function findByCI($field, $value)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery('SELECT c FROM \SharengoCore\Entity\Customers c WHERE UPPER(c.'.$field.') = UPPER(:value)');
        $query->setParameter('value', $value);

        return $query->getResult();
    }

    /**
     * @param string $s_username
     * @param string $s_password
     * @return Customer|null
     */
    public function getUserByEmailPassword($s_username, $s_password)
    {
        $s_query =  'SELECT c
            FROM \SharengoCore\Entity\Customers c
            WHERE lower(c.email) = lower(:user)
            AND c.password = :password
            AND c.registrationCompleted = true';

        $I_query = $this->getEntityManager()->createQuery($s_query);
        $I_query->setParameter('user', $s_username);
        $I_query->setParameter('password', $s_password);

        return $I_query->getOneOrNullResult();
    }

    /**
     * @return integer
     */
    public function getTotalCustomers()
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery('SELECT COUNT(c.id) FROM \SharengoCore\Entity\Customers c');
        return $query->getSingleScalarResult();
    }

    /**
     * @param mixed[] $filters
     * @param integer $limit
     * @return Customers[]
     */
    public function findListCustomersFilteredLimited($filters, $limit)
    {
        $qb = $this->createQueryBuilder('c');

        $isFirstParam = true;
        foreach ($filters as $key => $value) {
            // retrieve card entity, not just card field
            $processedKey = ($key == 'card') ? 'IDENTITY(c.' . $key . ')' : 'c.' . $key;

            // generate the dql statement for the specific parameter
            $statement = 'LOWER(' . $processedKey . ') LIKE :' . $key . 'Val';

            // set WHERE ... or AND ... based on isFirstParam flag
            if ($isFirstParam) {
                $qb->where($statement);
                $isFirstParam = false;
            } else {
                $qb->andWhere($statement);
            }

            // set the parameter
            $qb->setParameter($key . 'Val', strtolower('%'.$value.'%'));
        }

        $qb->orderBy('c.surname', 'ASC');
        $qb->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    /**
     * @return string[]
     */
    public function findMaintainersCards()
    {
        $em = $this->getEntityManager();

        $dql = 'SELECT IDENTITY(c.card)
        FROM \SharengoCore\Entity\Customers c
        WHERE c.maintainer = :maintainerValue AND c.enabled = :enabledValue';

        $query = $em->createQuery($dql);
        $query->setParameter('maintainerValue', true);
        $query->setParameter('enabledValue', true);

        return $query->getResult();
    }

    /**
     * @return Customers[]
     */
    public function findByFirstPaymentCompletedNoInvoice()
    {
        $em = $this->getEntityManager();

        $dql = "SELECT c
        FROM \SharengoCore\Entity\Customers c
        WHERE c.firstPaymentCompleted = true
        AND NOT EXISTS
        (SELECT 1 FROM \SharengoCore\Entity\Invoices i
         WHERE i.customer = c
         AND i.type = 'FIRST_PAYMENT'
        )
        ORDER BY c.insertedTs ASC";

        $query = $em->createQuery($dql);
        return $query->getResult();
    }

    /**
     * @return Customers[]
     */
    public function getLatePayers()
    {
        $dql = "SELECT c FROM \SharengoCore\Entity\Customers c ".
            "JOIN c.trips t ".
            "JOIN t.tripPayment tp ".
            "WHERE c.enabled = TRUE ".
            "AND tp.status = :status ".
            "AND tp.toBePayedFrom <= :oneWeekAgo ".
            "GROUP BY c.id";

        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('status', TripPayments::STATUS_TO_BE_PAYED);
        $query->setParameter('oneWeekAgo', date_create('-1 week'));

        return $query->getResult();
    }

    /**
     * @param \DateTime $date
     * @return Customers[]
     */
    public function findCustomersWithDiscountOlderThan(\DateTime $date)
    {
        $dql = "SELECT c FROM \SharengoCore\Entity\Customers c " .
            "LEFT JOIN c.oldDiscounts od " .
            "WHERE c.discountRate > 0 ".
            "AND (c.insertedTs <= :date " .
            "OR c.insertedTs IS NULL) " .
            "GROUP BY c.id " .
            "HAVING MAX(od.obsoleteFrom) IS NULL " .
            "OR MAX(od.obsoleteFrom) <= :date ";

        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('date', $date);

        return $query->getResult();
    }

    /**
     * @param \DateTime $date
     * @return Customers[]
     */
    public function findCustomersWithDiscountOlderExactly(\DateTime $date)
    {
        $dql = "SELECT c FROM \SharengoCore\Entity\Customers c " .
            "LEFT JOIN c.oldDiscounts od " .
            "WHERE c.discountRate > 0 ".
            "AND DATE_FORMAT(c.insertedTs, 'YYYY-MM-DD') = :date " .
            "GROUP BY c.id " .
            "HAVING MAX(od.obsoleteFrom) IS NULL " .
            "OR MAX(od.obsoleteFrom) <= :date ";

        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('date', $date->format('Y-m-d'));

        return $query->getResult();
    }

    /**
     * This method returns the Customers who's birthday is in the day specified
     * by the $date parameter and that DO NOT already have a CustomersBonus
     * assigned for it
     *
     * @param \DateTime $date
     * @return Customers[]
     */
    public function findAllForBirthdayBonusAssignement(\DateTime $date)
    {
        $em = $this->getEntityManager();

        $dql = "SELECT c
            FROM \SharengoCore\Entity\Customers c
            WHERE DATE_FORMAT(c.birthDate, 'MM-DD') = :birthdayParam
            AND NOT EXISTS(
                SELECT 1
                FROM \SharengoCore\Entity\CustomersBonus cb
                WHERE cb.customer = c
                AND cb.type = 'birthday'
                AND cb.validFrom = :fromParam
                AND cb.validTo = :toParam
            )";

        $query = $em->createQuery($dql);
        $query->setParameter('birthdayParam', $date->format('m-d'));
        $date = $date->format('Y-m-d');
        $query->setParameter('fromParam', $date . ' 00:00:00');
        $query->setParameter('toParam', $date . ' 23:59:59');

        return $query->getResult();
    }
    
        /**
     * This method returns the Customers with expired drive license at current date
     *
     * @return Customers[]
     */
    public function findAllCustomersWithExpireLicense(){
        
        $em = $this->getEntityManager();

        $dql = "SELECT c
            FROM \SharengoCore\Entity\Customers c
            WHERE c.enabled = TRUE AND c.driverLicenseExpire < CURRENT_DATE()  
            ORDER BY c.id ASC";

        $query = $em->createQuery($dql);
        return $query->getResult();
        
    }
}
