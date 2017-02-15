<?php

namespace SharengoCore\Entity;

class DiscountStatusTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $customer = new Customers();
        $discountStatus = new DiscountStatus($customer, 'status');

        $this->assertSame('status', $discountStatus->status());
    }

    public function testUpdateStatus()
    {
        $customer = new Customers();
        $discountStatus = new DiscountStatus($customer, 'status');

        $discountStatus->updateStatus('newStatus');

        $this->assertSame('newStatus', $discountStatus->status());
    }
}
