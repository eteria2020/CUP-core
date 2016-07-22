<?php

namespace SharengoCore\Entity;

use SharengoCore\Exception\CodeTooLongException;

use Doctrine\ORM\Mapping as ORM;

/**
 * CarrefourUsedCode
 *
 * @ORM\Table(name="carrefour_used_codes")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\CarrefourUsedCodeRepository")
 */
class CarrefourUsedCode
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="carrefour_used_codes_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \SharengoCore\Entity\Customers
     *
     * @ORM\ManyToOne(targetEntity="SharengoCore\Entity\Customers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
     * })
     */
    private $customer;

    /**
     * @var \SharengoCore\Entity\CustomersBonus
     *
     * @ORM\ManyToOne(targetEntity="SharengoCore\Entity\CustomersBonus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customers_bonus_id", referencedColumnName="id")
     * })
     */
    private $customersBonus;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=24, nullable=false)
     */
    private $code;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="inserted_ts", type="datetime", nullable=false)
     */
    private $insertedTs;

    /**
     * @param Customers $customer
     * @param CustomersBonus $customersBonus
     * @param string $code
     */
    public function __construct(
        Customers $customer,
        CustomersBonus $customersBonus,
        $code
    ) {
        $this->setCustomer($customer);
        $this->setCustomersBonus($customersBonus);
        $this->setCode($code);
        $this->setInsertedTs(date_create());
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->getId();
    }

    /**
     * @return Customers
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param Customers $customer
     */
    private function setCustomer(Customers $customer)
    {
        $this->customer = $customer;
    }

    /**
     * @return CustomersBonus
     */
    public function getCustomersBonus()
    {
        return $this->customersBonus;
    }

    /**
     * @param CustomersBonus $customersBonus
     */
    private function setCustomersBonus(CustomersBonus $customersBonus)
    {
        $this->customersBonus = $customersBonus;
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
     */
    private function setCode($code)
    {
        if (strlen($code) > 24) {
            throw new CodeTooLongException(strlen($code));
        }

        $this->code = $code;
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
     */
    private function setInsertedTs(\DateTime $insertedTs)
    {
        $this->insertedTs = $insertedTs;
    }
}
