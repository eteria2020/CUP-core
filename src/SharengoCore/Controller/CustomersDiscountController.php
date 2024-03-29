<?php

namespace SharengoCore\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use SharengoCore\Service\CustomersService;

class CustomersDiscountController extends AbstractRestfulController
{

    /**
     * @var CustomersService
     */
    private $customersService;

    public function __construct(
        CustomersService $customersService
    ) {
        $this->customersService = $customersService;
    }

    public function create($data)
    {
        if (!$this->requestFromServer($this->request)) {
            return $this->response->setStatusCode(403);
        }

        $email = $data['email'];
        $discount = $data['discount'];

        $customer = $this->customersService->findOneByEmail($email);

        // exists a customer with this email?
        if ($customer instanceof \SharengoCore\Entity\Customers) {
            // update ACL before uncomment next line
            $result = $this->customersService->updateCustomerDiscountRate($customer, $discount);

            if ($result) {
                return new JsonModel([
                    'response' => 'Discount updated'
                ]);

            } else {
                $this->response->setStatusCode(400);

                return new JsonModel([
                    'response' => 'Discount not updated: this customer has already a discount'
                ]);

            }

        }

        $this->response->setStatusCode(400);

        return new JsonModel([
            'response' => 'Customer not found'
        ]);
    }
}
