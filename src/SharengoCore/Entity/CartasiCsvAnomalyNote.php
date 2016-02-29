<?php

namespace SharengoCore\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * CartasiCsvAnomalyNote
 *
 * @ORM\Table(name="cartasi_csv_anomalies_notes")
 * @ORM\Entity
 */
class CartasiCsvAnomalyNote
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="inserted_at", type="datetime", nullable = false)
     */
    private $insertedAt;

    /**
     * Anomaly that the note refers to
     *
     * @var CartasiCsvAnomaly
     *
     * @ORM\ManyToOne(targetEntity="CartasiCsvAnomaly")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cartasi_csv_anomaly_id", referencedColumnName="id")
     * })
     */
    private $cartasiCsvAnomaly;

    /**
     * Webuser that added the note
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
     * @var string
     *
     * @ORM\Column(name="note", type="text", nullable=true)
     */
    private $note;

    /**
     * @param Webuser $webuser
     * @param CartasiCsvAnomaly $anomaly
     * @param $note
     */
    public function __construct(Webuser $webuser, CartasiCsvAnomaly $anomaly, $note) {
        $this->webuser = $webuser;
        $this->cartasiCsvAnomaly = $anomaly;
        $this->insertedAt = new DateTime();
        $this->note = $note;
    }

    /**
     * @return DateTime
     */
    public function getInsertedAt()
    {
        return $this->insertedAt;
    }

    /**
     * @return Webuser
     */
    public function getWebuser()
    {
        return $this->webuser;
    }

    /**
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @return CartasiCsvAnomaly
     */
    public function getAnomaly()
    {
        return $this->cartasiCsvAnomaly;
    }

}
