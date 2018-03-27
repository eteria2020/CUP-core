<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PartnersCustomers
 *
 * @ORM\Table(name="partners_customers", indexes={@ORM\Index(name="IDX_B9B8F1CA9395C3F3", columns={"customer_id"}), @ORM\Index(name="IDX_B9B8F1CA9393F8FE", columns={"partner_id"})})
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\PartnersCustomersRepository")
 */
class PartnersCustomers
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="partners_customers_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="inserted_ts", type="datetime", nullable=false)
     */
    private $insertedTs;

    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean", nullable=false)
     */
    private $enabled = true;

    /**
     * @var \Customers
     *
     * @ORM\ManyToOne(targetEntity="Customers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
     * })
     */
    private $customer;

    /**
     * @var \Partners
     *
     * @ORM\ManyToOne(targetEntity="Partners")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="partner_id", referencedColumnName="id")
     * })
     */
    private $partner;

    public function __construct() {
        $this->insertedTs = new \DateTime("now");
    }

    /**
     * 
     * @param \SharengoCore\Entity\Partners $partner
     */
    public function setPartner(Partners $partner) {
        $this->partner =$partner;
    }

    /**
     * 
     * @param \SharengoCore\Entity\Customers $customer
     */
    public function setCustomer(Customers $customer) {
        $this->customer =$customer;
    }
}

