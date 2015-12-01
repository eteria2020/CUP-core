<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Configurations
 *
 * @ORM\Table(name="configurations")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\ConfigurationsRepository")
 */
class Configurations
{
    const ALARM = 'alarm';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="configurations_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="text", nullable=false)
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="config_key", type="text", nullable=false)
     */
    private $configKey;

    /**
     * @var string
     *
     * @ORM\Column(name="config_value", type="text", nullable=false)
     */
    private $configValue;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * @return string
     */
    public function getConfigKey()
    {
        return $this->configKey;
    }

    /**
     * @param string $configKey
     */
    public function setConfigKey($configKey)
    {
        $this->configKey = $configKey;
    }

    /**
     * @return string
     */
    public function getConfigValue()
    {
        return $this->configValue;
    }

    /**
     * @param string $configValue
     */
    public function setConfigValue($configValue)
    {
        $this->configValue = $configValue;
    }
}
