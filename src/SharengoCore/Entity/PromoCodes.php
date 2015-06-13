<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Countries
 *
 * @ORM\Table(name="promo_codes")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\PromoCodesRepository")
 */
class PromoCodes
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="int", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="promocodes_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="promocode", type="string", nullable=false)
     */
    private $promocode;

    /**
     * @var \PromoCodesInfo
     *
     * @ORM\ManyToOne(targetEntity="PromoCodesInfo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="promocodesinfo_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $promocodesinfo;
    



}
