<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CartasiCsvFile
 *
 * @ORM\Table(name="cartasi_csv_files")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\CartasiCsvFileRepository")
 */
class CartasiCsvFile
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="cartasi_csv_files_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="inserted_ts", type="datetime", nullable = false)
     */
    private $insertedTs;

    /**
     * @var string
     *
     * @ORM\Column(name="filename", type="string", nullable=false)
     */
    private $filename;

    /**
     * @var Webuser
     *
     * @ORM\ManyToOne(targetEntity="Webuser")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="webuser_id", referencedColumnName="id")
     * })
     */
    private $webuser;

    /**
     * @var CartasiCsvAnomaly[]|null
     *
     * @ORM\OneToMany(targetEntity="CartasiCsvAnomaly", mappedBy="cartasiCsvFile")
     */
    private $cartasiCsvAnomalies;

    /**
     * @param string $filename
     * @param Webuser
     */
    public function __construct($filename, $webuser)
    {
        $this->insertedTs = date_create();
        $this->filename = $filename;
        $this->webuser = $webuser;
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
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @return CartasiCsvAnomaly[]|null
     */
    public function getCartasiCsvAnomalies()
    {
        return $this->cartasiCsvAnomalies;
    }
}
