<?php

namespace SharengoCore\Entity\Queries;

use Doctrine\ORM\EntityManagerInterface;

class ConvertGeoJsonToGeometry extends NativeQuery
{
    /**
     * @var array
     */
    private $params = [];

    /**
     * @param EntityManagerInterface $em
     * @param string $geoJson
     */
    public function __construct(
        EntityManagerInterface $em,
        $geoJson
    ) {
        parent::__construct($em);
        $this->params = [
            'geoJson' => (string) $geoJson
        ];
    }

    /**
     * @return string
     */
    protected function sql()
    {
        return 'SELECT ST_AsBinary(ST_Force2D(ST_GeomFromGeoJSON(:geoJson))) AS geometry';
    }

    /**
     * @return array
     */
    protected function params()
    {
        return $this->params;
    }

    /**
     * @return array
     */
    protected function scalarResults()
    {
        return [
            ['column_name' => 'geometry', 'alias' => 'geometry', 'type' => 'geometry']
        ];
    }

    protected function resultMethod()
    {
        return 'getOneOrNullResult';
    }
}
