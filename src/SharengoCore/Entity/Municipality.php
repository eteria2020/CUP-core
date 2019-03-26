<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Municipalities
 *
 * @ORM\Table(name="italian_municipalities")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\MunicipalityRepository")
 */
class Municipality
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="italian_municipalities_id_seq", allocationSize=1, initialValue=0)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="cadastral_code", type="string")
     */
    private $cadastralCode;

    /**
     * @var string
     *
     * @ORM\Column(name="province", type="string")
     */
    private $province;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="foreign_name", type="string", nullable=true)
     */
    private $foreignName;

    /**
     * @var string
     *
     * @ORM\Column(name="istat_code", type="string", nullable=true)
     */
    private $istatCode;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

    /**
     * @var string
     *
     * @ORM\Column(name="zip_codes", type="string")
     */
    private $zipCodes;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getProvince()
    {
        return $this->province;
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
     * Array of zip codes
     *
     * @return string
     */
    public function getZipCodes()
    {
        return $this->zipCodes;
    }

    /**
     * Get cadastral code
     *
     * @return string
     */
    public function getCadastralCode()
    {
        return $this->cadastralCode;
    }
}
