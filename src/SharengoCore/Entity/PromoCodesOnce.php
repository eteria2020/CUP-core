<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PromoCodesOnce
 *
 * @ORM\Table(name="promo_codes_once")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\PromoCodesOnceRepository")
 */
class PromoCodesOnce {

    /**
     * @var int $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="promocodesonce_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var int $promocodesinfo
     *
     * @ORM\ManyToOne(targetEntity="PromoCodesInfo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="promocodesinfo_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $promocodesinfo;

    /**
     * @var string $promocode
     *
     * @ORM\Column(name="promocode", type="string", nullable=false, unique=true)
     */
    private $promocode;

    /**
     * @var Customers
     *
     * @ORM\ManyToOne(targetEntity="Customers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $customer;

    /**
     * @var CustomersBonus
     *
     * @ORM\ManyToOne(targetEntity="CustomersBonus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_bonus_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $customerBonus;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="used_ts", type="datetime", nullable=true)
     */
    private $usedTs;

    /**
     * Get getId
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set customer
     *
     * @param int $promocodesinfo
     *
     * @return PromoCodesInfo
     */
    public function setPromoCodesInfo(\SharengoCore\Entity\PromoCodesInfo $promocodesinfo = null) {
        $this->promocodesinfo = $promocodesinfo;

        return $this;
    }

    /**
     * Get promocodesinfo
     *
     * @return int
     */
    public function getPromoCodesInfo() {
        return $this->promocodesinfo;
    }

    /**
     * Set customer
     *
     * @param int $customer
     *
     * @return Customer
     */
    public function setCustomer($customer) {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Get customer
     *
     * @return int
     */
    public function getCustomer() {
        return $this->customer;
    }

        /**
     * Set CustomerBonus
     *
     * @param int $customerBonus
     *
     * @return CustomerBonus
     */
    public function setCustomerBonus($customerBonus) {
        $this->customerBonus = $customerBonus;

        return $this;
    }

    /**
     * Get CustomerBonus
     *
     * @return CustomerBonus
     */
    public function getCustomerBonus() {
        return $this->customerBonus;
    }

    /**
     * Set usedTs
     *
     * @param \DateTime $usedTs
     *
     * @return PromoCodesInfo
     */
    public function setUsedTs($usedTs) {
        $this->usedTs = $usedTs;

        return $this;
    }

    /**
     * Get usedTs
     *
     * @return \DateTime
     */
    public function getUsedTs() {
        return $this->usedTs;
    }

    public function __construct($promoCodesInfo, $promocode) {
        $this->promocodesinfo = $promoCodesInfo;
        $this->promocode = $promocode;
    }

}
