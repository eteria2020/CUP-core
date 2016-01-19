<?php

namespace SharengoCore\Service;

use Cartasi\Entity\Transactions;
use Cartasi\Service\CartasiCsvService;
use Cartasi\Service\CartasiPaymentsService;
use SharengoCore\Entity\CartasiCsvAnomaly;
use SharengoCore\Entity\CartasiCsvFile;
use SharengoCore\Entity\Repository\CartasiCsvFileRepository;
use SharengoCore\Entity\Repository\CartasiCsvAnomalyRepository;
use SharengoCore\Exception\CsvFileAlreadyAnalyzedException;

use Doctrine\ORM\EntityManager;

class CsvService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CartasiCsvFileRepository
     */
    private $csvFileRepository;

    /**
     * @var CartasiCsvAnomalyRepository
     */
    private $csvAnomalyRepository;

    /**
     * @var CartasiCsvService
     */
    private $cartasiCsvService;

    /**
     * @var CartasiPaymentsService
     */
    private $cartasiPaymentsService;

    /**
     * @var string[]
     */
    private $csvConfig;

    /**
     * @param EntityManager $entityManager
     * @param CartasiCsvFileRepository $csvFileRepository
     * @param CartasiCsvAnomalyRepository $csvAnomalyRepository
     * @param CartasiCsvService $cartasiCsvService
     * @param CartasiPaymentsService $cartasiPaymentsService
     * @param array $csvConfig
     */
    public function __construct(
        EntityManager $entityManager,
        CartasiCsvFileRepository $csvFileRepository,
        CartasiCsvAnomalyRepository $csvAnomalyRepository,
        CartasiCsvService $cartasiCsvService,
        CartasiPaymentsService $cartasiPaymentsService,
        array $csvConfig
    ) {
        $this->entityManager = $entityManager;
        $this->csvFileRepository = $csvFileRepository;
        $this->csvAnomalyRepository = $csvAnomalyRepository;
        $this->cartasiCsvService = $cartasiCsvService;
        $this->cartasiPaymentsService = $cartasiPaymentsService;
        $this->csvConfig = $csvConfig;
    }

    /**
     * @param integer $id
     * @return CartasiCsvFile
     */
    public function getFileById($id)
    {
        return $this->csvFileRepository->findOneById($id);
    }

    /**
     * @return CartasiCsvFile[]
     */
    public function getAllFiles()
    {
        return $this->csvFileRepository->findAll();
    }

    /**
     * @return CartasiCsvAnomaly[]
     */
    public function getAllResolvedAnomalies()
    {
        return $this->csvAnomalyRepository->findAllResolved();
    }

    /**
     * @return CartasiCsvAnomaly[]
     */
    public function getAllUnresolvedAnomalies()
    {
        return $this->csvAnomalyRepository->findAllUnresolved();
    }

    /**
     * @return string[]
     */
    public function searchForNewFiles()
    {
        return $this->cartasiCsvService->getCsvList($this->csvConfig['newPath']);
    }

    /**
     * @param string $filename
     */
    public function addFile($filename)
    {
        $this->cartasiCsvService->checkFileCompatibility(
            $this->csvConfig['newPath'] . '/' . $filename
        );

        $csvFile = new CartasiCsvFile($filename);
        $this->entityManager->persist($csvFile);
        $this->entityManager->flush();
        $this->moveFile($csvFile, $this->csvConfig['newPath'], $this->csvConfig['addedPath']);
    }

    /**
     * @param CartasiCsvFile $csvFile
     * @throws CsvFileAlreadyAnalyzedException
     */
    public function analyzeFile(CartasiCsvFile $csvFile)
    {
        if ($csvFile->isAnalyzed()) {
            throw new CsvFileAlreadyAnalyzedException();
        }

        $this->moveFile($csvFile, $this->csvConfig['addedPath'], $this->csvConfig['tempPath']);

        try {
            // Get data for the file first. This may raise exceptions.
            $csvData = $this->cartasiCsvService->getCsvData(
                $this->csvConfig['tempPath'] . '/' . $csvFile->getFilename()
            );
        } catch (Exception $e) {
            $this->moveFile($csvFile, $this->csvConfig['tempPath'], $this->csvConfig['addedPath']);
            throw $e;
        }

        try {
            $this->entityManager->beginTransaction();
            foreach ($csvData as $key => $value) {
                $csvAnomaly = null;
                $transaction = $this->cartasiPaymentsService->getTransaction($key);

                if (!($transaction instanceof Transactions)) {
                    if (!$this->isAnomalyAlreadyRegistered($value)) {
                        $csvAnomaly = new CartasiCsvAnomaly(
                            $csvFile,
                            CartasiCsvAnomaly::MISSING_FROM_TRANSACTIONS,
                            $value
                        );
                    }
                } elseif ($this->isDataAnAnomaly($transaction, $value)) {
                    if (!$this->isAnomalyAlreadyRegistered($value, $transaction)) {
                        $csvAnomaly = new CartasiCsvAnomaly(
                            $csvFile,
                            CartasiCsvAnomaly::OUTCOME_ERROR,
                            $value,
                            $transaction
                        );
                    }
                }

                if ($csvAnomaly instanceof CartasiCsvAnomaly) {
                    $this->entityManager->persist($csvAnomaly);
                }
            }

            $csvFile->markAsAnalyzed();
            $this->entityManager->persist($csvFile);

            $this->entityManager->flush();
            $this->entityManager->commit();

            $this->moveFile($csvFile, $this->csvConfig['tempPath'], $this->csvConfig['analyzedPath']);
        } catch (Exception $e) {
            $this->moveFile($csvFile, $this->csvConfig['tempPath'], $this->csvConfig['addedPath']);
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * @param CartasiCsvFile $csvFile
     * @param string $fromPath
     * @param string $toPath
     */
    private function moveFile(CartasiCsvFile $csvFile, $fromPath, $toPath)
    {
        rename(
            $fromPath . '/' . $csvFile->getFilename(),
            $toPath . '/' . $csvFile->getFilename()
        );
    }

    /**
     * Checks for anomalies between the csv exported data and the related
     * Transaction. It first checks to see whether the outcomes are the same.
     * If they are and if they are positive it checks if the amounts are equal,
     * ignores the rest otherwise.
     *
     * @param Transactions $transaction
     * @param array $csvData
     * @return boolean
     */
    private function isDataAnAnomaly(Transactions $transaction, array $csvData)
    {
        // Check if outcome is the same
        $transactionOutcome =
            $transaction->getOutcome() == 'OK' ||
            $transaction->getOutcome() == '0 - autorizzazione concessa';
        $csvOutcome = $csvData['Stato'] == 'Contabilizzato rimborsabile';
        if ($transactionOutcome != $csvOutcome) {
            return true;
        } elseif ($transactionOutcome) {

            // Check if amount is the same
            $transactionAmount = $transaction->getAmount();
            $csvAmount = intval((floatval(str_replace(',', '.', $csvData['Importo contabilizzato'])) * 100) . '.0');
            if ($transactionAmount != $csvAmount) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $csvData
     * @param Transactions|null $transaction
     * @return boolean
     */
    private function isAnomalyAlreadyRegistered(
        array $csvData,
        Transactions $transaction = null
    ) {
        $duplicate = null;
        if ($transaction instanceof Transactions) {
            $duplicate = $this->csvAnomalyRepository->findDuplicateByDataAndTransaction(
                $csvData,
                $transaction
            );
        } else {
            $duplicate = $this->csvAnomalyRepository->findDuplicateByData($csvData);
        }

        return $duplicate instanceof CartasiCsvAnomaly;
    }
}
