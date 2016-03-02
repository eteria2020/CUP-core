<?php

namespace SharengoCore\Entity;

use Cartasi\Entity\Transactions;
use SharengoCore\Exception\CartasiCsvAnomalyAlreadyResolvedException;

use Doctrine\ORM\Mapping as ORM;

/**
 * CartasiCsvAnomaly
 *
 * @ORM\Table(name="cartasi_csv_anomalies")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\CartasiCsvAnomalyRepository")
 */
class CartasiCsvAnomaly
{
    /**
     * @var string
     */
    const MISSING_FROM_TRANSACTIONS = 'MISSING_FROM_TRANSACTIONS';

    /**
     * @var string
     */
    const OUTCOME_ERROR = 'OUTCOME_ERROR';

    /**
     * @var string
     */
    const AMOUNT_ERROR = 'AMOUNT_ERROR';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="cartasi_csv_anomalies_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="inserted_ts", type="datetime", nullable = false)
     */
    private $insertedTs;

    /**
     * @var CartasiCsvFile
     *
     * @ORM\ManyToOne(targetEntity="CartasiCsvFile")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cartasi_csv_file_id", referencedColumnName="id")
     * })
     */
    private $cartasiCsvFile;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", nullable=false)
     */
    private $type;

    /**
     * @var integer
     *
     * @ORM\Column(name="amount", type="integer", nullable=false)
     */
    private $amount;

    /**
     * @var boolean
     *
     * @ORM\Column(name="resolved", type="boolean", nullable=false)
     */
    private $resolved;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="resolved_ts", type="datetime", nullable = true)
     */
    private $resolvedTs;

    /**
     * Webuser that marks the anomaly as resolved
     *
     * @var Webuser
     *
     * @ORM\ManyToOne(targetEntity="Webuser")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="webuser_id", referencedColumnName="id")
     * })
     */
    private $webuser;

    /**
     * @var array
     *
     * @ORM\Column(name="csv_data", type="json_array", nullable=false)
     */
    private $csvData;

    /**
     * @var \Cartasi\Entity\Transactions
     *
     * @ORM\ManyToOne(targetEntity="Cartasi\Entity\Transactions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="transaction_id", referencedColumnName="id")
     * })
     */
    private $transaction;


    /**
     * Bidirectional - One-To-Many (INVERSE SIDE)
     * @var CartasiCsvAnomalyNote[]
     * @ORM\OneToMany(targetEntity="CartasiCsvAnomalyNote", mappedBy="cartasiCsvAnomaly")
     * @ORM\OrderBy({"insertedAt" = "DESC"})
     */
    private $notes;

    /**
     * @param CartasiCsvFile $cartasiCsvFile
     * @param string $type
     * @param array $csvData
     * @param Transactions|null $transaction
     */
    public function __construct(
        CartasiCsvFile $cartasiCsvFile,
        $type,
        array $csvData,
        Transactions $transaction = null
    ) {
        $this->insertedTs = date_create();
        $this->cartasiCsvFile = $cartasiCsvFile;
        $this->type = $type;
        $this->resolved = false;
        $this->csvData = $csvData;
        $this->transaction = $transaction;
        $this->amount = $this->calculateAmount();
    }

    /**
     * Returns the amount to be given to the customer. A negative value means
     * that an invoice should be generated.
     *
     * @return integer
     */
    private function calculateAmount()
    {
        // Calculate amount as stated by csv
        $csvAmount = intval(floatval(str_replace(',', '.', $this->csvData['Importo contabilizzato'])) * 100);

        switch ($this->type) {
            case self::MISSING_FROM_TRANSACTIONS:
                return $csvAmount;

            case self::OUTCOME_ERROR:
                if ($this->csvData['Stato'] == 'Contabilizzato rimborsabile') {
                    return - $csvAmount;
                } else {
                    return $this->transaction->getAmount();
                }

            case self::AMOUNT_ERROR:
                return $csvAmount - $this->transaction->getAmount();
        }
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getInsertedTs()
    {
        return $this->insertedTs;
    }

    /**
     * @return Webuser
     */
    public function getWebuser()
    {
        return $this->webuser;
    }

    /**
     * @return DateTime
     */
    public function getResolvedTs()
    {
        return $this->resolvedTs;
    }

    /**
     * @return CartasiCsvFile
     */
    public function getCartasiCsvFile()
    {
        return $this->cartasiCsvFile;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getTypeTranslated()
    {
        switch ($this->type) {
            case self::MISSING_FROM_TRANSACTIONS:
                return 'Transazione mancante';
                break;

            case self::OUTCOME_ERROR:
                return 'Anomalia negli esiti';

            case self::AMOUNT_ERROR:
                return 'Anomalia negli importi';

            default:
                return 'Errore nella traduzione della tipologia';
                break;
        }
    }

    /**
     * @return integer
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return boolean
     */
    public function isResolved()
    {
        return $this->resolved;
    }

    /**
     * Sets resolved variable as true and sets the webuser
     *
     * @param Webuser $webuser
     * @throws CartasiCsvAnomalyAlreadyResolvedException
     */
    public function markAsResolved(Webuser $webuser)
    {
        if ($this->isResolved()) {
            throw new CartasiCsvAnomalyAlreadyResolvedException();
        }

        $this->resolvedTs = date_create();
        $this->resolved = true;
        $this->webuser = $webuser;
    }

    /**
     * @return array
     */
    public function getCsvData()
    {
        return $this->csvData;
    }

    /**
     * @return Transactions|null
     */
    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * @return mixed
     */
    public function getNotes()
    {
        return $this->notes;
    }
}
