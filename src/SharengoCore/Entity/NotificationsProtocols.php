<?php

namespace SharengoCore\Entity;

// Externals
use Doctrine\ORM\Mapping as ORM;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

/**
 * NotificationsProtocols
 *
 * @ORM\Table(name="messages_transports")
* @ORM\Entity
 */
class NotificationsProtocols
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="text", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}