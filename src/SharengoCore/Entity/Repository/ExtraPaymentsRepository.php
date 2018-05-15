<?php

namespace SharengoCore\Entity\Repository;

use SharengoCore\Entity\Customers;
use SharengoCore\Entity\Trips;
use SharengoCore\Entity\TripPayments;
use SharengoCore\Entity\ExtraPayments;

use Doctrine\ORM\Query\ResultSetMapping;

class ExtraPaymentsRepository extends \Doctrine\ORM\EntityRepository
{

    public function countTotalExtra()
    {
        $em = $this->getEntityManager();

        $dql = 'SELECT COUNT(ep) FROM SharengoCore\Entity\ExtraPayments ep ';
            //. 'WHERE ep.status = :status';

        $query = $em->createQuery($dql);

        //$query->setParameter('status', TripPayments::STATUS_WRONG_PAYMENT);

        return $query->getSingleScalarResult();
    }

    public function findExtraPaymentsForPayment(Customers $customer = null, $timestampEndParam = null, $idCondition = null, $limit = null)
    {
        $em = $this->getEntityManager();

        $dql = 'SELECT ep FROM SharengoCore\Entity\ExtraPayments ep '.
            'JOIN ep.customer c '.
            'WHERE ep.status = :status '.
            'AND ep.generatedTs < :midnight ';

        if ($customer instanceof Customers) {
            $dql .= 'AND c = :customer ';
        }
        if ($timestampEndParam !== null){
            $dql .= 'AND ep.generatedTs >= :timestampEndParam ';
        }

        if ($idCondition !== null){
            $dql .= 'AND ep.id > :condition ';
        }
        $dql .= ' ORDER BY ep.id ASC';

        $query = $em->createQuery($dql);

        $query->setParameter('status', ExtraPayments::STATUS_TO_BE_PAYED);
        $query->setParameter('midnight', date_create('midnight'));

        if ($customer instanceof Customers) {
            $query->setParameter('customer', $customer);
        }

        if ($timestampEndParam !== null){
            $query->setParameter('timestampEndParam', date_create($timestampEndParam)->setTime(00,00,00));
        }

        if ($idCondition !== null){
            $query->setParameter('condition', $idCondition);
        }

        if ($limit !== null){
            $query->setMaxResults($limit);
        }
 
        return $query->getResult();
    }

    
    public function findWrongExtraPaymentsTime(Customers $customer = null, $start, $end, $idCondition = null, $limit = null)
    {
        $em = $this->getEntityManager();

        $dql = 'SELECT ep FROM SharengoCore\Entity\ExtraPayments ep '.
            'JOIN ep.customer c '.
            'WHERE ep.status = :status '.
            'AND ep.generatedTs >= :start '.
            'AND ep.generatedTs <= :end ';

        if ($customer instanceof Customers) {
            $dql .= 'AND c = :customer ';
        }

        if ($idCondition !== null){
            $dql .= 'AND ep.id > :condition ';
        }

        $dql .= ' ORDER BY ep.id ASC';

        $query = $em->createQuery($dql);

        $query->setParameter('status', TripPayments::STATUS_WRONG_PAYMENT);
        $query->setParameter('start', date_create($start));
        $query->setParameter('end', date_create($end));

        if ($customer instanceof Customers) {
            $query->setParameter('customer', $customer);
        }

        if ($idCondition !== null){
            $query->setParameter('condition', $idCondition);
        }

        if ($limit !== null){
            $query->setMaxResults($limit);
        }

        return $query->getResult();
    }
    
    public function getCountExtraPaymentsForPayment($timestampEndParam = null, $idCondition = null, $limit = null)
    {
        $em = $this->getEntityManager();
        $main = "SELECT ep.id as id FROM extra_payments as ep ".
               "WHERE ep.status = 'to_be_payed' AND ep.generated_Ts < (date 'now()' + time '00:00:00') ";

        if ($timestampEndParam !== null){
            $main .= "AND ep.generated_Ts >= (CURRENT_DATE -INTERVAL '".$timestampEndParam."')::date + time '00:00:00'";
        }

        if ($idCondition !== null){
            $main .= 'AND ep.id > '.$idCondition;
        }

        $main .= ' ORDER BY ep.id ASC';

        if ($limit !== null){
            $main .= ' LIMIT '.$limit;
        }
        $sql = "SELECT (SELECT count(id) FROM (".$main.") as tp) as count, (SELECT id FROM (".$main.") as tp ORDER BY id DESC LIMIT 1) as last";

        $rsm = new ResultSetMapping;
        $rsm->addScalarResult('count', 'count');
        $rsm->addScalarResult('last', 'last');
        $query = $em->createNativeQuery($sql, $rsm);

        return $query->getResult();
    }
    

    public function findExtraPaymentsWrong(Customers $customer = null, $timestampEndParam = null)
    {
        $em = $this->getEntityManager();

        $dql = 'SELECT ep FROM SharengoCore\Entity\ExtraPayments ep '.
            'JOIN ep.customer c '.
            'WHERE ep.status = :status '.
            'AND ep.generatedTs < :midnight ';

        if ($customer instanceof Customers) {
            $dql .= 'AND c = :customer ';
        }
        if ($timestampEndParam !== null){
            $dql .= 'AND ep.generatedTs >= :timestampEndParam ';
        }

        $dql .= ' ORDER BY ep.generatedTs ASC';

        $query = $em->createQuery($dql);

        $query->setParameter('status', ExtraPayments::STATUS_WRONG_PAYMENT);
        $query->setParameter('midnight', date_create('midnight'));

        if ($customer instanceof Customers) {
            $query->setParameter('customer', $customer);
        }

        if ($timestampEndParam !== null){
            $query->setParameter('timestampEndParam', date_create($timestampEndParam));
        }
        
        return $query->getResult();
    }
    
    public function getExtraPaymentsWrongAndPayable(Customers $customer) {
        $em = $this->getEntityManager();

        $dql = "SELECT ep
            FROM SharengoCore\Entity\ExtraPayments ep
            WHERE ep.customer = :customerParam
            AND ep.payable = TRUE 
            AND ep.status = :status ";

        $query = $em->createQuery($dql);

        $query->setParameter('customerParam', $customer);
        $query->setParameter('status', ExtraPayments::STATUS_WRONG_PAYMENT);

        return $query->getResult();
    }
    
    public function getCountWrongExtraPayments($start, $end, $idCondition = null, $limit = null)
    {
        $em = $this->getEntityManager();
        $main = "SELECT e.id as id FROM extra_payments as e ".
            "WHERE e.status = 'wrong_payment' AND e.generated_Ts >= '".$start."' AND e.generated_Ts <= '".$end."' ";

        if ($idCondition !== null){
            $main .= 'AND e.id > '.$idCondition;
        }

        $main .= ' ORDER BY e.id ASC';

        if ($limit !== null){
            $main .= ' LIMIT '.$limit;
        }
        $sql = "SELECT (SELECT count(id) FROM (".$main.") as ep) as count, (SELECT id FROM (".$main.") as ep ORDER BY id DESC LIMIT 1) as last";

        $rsm = new ResultSetMapping;
        $rsm->addScalarResult('count', 'count');
        $rsm->addScalarResult('last', 'last');
        $query = $em->createNativeQuery($sql, $rsm);

        return $query->getResult();
    }
    
    /**
     * @param Customers $customer
     * @return TripPayments[]
     */
    public function findFailedByCustomer(Customers $customer)
    {
        $em = $this->getEntityManager();

        $dql = "SELECT ep
            FROM SharengoCore\Entity\ExtraPayments ep
            LEFT JOIN SharengoCore\Entity\ExtraPaymentTries ept WITH ept.extraPayment = ep
            WHERE ep.customer = :customerParam
            AND ept.outcome = 'KO'
            GROUP BY ep.id
            ORDER BY ep.id DESC";

        $query = $em->createQuery($dql);

        $query->setParameter('customerParam', $customer);

        return $query->getResult();
    }

}
