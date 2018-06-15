<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * UserEventsCopy
 *
 * @ORM\Table(name="user_events_copy")
 * @ORM\Entity
 */
class UserEventsCopy
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="user_events_copy_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="webuser_id", type="integer", nullable=false)
     */
    private $webuserId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="insert_ts", type="datetime", nullable=false)
     */
    private $insertTs;

    /**
     * @var string
     *
     * @ORM\Column(name="topic", type="string", length=100, nullable=false)
     */
    private $topic;

    /**
     * @var string
     *
     * @ORM\Column(name="details", type="string", nullable=false)
     */
    private $details;


}

