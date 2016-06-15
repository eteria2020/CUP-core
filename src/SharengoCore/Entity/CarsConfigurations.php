<?php

namespace SharengoCore\Entity;

// Internals
use SharengoCore\Entity\Cars;
use SharengoCore\Entity\Fleet;
// Externals
use Doctrine\ORM\Mapping as ORM;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * CarsConfigurations
 *
 * @ORM\Table(name="cars_configurations")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\CarsConfigurationsRepository")
 */
class CarsConfigurations
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="car_configs_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var Fleet
     *
     * @ORM\ManyToOne(targetEntity="Fleet")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fleet_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $fleet;

    /**
     * @var string
     *
     * @ORM\Column(name="model", type="text", nullable=true)
     */
    private $model;

    /**
     * @var Cars
     *
     * @ORM\ManyToOne(targetEntity="Cars")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="car_plate", referencedColumnName="plate", nullable=true)
     * })
     */
    private $car;

    /**
     * @var string
     *
     * @ORM\Column(name="key", type="text", nullable=false)
     */
    private $key;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="text", nullable=true)
     */
    private $value;
    
    /**
     * @var string
     */
    const GLOBAL_TYPE = 'GLOBAL_TYPE';

    /**
     * @var string
     */
    const FLEET_TYPE = 'FLEET_TYPE';

    /**
     * @var string
     */
    const CAR_MODEL_TYPE = 'CAR_MODEL_TYPE';

    /**
     * @var string
     */
    const CAR_TYPE = 'CAR_TYPE';

    /**
     * @param DoctrineHydrator
     * @return mixed[]
     */
    public function toArray(DoctrineHydrator $hydrator)
    {
        $extractedCarConfigurations = $hydrator->extract($this);

        $extractedCarConfigurations['fleet'] = $this->getFleet()->toArray($hydrator);
        $extractedCarConfigurations['car'] = $this->getCar()->toArray($hydrator);

        return $extractedCarConfigurations;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param string $id
     *
     * @return CarsConfigurations
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get model
     *
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set model
     *
     * @param string $model
     *
     * @return CarsConfigurations
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set key
     *
     * @param string $key
     *
     * @return CarsConfigurations
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set value
     *
     * @param string $value
     *
     * @return CarsConfigurations
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get fleet
     *
     * @return Fleet
     */
    public function getFleet()
    {
        return $this->fleet;
    }

    /**
     * Set fleet
     *
     * @param Fleet $fleet
     *
     * @return CarsConfigurations
     */
    public function setFleet(Fleet $fleet)
    {
        $this->fleet = $fleet;

        return $this;
    }

    /**
     * Get car
     *
     * @return Cars
     */
    public function getCar()
    {
        return $this->car;
    }

    /**
     * Set car
     *
     * @param Cars $car
     *
     * @return CarsConfigurations
     */
    public function setCar(Cars $car)
    {
        $this->car = $car;

        return $this;
    }

    /**
     * Get plate
     *
     * @return string|null plate
     */
    public function getCarPlate()
    {
        if ($this->car instanceof Cars) {
            return $this->car->getPlate();
        } else {
            return null;
        }
    }

    /**
     * Get name
     *
     * @return string|null name
     */
    public function getFleetName()
    {
        if ($this->fleet instanceof Fleet) {
            return $this->fleet->getName();
        } else {
            return null;
        }
    }

    /**
     * Return the CarConfiguration priority type
     * with constant defined in this class.
     *
     * @return string
     */
    public function getPriority()
    {
        if ($this->getCarPlate() !== null) {
            return $this::CAR_TYPE;
        }
        if ($this->getModel() !== null) {
            return $this::CAR_MODEL_TYPE;
        }
        if ($this->getFleetName() !== null) {
            return $this::FLEET_TYPE;
        }
        return $this::GLOBAL_TYPE;
    }
}
