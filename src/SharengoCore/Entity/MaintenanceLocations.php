<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * MaintenanceLocations
 *
 * @ORM\Table(name="maintenance_locations", indexes={@ORM\Index(name="IDX_52276ECB4B061DF9", columns={"fleet_id"})})
 * @ORM\Entity
 */
class MaintenanceLocations
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="maintenance_locations_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="location", type="text", nullable=false)
     */
    private $location;

    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean", nullable=false)
     */
    private $enabled;

    /**
     * @var \Fleets
     *
     * @ORM\ManyToOne(targetEntity="Fleets")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fleet_id", referencedColumnName="id")
     * })
     */
    private $fleet;


}

