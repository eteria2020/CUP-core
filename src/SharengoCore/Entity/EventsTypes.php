<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EventsTypes
 *
 * @ORM\Table(name="events_types")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\EventsTypesRepository")
 */
class EventsTypes
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="events_types_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string", nullable=false)
     */
    private $label;

    /**
     * @var string
     *
     * @ORM\Column(name="map_logic", type="string", nullable=false)
     */
    private $mapLogic;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=false)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="text", nullable=true)
     */
    private $notes;

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getMapLogic()
    {
        return $this->mapLogic;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Return a formatted description
     * @param Events $event
     * @return string
     */
    public function getFormattedDescription($event)
    {
        $description = $this->description;
        $description = str_replace('{txtval}', $event->getTxtval(), $description);
        $description = str_replace('{intval}', $event->getIntval(), $description);
        return $description;
    }

    /**
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }
}
