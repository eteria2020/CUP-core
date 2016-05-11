<?php

namespace SharengoCore\Service;

// Internals
use SharengoCore\Entity\Queries\ConvertGeoJsonToGeometry;
use SharengoCore\Entity\Queries\ConvertGeomKMLToGeometry;
use SharengoCore\Exception\KMLFileNotValidException;
// Externals
use Doctrine\ORM\EntityManager;
use Zend\Config\Reader\Xml;
use SharengoCore\Utils\Kml;
use CrEOF\Spatial\PHP\Types\AbstractGeometry;

class PostGisService
{
    /** 
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param $entityManager EntityManager
     */
    public function __construct(
        EntityManager $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * This method return an istance of AbstractGeometry from a
     * given JSON.
     *
     * @return AbstractGeometry
     */
    public function getGeometryFromGeoJson($geoJson)
    {
        $query = new ConvertGeoJsonToGeometry(
            $this->entityManager,
            $geoJson
        );

        $geometry = $query();

        return $geometry['geometry'];
    }

    /**
     * This method return an istance of AbstractGeometry from a
     * given GeomKML Fragment (do not accept a complete KML file).
     * (for more info see: http://postgis.net/docs/manual-2.2/ST_GeomFromKML.html)
     *
     * @return AbstractGeometry
     */
    public function getGeometryFromGeomKMLFragment($geomKml)
    {
        $query = new ConvertGeomKMLToGeometry(
            $this->entityManager,
            $geomKml
        );

        $geometry = $query();

        return $geometry['geometry'];
    }

    /**
     * This method return an istance of AbstractGeometry from a
     * given GeomKML Filetype.
     * (for more info see: https://developers.google.com/kml/documentation)
     *
     * @throws RuntimeException
     * @throws KMLFileNotValidException
     *
     * @return AbstractGeometry
     */
    public function getGeometryFromGeomKMLFile($geomKmlFile)
    {
        // Create a new Zend\Config\ReaderInterfcace
        $xmlReader = new Xml();

        // Read and Convert KML to array.
        $data = $xmlReader->fromFile($geomKmlFile);
        
        // Get the content data.
        $placemark = $data['Document']['Placemark'];
        $kmlGeometryFragment = [];

        // Define accepted Geometry types.
        $geometries = [
            'Polygon','MultiPolygon','LineString',
            'MultiLineString','Point','MultiPoint'
        ];

        // Search for Geometries.
        foreach($geometries as $geometry){
            if(!empty($placemark[$geometry])){
                $kmlGeometryFragment[$geometry] = $placemark[$geometry];
            }
        }

        // If no geometry has been found, Throw error.
        if(empty($kmlGeometryFragment)){
            throw new KMLFileNotValidException();
        }

        // Create a new Zend\Config\WirterInterfcace
        $kmlWriter = new Kml();

        // Write and Convert array to KML Geometry Fragment
        // (XML type without header or additional info) 
        $xmlKmlGeometryFragmet = $kmlWriter->toString($kmlGeometryFragment);

        return $this->getGeometryFromGeomKMLFragment($xmlKmlGeometryFragmet);
    }
}

