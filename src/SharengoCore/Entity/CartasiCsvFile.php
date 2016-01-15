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
     * @var boolean
     *
     * @ORM\Column(name="analyzed", type="boolean", nullable=false)
     */
    private $analyzed;

    /**
     * @param string $filename
     */
    public function __construct($filename)
    {
        $this->insertedTs = date_create();
        $this->filename = $filename;
        $this->analyzed = false;
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
     * @return boolean
     */
    public function isAnalyzed()
    {
        return $this->analyzed;
    }

    public function markAsAnalyzed()
    {
        $this->analyzed = true;
    }
}
