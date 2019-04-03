<?php

namespace SharengoCore\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Authentication\AuthenticationService;
use Zend\Http\Response;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\Invoices;
use SharengoCore\Entity\Webuser;

class PdfController extends AbstractActionController
{

    private $viewRenderer;

    private $pdfService;

    /**
     * @var InvoicesService
     */
    private $invoicesService;

    /**
     * @var AuthenticationService
     */
    private $authService;

    /**
     * @var array
     */
    private $config;

    /**
     * @var $serverInstance
     */
    private $serverInstance = null;

    public function __construct(
        $viewRenderer,
        $pdfService,
        $invoicesService,
        AuthenticationService $authService,
        array $config
    ) {
        $this->viewRenderer = $viewRenderer;
        $this->pdfService = $pdfService;
        $this->invoicesService = $invoicesService;
        $this->authService = $authService;
        $this->config = $config;

        if(isset($this->config['serverInstance'])) {
            $this->serverInstance = $this->config['serverInstance'];
        }
    }

    public function indexAction()
    {

        // get user id from AuthService
        $user = $this->authService->getIdentity();

        $invoiceId = urldecode($this->params('id'));

        $invoice = $this->invoicesService->getInvoiceById($invoiceId);

        if ($invoice != null) {
            if (($user instanceof Customers &&
                $invoice->getCustomer()->getId() == $user->getId()) ||
                $user instanceof Webuser
            ) {
                return $this->generatePdfFromInvoice($invoice);
            }
        }
        $response = new Response();
        $response->setStatusCode(403);
        return $response;
    }

    private function generatePdfFromInvoice(Invoices $invoice)
    {
        $this->pdfService->setOptions([
            'footer-right' => '[page]/[topage]',
            'footer-left' => 'Share \'N Go',
            'footer-font-name' => 'Arial Sans Serif',
            'footer-font-size' => '10',
            'footer-line' => true,
            'lowquality' => false,
            'image-quality' => 100
        ]);

        $layoutViewModel = $this->layout();
        $layoutViewModel->setTemplate('layout/pdf-layout');

        $viewModel = new ViewModel([
            'invoiceNumber' => $invoice->getInvoiceNumber(),
            'invoiceContent' => $invoice->getContent()
        ]);

        $instanceFolder = "";
        if(isset($this->serverInstance) && !is_null($this->serverInstance) && $this->serverInstance["id"] != "it_IT"){
            $instanceFolder = substr($this->serverInstance["id"], 0, 2)."/";
        }

        $templateVersion = $invoice->getContent()['template_version'];
        $viewModel->setTemplate('sharengo-core/pdf/'.$instanceFolder.'invoice-pdf-v' . $templateVersion);

        $layoutViewModel->setVariables([
            'content' => $this->viewRenderer->render($viewModel),
            'templateVersion' => $templateVersion
        ]);

        $htmlOutput = $this->viewRenderer->render($layoutViewModel);

        $output = $this->pdfService->getOutputFromHtml($htmlOutput);
        $response = $this->getResponse();
        $headers  = $response->getHeaders();
        $headers->addHeaderLine('Content-Type', 'application/pdf');
        $headers->addHeaderLine(
            'Content-Disposition',
            "attachment; filename=\"Fattura-" . $invoice->getInvoiceNumber() . ".pdf\""
        );
        $headers->addHeaderLine('Content-Length', strlen($output));

        $response->setContent($output);

        return $response;
    }
}
