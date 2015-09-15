<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

/**
 * CustomersBonusPackages
 *
 * @ORM\Table(name="customers_bonus_packages")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\CustomersBonusPackagesRepository")
 */
class CustomersBonusPackages
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="customers_bonus_packages_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", nullable=false)
     */
    private $code;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="inserted_ts", type="datetime", nullable=false)
     */
    private $insertedTs;

    /**
     * @var integer
     *
     * @ORM\Column(name="minutes", type="integer", nullable=false)
     */
    private $minutes;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=100, nullable=false)
     */
    private $type = 'promo';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="valid_from", type="datetime", nullable=false)
     */
    private $validFrom;

    /**
     * @var integer
     *
     * @ORM\Column(name="duration", type="integer", nullable=true)
     */
    private $duration;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="valid_to", type="datetime", nullable=true)
     */
    private $validTo;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="buyable_until", type="datetime", nullable=false)
     */
    private $buyableUntil;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var integer
     * @ORM\Column(name="cost", type="integer", nullable=false)
     */
    private $cost;

    /**
     * @param DoctrineHydrator
     * @return mixed[]
     */
    public function toArray(DoctrineHydrator $hydrator)
    {
        $package = $hydrator->extract($this);
        $package['validFrom'] = $this->validFrom->format("d-m-Y");
        $package['validTo'] = $this->validTo->format("d-m-Y");
        $package['buyableUntil'] = $this->buyableUntil->format("d-m-Y");

        return $package;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return CustomersBonusPackages
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getInsertedTs()
    {
        return $this->insertedTs;
    }

    /**
     * @param \DateTime $insertedTs
     *
     * @return CustomersBonusPackages
     */
    public function setInsertedTs($insertedTs)
    {
        $this->insertedTs = $insertedTs;

        return $this;
    }

    /**
     * @return integer
     */
    public function getMinutes()
    {
        return $this->minutes;
    }

    /**
     * @param integer $minutes
     *
     * @return CustomersBonusPackages
     */
    public function setMinutes($minutes)
    {
        $this->minutes = $minutes;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return CustomersBonusPackages
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getValidFrom()
    {
        return $this->validFrom;
    }

    /**
     * @param \DateTime $validFrom
     *
     * @return CustomersBonusPackages
     */
    public function setValidFrom($validFrom)
    {
        $this->validFrom = $validFrom;

        return $this;
    }

    /**
     * @return integer
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param integer $duration
     *
     * @return CustomersBonusPackages
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getValidTo()
    {
        return $this->validTo;
    }

    /**
     * @param \DateTime $validTo
     *
     * @return CustomersBonusPackages
     */
    public function setValidTo($validTo)
    {
        $this->validTo = $validTo;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getBuyableUntil()
    {
        return $this->buyableUntil;
    }

    /**
     * @param \DateTime $validTo
     *
     * @return CustomersBonusPackages
     */
    public function setBuyableUntil($buyableUntil)
    {
        $this->buyableUntil = $buyableUntil;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return CustomersBonusPackages
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return integer
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * @param integer $cost
     *
     * @return CustomersBonusPackages
     */
    public function setCost($cost)
    {
        $this->cost = $cost;

        return $this;
    }
}
