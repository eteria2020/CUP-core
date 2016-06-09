<?php

namespace SharengoCore\Filter;

// Internals
use SharengoCore\Exception\GeometryNotValidException;
// Externals
use Zend\Filter\AbstractFilter;
use CrEOF\Spatial\PHP\Types\AbstractGeometry;

class GeoJsonToGeometry extends AbstractFilter
{
    /**
     * @var PostGisService
     */
    private $postGisService;

    public function __construct(array $options)
    {
        $this->postGisService = $options['postGisService'];
    }

    /**
     * This filter convert a GeoJson string to a
     * PostGis Polygon.
     *
     * @param string $geoJson
     * @throws GeometryNotValidException
     * @return Geometry
     */
    public function filter($geoJson)
    {
        $geomerty = $this->postGisService->getGeometryFromGeoJson($geoJson);

        if (! $geomerty instanceof AbstractGeometry) {
            throw new GeometryNotValidException();
        }

        return $geomerty;
    }
}
