<?php

namespace SharengoCore\Service;

use Cartasi\Entity\Transactions;
use Cartasi\Service\CartasiCsvService;
use Cartasi\Service\CartasiPaymentsService;
use SharengoCore\Entity\CartasiCsvAnomaly;
use SharengoCore\Entity\CartasiCsvFile;
use SharengoCore\Entity\Webuser;
use SharengoCore\Entity\Repository\CartasiCsvAnomalyRepository;
use SharengoCore\Entity\Repository\CartasiCsvFileRepository;
use SharengoCore\Exception\CsvFileAlreadyAnalyzedException;
use SharengoCore\Exception\NoteContentNotValidException;

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
     * Get CartasiCsvFile based on id.
     *
     * @param integer $id
     * @return CartasiCsvFile
     */
    public function getFileById($id)
    {
        return $this->csvFileRepository->findOneById($id);
    }

    /**
     * Get all CartasiCsvFiles.
     *
     * @return CartasiCsvFile[]
     */
    public function getAllFiles()
    {
        return $this->csvFileRepository->findAll();
    }

    /**
     * @param integer $id
     * @return CartasiCsvAnomaly
     */
    public function getAnomalyById($id)
    {
        return $this->csvAnomalyRepository->findOneById($id);
    }

    /**
     * Get all CartasiCsvAnomalies with resolved = true.
     *
     * @return CartasiCsvAnomaly[]
     */
    public function getAllResolvedAnomalies()
    {
        return $this->csvAnomalyRepository->findAllResolved();
    }

    /**
     * Get all CartasiCsvAnomalies with resolved = false.
     *
     * @return CartasiCsvAnomaly[]
     */
    public function getAllUnresolvedAnomalies()
    {
        return $this->csvAnomalyRepository->findAllUnresolved();
    }

    /**
     * Return files that have not yet been analyzed. These are the files that
     * have been uploaded manually (not with the upload form).
     *
     * @return string[]
     */
    public function searchForNewFiles()
    {
        return $this->cartasiCsvService->getCsvList($this->csvConfig['newPath']);
    }

    /**
     * Generate a CartasiCsvFile from a .csv file.
     *
     * @param string $filename
     * @param Webuser $webuser
     * @return CartasiCsvFile
     */
    public function addFile($filename, Webuser $webuser)
    {
        $this->cartasiCsvService->checkFileCompatibility(
            $this->csvConfig['newPath'] . '/' . $filename
        );

        $csvFile = new CartasiCsvFile($filename, $webuser);
        $this->entityManager->persist($csvFile);
        $this->entityManager->flush();
        $this->moveFile($csvFile, $this->csvConfig['newPath'], $this->csvConfig['addedPath']);

        return $csvFile;
    }

    /**
     * Generate all CartasiCsvAnomalies for a CartasiCsvFile.
     *
     * @param CartasiCsvFile $csvFile
     * @throws CsvFileAlreadyAnalyzedException
     */
    public function analyzeFile(CartasiCsvFile $csvFile)
    {
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

                // Find if related transaction exists. If it does not, proceed
                // with CartasiCsvAnomaly creation
                $transaction = $this->cartasiPaymentsService->getTransaction($key);
                if (!($transaction instanceof Transactions)) {
                    if (!$this->isAnomalyAlreadyRegistered($value)) {
                        $csvAnomaly = new CartasiCsvAnomaly(
                            $csvFile,
                            CartasiCsvAnomaly::MISSING_FROM_TRANSACTIONS,
                            $value
                        );
                    }

                // If transaction exists, check if the values are the same. If
                // not, proceed with CartasiCsvAnomaly creation
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
     * Add a note to the CartasiCsvAnomaly.
     *
     * @param CartasiCsvAnomaly $csvAnomaly
     * @param Webuser $webuser
     * @param string $content
     * @throws NoteContentNotValidException
     */
    public function addNoteToAnomaly(
        CartasiCsvAnomaly $csvAnomaly,
        Webuser $webuser,
        $content
    ) {
        if (empty($content)) {
            throw new NoteContentNotValidException();
        }

        $csvAnomaly->addUpdate($webuser, $content);
        $this->entityManager->persist($csvAnomaly);
        $this->entityManager->flush();
    }

    /**
     * Move a file from one path to another.
     *
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

            // Check if amount is the same only if outcome is positive
            $transactionAmount = $transaction->getAmount();
            $csvAmount = intval((floatval(str_replace(',', '.', $csvData['Importo contabilizzato'])) * 100) . '.0');
            if ($transactionAmount != $csvAmount) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if a CartasiCsvAnomaly with same csvData and optionally
     * transaction already exists.
     *
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
