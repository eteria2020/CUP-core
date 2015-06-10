<?php

namespace SharengoCore\Service\DatatableQueryBuilders;

interface DatatableQueryBuilderInterface
{
    public function select();

    public function join();
}
