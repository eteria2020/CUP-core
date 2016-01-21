<?php

namespace SharengoCore\Entity;

use Cartasi\Entity\Transactions;

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
     * @var boolean
     *
     * @ORM\Column(name="resolved", type="boolean", nullable=false)
     */
    private $resolved;

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
     * @var array
     *
     * @ORM\Column(name="updates", type="json_array", nullable=true)
     */
    private $updates;

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
     * @return CartasiCsvFile
     */
    public function getCartasiCsvFile()
    {
        return $this->cartasiCsvFile;
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
                return 'Anomalia nei dati della transazione';

            default:
                return 'Errore nella traduzione della tipologia';
                break;
        }
    }

    /**
     * @return Transactions|null
     */
    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * @return array|null
     */
    public function getUpdates()
    {
        return $this->updates;
    }

    /**
     * Add an update to the anomaly. Updates are saved as a key => value array
     * where the key is the date at which the note was added and the value is
     * another array with two key => value pairs.
     *
     * The first is 'webuser' => the id of the webuser who inserted the note.
     * The second is 'content' => the content of the note.
     *
     * @param Webuser $webuser
     * @param string $content
     */
    public function addUpdate(Webuser $webuser, $content)
    {
        // Create the main array if no notes were ever added
        if (empty($this->updates)) {
            $this->updates = [];
        }

        $this->updates[date_create()->format('Y-m-d H:i:s')] = [
                'webuser' => $webuser->getId(),
                'content' => $content
            ];
    }
}
