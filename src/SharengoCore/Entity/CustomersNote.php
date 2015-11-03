<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CustomersNote
 *
 * @ORM\Table(name="customers_notes")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\CustomersNoteRepository")
 */
class CustomersNote
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="customers_notes_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var Customers
     *
     * @ORM\ManyToOne(targetEntity="Customers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $customer;

    /**
     * @var Webuser
     *
     * @ORM\ManyToOne(targetEntity="Webuser")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="webuser_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $webuser;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="inserted_ts", type="datetime", nullable=false)
     */
    private $insertedTs;

    /**
     * @var string
     *
     * @ORM\Column(name="note", type="string", nullable=false)
     */
    private $note;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Webuser
     */
    public function getWebuser()
    {
        return $this->webuser;
    }

    /**
     * @return \DateTime
     */
    public function getInsertedTs()
    {
        return $this->insertesTs;
    }

    /**
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }
}
