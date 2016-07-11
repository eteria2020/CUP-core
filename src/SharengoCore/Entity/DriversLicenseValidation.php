<?php

namespace SharengoCore\Entity;

use MvLabsDriversLicenseValidation\Response;

use Doctrine\ORM\Mapping as ORM;

/**
 * DriversLicenseValidation
 *
 * @ORM\Table(name="drivers_license_validations")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\DriversLicenseValidationRepository")
 */
class DriversLicenseValidation
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="drivers_license_validations_id_seq", allocationSize=1, initialValue=0)
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
     * @var boolean
     *
     * @ORM\Column(name="valid", type="boolean", nullable=false)
     */
    private $valid;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="text", nullable=false)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text", nullable=false)
     */
    private $message;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="generated_ts", type="datetime", nullable=false)
     */
    private $generatedTs;

    /**
     * @param Customers $customer
     * @param boolean $valid
     * @param string $code
     * @param string $message
     */
    public function __construct(Customers $customer, $valid, $code, $message)
    {
        $this->customer = $customer;
        $this->valid = $this->setValid($valid);
        $this->code = $this->setCode($code);
        $this->message = $this->setMessage($message);
        $this->generatedTs = $this->setGeneratedTs(date_create());
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return boolean
     */
    public function isValid()
    {
        return $this->valid;
    }

    /**
     * @param boolean $valid
     */
    private function setValid($valid)
    {
        $this->valid = $valid;
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
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    private function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return \DateTime
     */
    public function getGeneratedTs()
    {
        return $this->generatedTs();
    }

    /**
     * @param \DateTime $generatedTs
     */
    private function setGeneratedTs(\DateTime $generatedTs)
    {
        $this->generatedTs = $generatedTs;
    }
}
