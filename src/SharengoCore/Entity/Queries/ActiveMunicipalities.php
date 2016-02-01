<?php

namespace SharengoCore\Entity\Queries;

use Doctrine\ORM\EntityManagerInterface;

final class ActiveMunicipalities extends Query
{
    /**
     * @var string $province
     */
    private $province;

    public function __construct(
        EntityManagerInterface $entityManager,
        $province
    ) {
        $this->province = $province;

        parent::__construct($entityManager);
    }

    protected function dql()
    {
        $dql =  'SELECT m FROM \SharengoCore\Entity\Municipality m '.
            'WHERE m.active = :active ';

        if (!is_null($this->province)) {
            $dql .= 'AND m.province = :province ';
        }

        $dql .= 'ORDER BY m.name ASC';

        return $dql;
    }

    protected function params()
    {
        if (empty($this->province)) {
            return [];
        }

        return [
            'province' => $this->province,
            'active' => true
        ];
    }
}
