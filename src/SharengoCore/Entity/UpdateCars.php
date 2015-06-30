<?php

namespace SharengoCore\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * UpdateCars
 *
 * @ORM\Table(name="update_cars", indexes={@ORM\Index(name="IDX_41AB4A8BAE35528C", columns={"car_plate"}), @ORM\Index(name="IDX_41AB4A8B49279951", columns={"webuser_id"})})
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\UpdateCarsRepository")

 */
class UpdateCars
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="update_cars_id_seq", allocationSize=1, initialValue=1)
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
     * @ORM\Column(name="update", type="datetime", nullable=false)
     */
    private $update;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", nullable=false)
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="note", type="text", nullable=true)
     */
    private $note;

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
     * Set update
     *
     * @param \DateTime $update
     *
     * @return UpdateCars
     */
    public function setUpdate($update)
    {
        $this->update = $update;

        return $this;
    }

    /**
     * Get update
     *
     * @return \DateTime
     */
    public function getUpdate()
    {
        return $this->update;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return UpdateCars
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Get note
     *
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Set note
     *
     * @param string $note
     */
    public function setNote($note)
    {
        $this->note = $note;
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