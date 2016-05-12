<?php

namespace SharengoCore\Entity;

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
     * @var \Fleet
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
     * @var \Cars
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
     * @ORM\Column(name="value", type="text", nullable=false)
     */
    private $value;

    public function __construct()
    {
        $this->trips = new ArrayCollection();
    }

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
     * Get model
     *
     * @return string
     */
    public function getModel()
    {
        return $this->model;
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
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get fleet
     *
     * @return \SharengoCore\Entity\Fleet
     */
    public function getFleet()
    {
        return $this->fleet;
    }

    /**
     * Get car
     *
     * @return \SharengoCore\Entity\Cars
     */
    public function getCar()
    {
        return $this->car;
    }

    /**
     * Get  plate
     *
     * @return \SharengoCore\Entity\Cars plate
     */
    public function getCarPlate()
    {
        if( isset($this->car) ){
            return $this->car->getPlate();
        } else {
            return null;
        }
    }

    /**
     * Get name
     *
     * @return \SharengoCore\Entity\Fleet name
     */
    public function getFleetName()
    {
        if( isset($this->fleet) ){
            return $this->fleet->getName();
        } else {
            return null;
        }
    }

    /**
     * Set value
     *
     * @param $value string
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getPriority()
    {
        if ( $this->getCarPlate() !== null ){
            return 'Configurazione Specifica di un Auto';
        }
        if ( $this->getModel() !== null ){
            return 'Configurazione di un Modello di Auto';
        }
        if ( $this->getFleetName() !== null ){
            return 'Configurazione di una Citta\'';
        }
        return 'Configurazione Globale';
    }
}
