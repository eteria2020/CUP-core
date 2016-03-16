<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ForeignDriversLicesValidation
 *
 * @ORM\Table(name="foreign_drivers_license_validation")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\ForeignDriversLicenseValidationRepository")
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
     * @ORM\ManyToOne(targetEntity="ForeignDriversLicenseUpload")
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

    public function __construct(ForeignDriversLicenseUpload $license)
    {
        $this->foreignDriversLicenseUpload = $license;
    }

    /**
     * @return DateTime
     */
    public function getRevokedAt()
    {
        return $this->revokedAt;
    }

    /**
     * @return DateTime
     */
    public function getValidatedAt()
    {
        return $this->validatedAt;
    }

    /**
     * @return ForeignDriversLicenseUpload
     */
    public function getForeignDriversLicenseUpload()
    {
        return $this->foreignDriversLicenseUpload;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Webuser
     */
    public function getValidatedBy()
    {
        return $this->validatedBy;
    }

    /**
     * @return Webuser
     */
    public function getRevokedBy()
    {
        return $this->revokedBy;
    }

    /**
     * @var Webuser $webuser
     * @return static
     */
    public function validate(Webuser $webuser)
    {
        $this->validatedAt = date_create();
        $this->validatedBy = $webuser;

        return $this;
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
}
