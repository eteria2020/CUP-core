<?php

namespace SharengoCore\Filter;

// Externals
use Zend\Filter\AbstractFilter;
use CrEOF\Spatial\PHP\Types\AbstractGeometry;

class GeoJsonToGeometry extends AbstractFilter
{
    /**
     * @var ServiceLocatorInterface
     */
    private $serviceLocator;

    /**
     * @var PostGisService
     */
    private $postGisService;
    
    public function __construct($options)
    {
        $this->postGisService = $options['postGisService'];
    }

    /**
     * This filter convert a GeoJson string to a 
     * PostGis Polygon.
     *
     * @param string $geoJson
     * @return Geometry
     */
    public function filter($geoJson)
    {
        $geomerty = $this->postGisService->getGeometryFromGeoJson($geoJson);

        if (! $geomerty instanceof AbstractGeometry) {
            error_log("\nGeometryInterface Not Valid\n",0);//throw new GeometryNotValidException();
        }

        return $geomerty;
    }
}