<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

/**
 * Fares
 *
 * @ORM\Table(name="fares")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\FaresRepository")
 */
class Fares
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="fares_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var interger cost in eurocents
     *
     * @ORM\Column(name="motion_cost_per_minute", type="integer", nullable=false)
     */
    private $motionCostPerMinute;

    /**
     * @var integer cost in eurocents
     *
     * @ORM\Column(name="park_cost_per_minute", type="integer", nullable=false)
     */
    private $parkCostPerMinute;

    /**
     * @var string json representation of the price steps.
     *
     * every key in the json file represents a minutes quantity and its value
     * is the cost of a trip of those minutes in eurocents.
     *
     * @ORM\Column(name="cost_steps", type="string", nullable=false)
     */
    private $costSteps;

    public function __construct($motionCostPerMinute, $parkCostPerMinute, $costSteps)
    {
        $this->motionCostPerMinute = $motionCostPerMinute;
        $this->parkCostPerMinute = $parkCostPerMinute;
        $this->costSteps = $costSteps;
    }

    /**
     * @param DoctrineHydrator $hydrator
     * @return mixed
     */
    public function toArray(DoctrineHydrator $hydrator)
    {
        return $hydrator->extract($this);
    }

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
     * Get motion cost per minute
     *
     * @return integer
     */
    public function getMotionCostPerMinute()
    {
        return $this->motionCostPerMinute;
    }

    /**
     * Get park cost per minute
     *
     * @return integer
     */
    public function getParkCostPerMinute()
    {
        return $this->parkCostPerMinute;
    }

    /**
     * Get cost steps
     * in order for the algorithm to work correctly we need to return them in
     * decreasing order
     *
     * @return array
     */
    public function getCostSteps()
    {
        $costSteps = json_decode($this->costSteps, true);

        krsort($costSteps);

        return $costSteps;
    }
}
