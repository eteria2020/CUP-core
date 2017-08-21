<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * CustomersPoints
 *
 * @ORM\Table(name="customers_points")
 * @ORM\Entity
 */
class CustomersPoints
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="customers_points_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="customer_id", type="integer", nullable=false)
     */
    private $customerId;

    /**
     * @var integer
     *
     * @ORM\Column(name="webuser_id", type="integer", nullable=true)
     */
    private $webuserId;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active;

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
     * @var string
     *
     * @ORM\Column(name="operator", type="string", length=100, nullable=true)
     */
    private $operator;

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
     * @var integer
     *
     * @ORM\Column(name="promocode_id", type="integer", nullable=true)
     */
    private $promocodeId;

    /**
     * @var integer
     *
     * @ORM\Column(name="package_id", type="integer", nullable=true)
     */
    private $packageId;

    /**
     * @var integer
     *
     * @ORM\Column(name="transaction_id", type="integer", nullable=true)
     */
    private $transactionId;

    /**
     * @var integer
     *
     * @ORM\Column(name="invoice_id", type="integer", nullable=true)
     */
    private $invoiceId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="invoiced_at", type="datetime", nullable=true)
     */
    private $invoicedAt;


    
    /**
     * Get id
     *
     * @return integer
     */
    function getId() {
        return $this->id;
    }

    /**
     * Get customerId
     *
     * @return integer
     */
    function getCustomerId() {
        return $this->customerId;
    }

    /**
     * Get webuserId
     *
     * @return integer
     */
    function getWebuserId() {
        return $this->webuserId;
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
     * Get operator
     *
     * @return string
     */
    function getOperator() {
        return $this->operator;
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

    /**
     * Get promocodeId
     *
     * @return integer
     */
    function getPromocodeId() {
        return $this->promocodeId;
    }

    /**
     * Get packageId
     *
     * @return integer
     */
    function getPackageId() {
        return $this->packageId;
    }

    /**
     * Get transactionId
     *
     * @return integer
     */
    function getTransactionId() {
        return $this->transactionId;
    }

    /**
     * Get invoiceId
     *
     * @return integer
     */
    function getInvoiceId() {
        return $this->invoiceId;
    }

    /**
     * Get invoicedAt
     *
     * @return datetime
     */
    function getInvoicedAt() {
        return $this->invoicedAt;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setCustomerId($customerId) {
        $this->customerId = $customerId;
    }

    function setWebuserId($webuserId) {
        $this->webuserId = $webuserId;
    }

    function setActive($active) {
        $this->active = $active;
    }

    function setInsertTs(\DateTime $insertTs) {
        $this->insertTs = $insertTs;
    }

    function setUpdateTs(\DateTime $updateTs) {
        $this->updateTs = $updateTs;
    }

    function setTotal($total) {
        $this->total = $total;
    }

    function setResidual($residual) {
        $this->residual = $residual;
    }

    function setType($type) {
        $this->type = $type;
    }

    function setOperator($operator) {
        $this->operator = $operator;
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

    function setPromocodeId($promocodeId) {
        $this->promocodeId = $promocodeId;
    }

    function setPackageId($packageId) {
        $this->packageId = $packageId;
    }

    function setTransactionId($transactionId) {
        $this->transactionId = $transactionId;
    }

    function setInvoiceId($invoiceId) {
        $this->invoiceId = $invoiceId;
    }

    function setInvoicedAt(\DateTime $invoicedAt) {
        $this->invoicedAt = $invoicedAt;
    }
    
    
    
}

