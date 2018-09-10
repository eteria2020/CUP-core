<?php

namespace SharengoCore\Entity\Repository;

use SharengoCore\Entity\Customers;
use SharengoCore\Entity\CustomersBonus;
use SharengoCore\Entity\TripPayments;
use Doctrine\ORM\Query\ResultSetMapping;

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
            AND c.password = :password';

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

    /**
     *
     * Find customers by mobile number
     *
     * @param string $value    mobile number
     * @return Customers[]     customers list with the same mobile number
     */
    public function findByMobile($value)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery('SELECT c FROM \SharengoCore\Entity\Customers c WHERE c.mobile = :value');
        $query->setParameter('value', $value);
        return $query->getResult();
    }

    /**
     * Find customers that have the last 9 digits of mobile number the same of $value
     * 
     * @param string $value     mobile number
     * @return Customers[]      customers list with the same mobile number
     */
    public function findByMobileLast9Digit($value)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT c FROM \SharengoCore\Entity\Customers c WHERE c.mobile LIKE :value");
        $query->setParameter('value', '%'.substr($value, -9));
        return $query->getResult();
    }

 /**
     * Return the customer that promocode Member Get Member (XXXXX-XXXXX) match width part of hash code 
     * (i.e D07D4-72E62 --> select * FROM customers WHERE hash LIKE LOWER('d_0_7_D_4_7_2_F_6_2%'))
     * 
     * @param type $promocode
     * @return Customers
     */
    public function findByPromocodeMemberGetMember($promocode)
    {
        $value = strtolower( substr($promocode,0,1).'_'.substr($promocode,1,1).'_'.substr($promocode,2,1).'_'.substr($promocode,3,1).'_'.substr($promocode,4,1).'_'.
            substr($promocode,6,1).'_'.substr($promocode,7,1).'_'.substr($promocode,8,1).'_'.substr($promocode,9,1).'_'.substr($promocode,10,1).'_'.
            '%');

        $em = $this->getEntityManager();
        $query = $em->createQuery('SELECT c FROM \SharengoCore\Entity\Customers c WHERE c.hash LIKE :value');
        $query->setParameter('value', $value);
        return $query->getOneOrNullResult();
    }

    
    /**
     *
     * Check if mobile number already exists
     * The function compares values from right to left to evaluate the mobiles without dial code
     *
     * @param string $mobile    mobile number
     * @return int              0 = not found
     *                         >0 = found
     */
    public function checkMobileNumber($mobile)
    {
        $sql = "SELECT sng_checkmobile('".$mobile."')";
        $query = $this->getEntityManager()->getConnection()->query($sql);
        $row = $query->fetch();
        return $row["sng_checkmobile"];
    }

    public function partnerData($param)
    {
        $sql = "SELECT partnerData('".$param."')";
        $query = $this->getEntityManager()->getConnection()->query($sql);
        $row = $query->fetch();
        return $row['partnerdata'];
    }

    /**
     * Return an array of customers_id that Customers have:
     * - driver validation old that $lastCheckDate (default 1 year)
     * - at least one trip in the last month
     * - enabled
     * - not maintainer and not gold list
     * - driver license id not foreign
     * 
     * @param datetime $lastCheckDate
     * @param integer $maxCustomers
     * @return array
     */
    public function findCustomersValidLicenseOldCheck($lastCheckDate = null, $maxCustomers = null) {

        $customersLimit = "";

        if(is_null($lastCheckDate)) {
            $lastCheckDate = date("Y-m-d", strtotime("-1 year", time()));
        }

        if(!is_null($maxCustomers)){
            $customersLimit = " LIMIT 10 ";
        }

        $sql = sprintf("SELECT c.id ".
            "FROM customers c ".
            "INNER JOIN ( ".
                "SELECT max(generated_ts) generated_max, customer_id FROM drivers_license_validations ".
                "WHERE  valid = true  ".
                "GROUP BY customer_id  ".
                "HAVING max(generated_ts)< '%s') dlv ".
            "ON (c.id = dlv.customer_id) ".
            "INNER JOIN ( ".
                "SELECT count(*), customer_id ".
                "FROM trips ".
                "WHERE timestamp_end > now() - interval '1 month' ".
                "GROUP BY customer_id) t ".
            "ON (c.id = t.customer_id) ".
            "WHERE ".
            "c.enabled=true AND c.maintainer=false AND c.gold_list=false AND c.driver_license_foreign = false ".
            "ORDER BY c.id %s",
            $lastCheckDate,
            $customersLimit);

        $stm = $this->getEntityManager()->getConnection()->query($sql);
        $result = $stm->fetchAll();

        return $result;
    }

}