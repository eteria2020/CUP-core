<?php

namespace SharengoCore\Service;

use SharengoCore\Bootstrap;
use SharengoCore\Entity\DiscountStatus;

use Mockery as M;

class DiscountStatusServiceTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->serviceManager = Bootstrap::getServiceManager();

        $this->em = M::mock('Doctrine\Orm\EntityManager');
        $this->em = M::mock('Doctrine\ORM\EntityManager');var_dump($this->em instanceof \Doctrine\ORM\EntityManagerInterface);
        $this->serviceManager->setAllowOverride(true);
        $this->serviceManager->setService('doctrine.entitymanager.orm_default', $this->em);


        $this->discountStatusService = $this->serviceManager->get('SharengoCore\Service\DiscountStatusService');

        $this->customer = M::mock('SharengoCore\Entity\Customers');
    }

    public function tearDown()
    {
        M::close();
    }

    public function testInsertStatus()
    {
        $this->customer->shouldReceive('hasDiscountStatus')->andReturn(false);

        $this->em->shouldReceive('persist');
        $this->em->shouldReceive('flush');

        $this->discountStatusService->upsertStatus($this->customer, 'status');
    }

    /*public function testUpdateStatus()
    {
        $this->customer->shouldReceive('hasDiscountStatus')->andReturn(true);
        $this->customer->shouldReceive('discountStatus')->andReturn(new DiscountStatus($this->customer, 'status'));

        $this->em->shouldReceive('persist');
        $this->em->shouldReceive('flush');

        $this->discountStatusService->upsertStatus($this->customer, 'status');
    }*/
}
