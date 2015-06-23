<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cards
 *
 * @ORM\Table(name="cards", uniqueConstraints={@ORM\UniqueConstraint(name="cards_code_key", columns={"code"})})
 * @ORM\Entity
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
}
