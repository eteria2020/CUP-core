<?php

namespace SharengoCore\Entity\Repository;

use Cartasi\Entity\Transactions;
use SharengoCore\Entity\CartasiCsvAnomaly;

class CartasiCsvAnomalyRepository extends \Doctrine\ORM\EntityRepository
{
    public function findAllResolved()
    {
        $em = $this->getEntityManager();
        $dql = 'SELECT ccf
            FROM \SharengoCore\Entity\CartasiCsvAnomaly ccf
            WHERE ccf.resolved = true
            ORDER BY ccf.id ASC';

        $query = $em->createQuery($dql);

        return $query->getResult();
    }

    public function findAllUnresolved()
    {
        $em = $this->getEntityManager();
        $dql = 'SELECT ccf
            FROM \SharengoCore\Entity\CartasiCsvAnomaly ccf
            WHERE ccf.resolved = false
            ORDER BY ccf.id ASC';

        $query = $em->createQuery($dql);

        return $query->getResult();
    }

    /**
     * @param array $csvData
     * @return CartasiCsvAnomaly|null
     */
    public function findDuplicateByData(array $csvData)
    {
        $em = $this->getEntityManager();
        $dql = 'SELECT ccf
            FROM \SharengoCore\Entity\CartasiCsvAnomaly ccf
            WHERE ccf.csvData = :csvDataParam';

        $query = $em->createQuery($dql);
        $query->setParameter('csvDataParam', json_encode($csvData));

        return $query->getOneOrNullResult();
    }

    /**
     * @param array $csvData
     * @param Transactions $transaction
     * @return CartasiCsvAnomaly|null
     */
    public function findDuplicateByDataAndTransaction(
        array $csvData,
        Transactions $transaction
    ) {
        $em = $this->getEntityManager();
        $dql = 'SELECT ccf
            FROM \SharengoCore\Entity\CartasiCsvAnomaly ccf
            WHERE ccf.csvData = :csvDataParam
            AND ccf.transaction = :transactionParam';

        $query = $em->createQuery($dql);
        $query->setParameter('csvDataParam', json_encode($csvData));
        $query->setParameter('transactionParam', $transaction);

        return $query->getOneOrNullResult();
    }

    public function findTransactionTypeEntityFromTransaction(Transactions $transaction)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            'SELECT p FROM \SharengoCore\Entity\SubscriptionPayment p '.
            'WHERE p.transaction = :transaction '
        );
        $query->setParameter('transaction', $transaction);
        $query->setMaxResults(1);
        $result = $query->getOneOrNullResult();
        if (!is_null($result)) {
            return $result;
        }

        $query = $em->createQuery(
            'SELECT p FROM \SharengoCore\Entity\BonusPackagePayment p '.
            'WHERE p.transaction = :transaction '
        );
        $query->setParameter('transaction', $transaction);
        $query->setMaxResults(1);
        $result = $query->getOneOrNullResult();
        if (!is_null($result)) {
            return $result;
        }

        $query = $em->createQuery(
            'SELECT tp FROM \SharengoCore\Entity\TripPayments tp '.
            'JOIN tp.tripPaymentTries tpt '.
            'WHERE tpt.transaction = :transaction '
        );
        $query->setParameter('transaction', $transaction);
        $query->setMaxResults(1);
        return $query->getOneOrNullResult();
    }
}
