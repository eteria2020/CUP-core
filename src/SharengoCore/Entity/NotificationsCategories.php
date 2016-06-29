<?php

namespace SharengoCore\Entity;

// Internals
use SharengoCore\Entity\NotificationsProtocols;
// Externals
use Doctrine\ORM\Mapping as ORM;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

/**
 * NotificationsCategories
 *
 * @ORM\Table(name="messages_types")
 * @ORM\Entity
 */
class NotificationsCategories
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
     * @var NotificationsProtocols
     *
     * @ORM\ManyToOne(targetEntity="NotificationsProtocols")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="default_transport", referencedColumnName="name", nullable=true)
     * })
     */
    private $defaultProtocol;

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