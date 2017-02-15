<?php

namespace SharengoCore\Entity\Queries;

use Doctrine\ORM\EntityManagerInterface;

class ConvertGeomKMLToGeometry extends NativeQuery
{
    /**
     * @var array
     */
    private $params = [];

    /**
     * @param EntityManagerInterface $em
     * @param string $geomKml
     */
    public function __construct(
        EntityManagerInterface $em,
        $geomKml
    ) {
        parent::__construct($em);
        $this->params = [
            'geomKml' => (string) $geomKml
        ];
    }

    /**
     * @return string
     */
    protected function sql()
    {
        return 'SELECT ST_AsBinary(ST_Force2D(ST_GeomFromKML(:geomKml))) AS geometry';
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
