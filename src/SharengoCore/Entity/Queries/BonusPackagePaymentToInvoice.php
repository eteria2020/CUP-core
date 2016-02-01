<?php

namespace SharengoCore\Entity\Queries;

use Doctrine\ORM\EntityManagerInterface;

class BonusPackagePaymentToInvoice extends Query
{
    protected function dql()
    {
        return 'SELECT bpp FROM \SharengoCore\Entity\BonusPackagePayment bpp '.
            'WHERE bpp.invoice IS NULL';
    }
}
