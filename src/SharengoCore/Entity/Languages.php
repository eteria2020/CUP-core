<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * languages
 *
 * @ORM\Table(name="languages")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\languagesRepository")
 */
class Languages
{
    /**
     * @var string
     *
     * @ORM\Column(name="code", type="text", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="code2", type="text", nullable=false)
     */
    private $code2;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="text", nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="params", type="text", nullable=true)
     */
    private $params;

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
     * Set code
     *
     * @param string $code
     *
     * @return Languages
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code2
     *
     * @return string
     */
    public function getCode2()
    {
        return $this->code2;
    }

    /**
     * Set code2
     *
     * @param string $code2
     *
     * @return Languages
     */
    public function setCode2($code2)
    {
        $this->code2 = $code2;

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
     * Set name
     *
     * @param string $name
     *
     * @return Languages
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get params
     *
     * @return string
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set params
     *
     * @param array|null $param
     *
     * @return Languages
     */
    public function setParams(array $params = null)
    {
        if (count($params) > 0 && null != $params) {
            $this->params = json_encode($params);
        } else {
            $this->params = null;
        }

        return $this;
    }

}
