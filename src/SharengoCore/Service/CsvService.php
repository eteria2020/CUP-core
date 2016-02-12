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
use SharengoCore\Exception\NoteContentNotValidException;
use SharengoCore\Exception\MissingOverrideNameException;

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
     * @param boolean $pathWithName Indicated that $filename inclused the path
     *     to the file (path/to/file/filename.csv)
     * @param string|null $overrideName The name used for the CartasiCsvFile
     *     instead of $filename. Necessary if $pathWithName is true
     * @return CartasiCsvFile
     * @throws MissingOverrideNameException
     */
    public function addFile(
        $filename,
        Webuser $webuser,
        $nameWithPath = false,
        $overrideName = null
    ) {
        // Perform some checks
        if ($nameWithPath && is_null($overrideName)) {
            throw new MissingOverrideNameException();
        }
        $this->cartasiCsvService->checkFileCompatibility(
            ($nameWithPath ? '' : $this->csvConfig['newPath'] . '/') . $filename
        );

        $csvFile = new CartasiCsvFile(
            $nameWithPath ? $overrideName : $filename,
            $webuser
        );
        $this->entityManager->persist($csvFile);
        $this->entityManager->flush();
        $this->moveFile(
            $csvFile,
            $nameWithPath ? $filename : $this->csvConfig['newPath'],
            $this->csvConfig['addedPath'],
            !$nameWithPath
        );

        return $csvFile;
    }

    /**
     * Generate all CartasiCsvAnomalies for a CartasiCsvFile.
     *
     * @param CartasiCsvFile $csvFile
     */
    public function analyzeFile(CartasiCsvFile $csvFile)
    {
        $this->moveFile($csvFile, $this->csvConfig['addedPath'], $this->csvConfig['tempPath']);

        try {
            // Get data for the file first. This may raise exceptions.
            $csvData = $this->cartasiCsvService->getCsvData(
                $this->csvConfig['tempPath'] . '/' . $csvFile->getFilename()
            );
        } catch (\Exception $e) {
            $this->moveFile($csvFile, $this->csvConfig['tempPath'], $this->csvConfig['addedPath']);
            throw $e;
        }

        try {
            $this->entityManager->beginTransaction();
            foreach ($csvData as $key => $value) {
                $transaction = $this->cartasiPaymentsService->getTransaction($key);
                $anomalyType = $this->getDataAnomaly($value, $transaction);

                if ($anomalyType !== null) {
                    if (!$this->isAnomalyAlreadyRegistered($value, $transaction)) {
                        $csvAnomaly = new CartasiCsvAnomaly(
                            $csvFile,
                            $anomalyType,
                            $value,
                            $transaction
                        );
                        $this->entityManager->persist($csvAnomaly);
                    }
                }
            }

            $this->entityManager->persist($csvFile);
            $this->entityManager->flush();
            $this->entityManager->commit();

            $this->moveFile($csvFile, $this->csvConfig['tempPath'], $this->csvConfig['analyzedPath']);
        } catch (\Exception $e) {
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
     * @param CartasiCsvAnomaly $csvAnomaly
     * @param Webuser $webuser
     */
    public function resolveAnomaly(CartasiCsvAnomaly $csvAnomaly, Webuser $webuser)
    {
        $csvAnomaly->markAsResolved($webuser);
        $this->entityManager->persist($csvAnomaly);
        $this->entityManager->flush();
    }

    /**
     * Move a file from one path to another.
     *
     * @param CartasiCsvFile $csvFile
     * @param string $fromPath
     * @param string $toPath
     * @param boolean $useCsvFile Specifies wether the name should be extracted
     *     from the CartasiCsvFile or if it is already included in $fromPath.
     *     Does not change the behaviour of $toPath
     */
    private function moveFile(
        CartasiCsvFile $csvFile,
        $fromPath,
        $toPath,
        $useCsvFile = true
    ) {
        rename(
            $fromPath . ($useCsvFile ? '/' . $csvFile->getFilename() : ''),
            $toPath . '/' . $csvFile->getFilename()
        );
    }

    /**
     * Checks for anomalies between the csv exported data and the data in the
     * database. There are three cases of interest for anomalies:
     *
     * - MISSING_FROM_TRANSACTIONS = no transaction and positive csv outcome
     * - OUTCOME_ERROR = transaction found and outcomes are different
     * - AMOUNT_ERROR = transaction found, outcomes are positive and amounts
     *     are different
     *
     * @param array $csvData
     * @param Transactions|null $transaction
     * @return string|null CartasiCsvAnomaly's type if anomaly is found, null
     *     otherwise
     */
    private function getDataAnomaly(
        array $csvData,
        Transactions $transaction = null
    ) {
        $csvOutcome = $csvData['Stato'] == 'Contabilizzato rimborsabile';

        // Check if transaction is registered for a positive outcome
        if (!($transaction instanceof Transactions)) {
            if ($csvOutcome) {
                return CartasiCsvAnomaly::MISSING_FROM_TRANSACTIONS;
            }

        // Check if outcomes are different
        } else {
            $transactionOutcome =
                $transaction->getOutcome() == 'OK' ||
                $transaction->getOutcome() == '0 - autorizzazione concessa';
            if ($transactionOutcome != $csvOutcome) {
                return CartasiCsvAnomaly::OUTCOME_ERROR;

            // Check if amounts are different only if outcome is positive
            } elseif ($transactionOutcome) {
                $transactionAmount = $transaction->getAmount();
                $csvAmount = intval((floatval(str_replace(',', '.', $csvData['Importo contabilizzato'])) * 100) . '.0');
                if ($transactionAmount != $csvAmount) {
                    return CartasiCsvAnomaly::AMOUNT_ERROR;
                }
            }
        }

        return null;
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
