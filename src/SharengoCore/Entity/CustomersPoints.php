<?php

namespace SharengoCore\Entity;

use SharengoCore\Exception\NonPositiveIntegerException;

use Doctrine\ORM\Mapping as ORM;

/**
 * CustomersPoints
 *
 * @ORM\Table(name="customers_points")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\CustomersPointsRepository")
 */
class CustomersPoints
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="customerspoints_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    
    
    /**
     * @var \Customers
     *
     * @ORM\ManyToOne(targetEntity="Customers", inversedBy="customerspoints")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $customer;

    /**
     * @var \Webuser
     *
     * @ORM\ManyToOne(targetEntity="Webuser")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="webuser_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $webuser;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active = true;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="insert_ts", type="datetime", nullable=false)
     */
    private $insertTs;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_ts", type="datetime", nullable=false)
     */
    private $updateTs;

    /**
     * @var integer
     *
     * @ORM\Column(name="total", type="integer", nullable=false)
     */
    private $total;

    /**
     * @var integer
     *
     * @ORM\Column(name="residual", type="integer", nullable=false)
     */
    private $residual;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=100, nullable=false)
     */
    private $type;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="valid_from", type="datetime", nullable=true)
     */
    private $validFrom;

    /**
     * @var integer
     *
     * @ORM\Column(name="duration_days", type="integer", nullable=true)
     */
    private $durationDays;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="valid_to", type="datetime", nullable=true)
     */
    private $validTo;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;


    
    /**
     * Get id
     *
     * @return integer
     */
    function getId() {
        return $this->id;
    }

    /**
     * Get customer
     *
     * @return integer
     */
    function getCustomer() {
        return $this->customer;
    }

    /**
     * Get webuser
     *
     * @return Webuser
     */
    public function getWebuser()
    {
        return $this->webuser;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    function getActive() {
        return $this->active;
    }

    /**
     * Get insertTs
     *
     * @return datetime
     */
    function getInsertTs() {
        return $this->insertTs;
    }

    /**
     * Get updateTs
     *
     * @return datetime
     */
    function getUpdateTs() {
        return $this->updateTs;
    }

    /**
     * Get total
     *
     * @return integer
     */
    function getTotal() {
        return $this->total;
    }

    /**
     * Get residual
     *
     * @return integer
     */
    function getResidual() {
        return $this->residual;
    }

    /**
     * Get type
     *
     * @return string
     */
    function getType() {
        return $this->type;
    }

    /**
     * Get validFrom
     *
     * @return datetime
     */
    function getValidFrom() {
        return $this->validFrom;
    }

    /**
     * Get durationDays
     *
     * @return integer
     */
    function getDurationDays() {
        return $this->durationDays;
    }

    /**
     * Get validTo
     *
     * @return datetime
     */
    function getValidTo() {
        return $this->validTo;
    }

    /**
     * Get description
     *
     * @return text
     */
    function getDescription() {
        return $this->description;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setCustomer($customer) {
        $this->customer = $customer;
    }

    /**
     * Set webuser
     *
     * @param Webuser $webuser
     *
     * @return CustomersBonus
     */
    public function setWebuser(Webuser $webuser = null)
    {
        $this->webuser = $webuser;

        return $this;
    }
    
    

    function setActive($active) {
        $this->active = $active;
    }

    /**
     * Set insertTs
     *
     * @param \DateTime $insertTs
     *
     * @return CustomersBonus
     */
    public function setInsertTs($insertTs)
    {
        $this->insertTs = $insertTs;

        return $this;
    }

    function setUpdateTs(\DateTime $updateTs) {
        $this->updateTs = $updateTs;
    }

    function setTotal($total) {
        $this->total = $total;
    }

    /**
     * Set residual
     *
     * @param integer $residual
     *
     * @return CustomersBonus
     */
    public function setResidual($residual)
    {
        $this->residual = $residual;
        $this->touch();

        return $this;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return CustomersBonus
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    function setValidFrom(\DateTime $validFrom) {
        $this->validFrom = $validFrom;
    }

    function setDurationDays($durationDays) {
        $this->durationDays = $durationDays;
    }

    function setValidTo(\DateTime $validTo) {
        $this->validTo = $validTo;
    }

    function setDescription($description) {
        $this->description = $description;
    }
    
    public function canBeDeleted()
    {
        /*return $this->getTotal() == $this->getResidual() &&
               !$this->impliesSubscriptionDiscount();*/
        
        return true;
    }
    
    /**
     * Updates the updateTs
     */
    private function touch()
    {
        $this->updateTs = date_create();
    }
    
}

