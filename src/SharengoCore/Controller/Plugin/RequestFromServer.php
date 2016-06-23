<?php

namespace SharengoCore\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Http\Request;

class RequestFromServer extends AbstractPlugin
{
    /**
     * return bool
     */
    public function __invoke(Request $request)
    {
        return $request->getServer()->get('REMOTE_ADDR') === $request->getServer()->get('SERVER_ADDR');
    }
}
