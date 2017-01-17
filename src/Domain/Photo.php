<?php

namespace Linkman\Domain;

use DateTime;

class Photo extends FileContent
{
    /**
     * @var int
     */
    protected $width;

    /**
     * @var int
     */
    protected $height;

    /**
     * @var string
     */
    protected $make;

    /**
     * @var string
     */
    protected $model;

    /**
     * @var string
     */
    protected $orientation;

    /**
     * @var string
     */
    protected $software;

    /**
     * @var string
     */
    protected $exposureTime;

    /**
     * @var string
     */
    protected $fNumber;

    /**
     * @var string
     */
    protected $iso;

    /**
     * @var DateTime
     */
    protected $lastChanged;

    /**
     * TODO: Move this switch out to the plugin and use set/get
     *
     * @param array $exifData
     */
    public function update(array $exifData)
    {
        foreach ($exifData as $key => $value) {
            $keyLower = strtolower($key);

            switch ($keyLower) {
                case 'filedatetime': break;
                case 'make': $this->make = $value; break;
                case 'model': $this->model = $value; break;
                case 'orientation': $this->orientation = $value; break;
                case 'software': $this->software = $value; break;
                case 'exposuretime': $this->exposureTime = $value; break;
                case 'fnumber': $this->fNumber = $value; break;
                case 'isospeedratings': $this->iso = $value; break;

                case 'width':
                case 'exifimagewidth': $this->width = $value; break;

                case 'height':
                case 'exifimagelength':
                case 'exifimageheight': $this->height = $value; break;
            }
        }
    }

    public function getWidth()
    {
        $orientation = $this->getOrientationAdjust();

        if ($orientation == 90 || $orientation == -90) {
            return $this->height;
        }

        return $this->width;
    }

    public function getHeight()
    {
        $orientation = $this->getOrientationAdjust();

        if ($orientation == 90 || $orientation == -90) {
            return $this->width;
        }

        return $this->height;
    }

    public function getOrientationAdjust()
    {
        switch ($this->orientation) {
            case 3:
                return 180;
            case 6:
                return -90;
            case 8:
                return 90;
        }
    }

    /**
     * @return string
     */
    public function getMake()
    {
        return $this->make;
    }

    /**
     * @return DateTime
     */
    public function getLastChanged()
    {
        return $this->lastChanged;
    }

    /**
     * @return string
     */
    public function getIso()
    {
        return $this->iso;
    }

    /**
     * @return string
     */
    public function getFNumber()
    {
        return $this->fNumber;
    }

    /**
     * @return string
     */
    public function getExposureTime()
    {
        return $this->exposureTime;
    }

    /**
     * @return string
     */
    public function getSoftware()
    {
        return $this->software;
    }

    /**
     * @return string
     */
    public function getOrientation()
    {
        return $this->orientation;
    }

    /**
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }
}
