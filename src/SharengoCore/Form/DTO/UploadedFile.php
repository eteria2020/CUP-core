<?php

namespace SharengoCore\Form\DTO;

class UploadedFile
{
    /**
     * @var string
     */
    private $userFileName;

    /**
     * @var string
     */
    private $userFileType;

    /**
     * @var string
     */
    private $fileTemporaryLocation;

    /**
     * @var int
     */
    private $fileSize;

    public function __construct(
        $userFileName,
        $userFileType,
        $fileTemporaryLocation,
        $fileSize
    ) {
        $this->userFileName = $userFileName;
        $this->userFileType = $userFileType;
        $this->fileTemporaryLocation = $fileTemporaryLocation;
        $this->fileSize = $fileSize;
    }

    /**
     * @return string
     */
    public function getTemporaryLocation()
    {
        return $this->fileTemporaryLocation;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->userFileName;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->userFileType;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->fileSize;
    }
}
