<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Zone
 *
 * @ORM\Table(name="zone_price")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\ZonePricesRepository")
 */
class ZonePrices
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="zone_prices_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="note", type="string", nullable=false)
     */
    private $note;

    /**
     * @var integer
     *
     * @ORM\Column(name="cost", type="integer", nullable=false)
     */
    private $cost;

    /**
     * @var integer
     *
     * @ORM\Column(name="bonus", type="integer", nullable=false)
     */
    private $bonus;

    /**
     * @var \ZoneGroups
     *
     * @ORM\ManyToOne(targetEntity="ZoneGroups")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_group_open", referencedColumnName="id")
     * })
     */
    private $groupOpen;

    /**
     * @var \ZoneGroups
     *
     * @ORM\ManyToOne(targetEntity="ZoneGroups")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_group_close", referencedColumnName="id")
     * })
     */
    private $groupClose;



    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set note
     *
     * @param string $note
     *
     * @return ZonePrices
     */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }

    /**
     * Get note
     *
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Set cost
     *
     * @param integer $cost
     *
     * @return ZonePrices
     */
    public function setCost($cost)
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * Get cost
     *
     * @return integer
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * Set bonus
     *
     * @param integer $bonus
     *
     * @return ZonePrices
     */
    public function setBonus($bonus)
    {
        $this->bonus = $bonus;

        return $this;
    }

    /**
     * Get bonus
     *
     * @return integer
     */
    public function getBonus()
    {
        return $this->bonus;
    }

    /**
     * Set groupOpen
     *
     * @param \SharengoCore\Entity\ZoneGroups $groupOpen
     *
     * @return ZonePrices
     */
    public function setGroupOpen(\SharengoCore\Entity\ZoneGroups $groupOpen = null)
    {
        $this->groupOpen = $groupOpen;

        return $this;
    }

    /**
     * Get groupOpen
     *
     * @return \SharengoCore\Entity\ZoneGroups
     */
    public function getGroupOpen()
    {
        return $this->groupOpen;
    }

    /**
     * Set groupClose
     *
     * @param \SharengoCore\Entity\ZoneGroups $groupClose
     *
     * @return ZonePrices
     */
    public function setGroupClose(\SharengoCore\Entity\ZoneGroups $groupClose = null)
    {
        $this->groupClose = $groupClose;

        return $this;
    }

    /**
     * Get groupClose
     *
     * @return \SharengoCore\Entity\ZoneGroups
     */
    public function getGroupClose()
    {
        return $this->groupClose;
    }
}
