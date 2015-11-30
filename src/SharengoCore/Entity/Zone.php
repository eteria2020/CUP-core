<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Zone
 *
 * @ORM\Table(name="zone")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\ZoneRepository")
 */
class Zone
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="zone_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="area_invoice", type="string", nullable=false)
     */
    private $areaInvoice;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active;

    /**
     * @var boolean
     *
     * @ORM\Column(name="hidden", type="boolean", nullable=false)
     */
    private $hidden;

    /**
     * @var string
     *
     * @ORM\Column(name="invoice_description", type="string", nullable=false)
     */
    private $invoiceDescription;

    /**
     * @var boolean
     *
     * @ORM\Column(name="rev_geo", type="boolean", nullable=false)
     */
    private $revGeo;

    /**
     * @var string
     *
     * @ORM\Column(name="area_use", type="string", nullable=false)
     */
    private $areaUse;


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
     * Set name
     *
     * @param string $name
     *
     * @return Zone
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set areaInvoice
     *
     * @param string $areaInvoice
     *
     * @return Zone
     */
    public function setAreaInvoice($areaInvoice)
    {
        $this->areaInvoice = $areaInvoice;

        return $this;
    }

    /**
     * Get areaInvoice
     *
     * @return string
     */
    public function getAreaInvoice()
    {
        return $this->areaInvoice;
    }

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return Zone
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set hidden
     *
     * @param boolean $hidden
     *
     * @return Zone
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;

        return $this;
    }

    /**
     * Get hidden
     *
     * @return boolean
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * Set invoiceDescription
     *
     * @param string $invoiceDescription
     *
     * @return Zone
     */
    public function setInvoiceDescription($invoiceDescription)
    {
        $this->invoiceDescription = $invoiceDescription;

        return $this;
    }

    /**
     * Get invoiceDescription
     *
     * @return string
     */
    public function getInvoiceDescription()
    {
        return $this->invoiceDescription;
    }

    /**
     * Set revGeo
     *
     * @param boolean $revGeo
     *
     * @return Zone
     */
    public function setRevGeo($revGeo)
    {
        $this->revGeo = $revGeo;

        return $this;
    }

    /**
     * Get revGeo
     *
     * @return boolean
     */
    public function getRevGeo()
    {
        return $this->revGeo;
    }

    /**
     * Set areaUse
     *
     * @param string $areaUse
     *
     * @return Zone
     */
    public function setAreaUse($areaUse)
    {
        $this->areaUse = $areaUse;

        return $this;
    }

    /**
     * Get areaUse
     *
     * @return string
     */
    public function getAreaUse()
    {
        return $this->areaUse;
    }
}
