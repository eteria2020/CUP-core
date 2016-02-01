<?php

namespace SharengoCore\Entity;

use Cartasi\Entity\Transactions;

use Doctrine\ORM\Mapping as ORM;

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
     * @var string
     *
     * @ORM\Column(name="notes", type="text", nullable=true)
     */
    private $notes;

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
     * @return \DateTime
     */
    public function getInsertedTs()
    {
        return $this->insertedTs;
    }

    /**
     * @return integer
     */
    public function getMinutes()
    {
        return $this->minutes;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return \DateTime
     */
    public function getValidFrom()
    {
        return $this->validFrom;
    }

    /**
     * @return integer
     */
    public function getDuration()
    {
        if (!is_null($this->duration)) {
            return $this->duration;
        }

        return $this->validTo->diff($this->validFrom)->format('%a');
    }

    /**
     * @return \DateTime
     */
    public function getValidTo()
    {
        if (!is_null($this->validTo)) {
            return $this->validTo;
        }

        $durationInterval = new \DateInterval('P' . $this->duration . 'D');
        $from = max(date_create(), $this->validFrom);
        return $from->add($durationInterval);
    }

    /**
     * @return \DateTime
     */
    public function getBuyableUntil()
    {
        return $this->buyableUntil;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return integer
     */
    public function getCost()
    {
        return $this->cost;
    }

    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param Customers $customer
     * @return CustomersBonus
     */
    public function generateCustomerBonus(Customers $customer)
    {
        return CustomersBonus::createFromBonusPackage($customer, $this);
    }
}
