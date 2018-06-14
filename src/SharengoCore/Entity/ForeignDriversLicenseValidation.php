<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ForeignDriversLicesValidation
 *
 * @ORM\Table(name="foreign_drivers_license_validation")
 * @ORM\Entity
 */
class ForeignDriversLicenseValidation
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     */
    private $id;

    /**
     * @var ForeignDriversLicenseUpload
     *
     * @ORM\ManyToOne(targetEntity="ForeignDriversLicenseUpload", inversedBy="validations")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="foreign_drivers_license_upload_id", referencedColumnName="id")
     * })
     */
    private $foreignDriversLicenseUpload;


    /**
     * @var DateTime
     *
     * @ORM\Column(name="validated_at", type="datetime", nullable=true)
     */
    private $validatedAt;

    /**
     * @var Webuser
     *
     * @ORM\ManyToOne(targetEntity="Webuser")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="validated_by", referencedColumnName="id", nullable=true)
     * })
     */
    private $validatedBy;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="revoked_at", type="datetime", nullable=true)
     */
    private $revokedAt;

    /**
     * @var Webuser
     *
     * @ORM\ManyToOne(targetEntity="Webuser")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="revoked_by", referencedColumnName="id", nullable=true)
     * })
     */
    private $revokedBy;

    public function __construct(ForeignDriversLicenseUpload $license, Webuser $webuser)
    {
        $this->foreignDriversLicenseUpload = $license;
        $this->validatedAt = date_create();
        $this->validatedBy = $webuser;
    }

    /**
     * @var Webuser $webuser
     * @return static
     */
    public function revoke(Webuser $webuser)
    {
        $this->revokedAt = date_create();
        $this->revokedBy = $webuser;

        return $this;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return !is_null($this->validatedAt) && is_null($this->revokedAt);
    }
}
