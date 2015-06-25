<?php

namespace SharengoCore\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Http\Client;

class ReservationsController extends AbstractRestfulController
{

    /**
     * @var string
     */
    private $url;

    public function __construct($url)
    {
        $this->url = sprintf($url, '');
    }

    public function getList()
    {

    	$client = new Client($this->url, array(
            'maxredirects' => 0,
            'timeout'      => 30
        ));

        $response = $client->send();
        
        return new JsonModel(json_decode($response->getBody(), true));
    }
 
    public function create($data)
    {
        
    }
 
    public function delete($id)
    {
        
    }
}
