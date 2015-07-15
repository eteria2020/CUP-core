<?php

namespace SharengoCore\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * CarsMaintenance
 *
 * @ORM\Table(name="cars_maintenance", indexes={@ORM\Index(name="IDX_41AB4A8BAE35528C", columns={"car_plate"}), @ORM\Index(name="IDX_41AB4A8B49279951", columns={"webuser_id"})})
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\CarsMaintenanceRepository")

 */
class CarsMaintenance
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="cars_maintenance_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="location", type="text", nullable=false)
     */
    private $location;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_ts", type="datetime", nullable=false)
     */
    private $updateTs;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="text", nullable=true)
     */
    private $notes;

    /**
     * @var \Cars
     *
     * @ORM\ManyToOne(targetEntity="Cars")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="car_plate", referencedColumnName="plate")
     * })
     */
    private $carPlate;

    /**
     * @var \Webuser
     *
     * @ORM\ManyToOne(targetEntity="Webuser")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="webuser_id", referencedColumnName="id")
     * })
     */
    private $webuser;


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
     * Set location
     *
     * @param string $location
     *
     * @return UpdateCars
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set updateTs
     *
     * @param \DateTime $updateTs
     *
     * @return UpdateCars
     */
    public function setUpdateTs($updateTs)
    {
        $this->updateTs = $updateTs;

        return $this;
    }

    /**
     * Get updateTs
     *
     * @return \DateTime
     */
    public function getUpdateTs()
    {
        return $this->updateTs;
    }

    /**
     * Get notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set notes
     *
     * @param string $notes
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
    }

    /**
     * Set carPlate
     *
     * @param \SharengoCore\Entity\Cars $carPlate
     *
     * @return UpdateCars
     */
    public function setCarPlate(\SharengoCore\Entity\Cars $carPlate = null)
    {
        $this->carPlate = $carPlate;

        return $this;
    }

    /**
     * Get carPlate
     *
     * @return \SharengoCore\Entity\Cars
     */
    public function getCarPlate()
    {
        return $this->carPlate;
    }

    /**
     * Set webuser
     *
     * @param \SharengoCore\Entity\Webuser $webuser
     *
     * @return UpdateCars
     */
    public function setWebuser(\SharengoCore\Entity\Webuser $webuser = null)
    {
        $this->webuser = $webuser;

        return $this;
    }

    /**
     * Get webuser
     *
     * @return \SharengoCore\Entity\Webuser
     */
    public function getWebuser()
    {
        return $this->webuser;
    }
}