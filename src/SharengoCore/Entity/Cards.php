<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

/**
 * Cards
 *
 * @ORM\Table(name="cards", uniqueConstraints={@ORM\UniqueConstraint(name="cards_code_key", columns={"code"})})
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\CardsRepository")
 */
class Cards
{
    /**
     * @var string
     *
     * @ORM\Column(name="rfid", type="text", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $rfid;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="text", nullable=false)
     * @ORM\Id
     */
    private $code;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_assigned", type="boolean", nullable=false)
     */
    private $isAssigned = false;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="text", nullable=true)
     */
    private $notes;

    /**
     * @var boolean
     *
     * @ORM\Column(name="assignable", type="boolean", nullable=false)
     */
    private $assignable = true;

    /**
     * @ORM\OneToOne(targetEntity="Customers", mappedBy="card")
     **/
    private $customer;

    /**
     * Set rfid
     *
     * @param string $rfid
     *
     * @return Cards
     */
    public function setRfid($rfid)
    {
        $this->rfid = $rfid;

        return $this;
    }

    /**
     * Get rfid
     *
     * @return string
     */
    public function getRfid()
    {
        return $this->rfid;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return Cards
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set isAssigned
     *
     * @param boolean $isAssigned
     *
     * @return Cards
     */
    public function setIsAssigned($isAssigned)
    {
        $this->isAssigned = $isAssigned;

        return $this;
    }

    /**
     * Get isAssigned
     *
     * @return boolean
     */
    public function getIsAssigned()
    {
        return $this->isAssigned;
    }

    /**
     * Set notes
     *
     * @param string $notes
     *
     * @return Cards
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Get assignable
     *
     * @return boolean
     */
    public function getAssignable()
    {
        return $this->assignable;
    }

    /**
     * Set assignable
     *
     * @param boolean $assignable
     *
     * @return Cards
     */
    public function setAssignable($assignable)
    {
        $this->assignable = $assignable;
    }

    /**
     * @param DoctrineHydrator
     * @return mixed[]
     */
    public function toArray(DoctrineHydrator $hydrator)
    {
        return $hydrator->extract($this);
    }

    /**
     * @return mixed
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param mixed $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }
}
