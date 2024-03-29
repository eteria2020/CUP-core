<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Countries
 *
 * @ORM\Table(name="countries")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\CountriesRepository")
 */
class Countries
{
    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=2, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="countries_code_seq", allocationSize=1, initialValue=1)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="text", nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="mctc", type="string", length=3, nullable=true)
     */
    private $mctc;

    /**
     * @var string
     *
     * @ORM\Column(name="cadastral_code", type="string")
     */
    private $cadastralCode;

    /**
     * @var string
     *
     * @ORM\Column(name="phone_code", type="string")
     */
    private $phoneCode;

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Countries
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
     * Get code for Motorizzazione civile
     *
     * @return string
     */
    public function getMctc()
    {
        return $this->mctc;
    }

    /**
     * Get code for stato estero cf
     *
     * @return string
     */
    public function getCadastralCode()
    {
        return $this->cadastralCode;
    }

    /**
     * Get international phone code number
     *
     * @return string
     */
    public function getPhoneCode()
    {
        return $this->phoneCode;
    }
}
