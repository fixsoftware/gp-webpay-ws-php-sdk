<?php

namespace FixSoftware\WebpayWS;

class Api {

    /** @var array */
    private $soapWrapperNameIrregulars = [
        'processMasterPaymentRevoke' => 'masterPaymentStatus'
        // TODO: add other irregularities
    ];

    /** @var Config */
    private $config;

    /** @var Signer */
    private $signer;

    /** @var Request */
    private $request;

    /** @var Response */
    private $response;

    /** @var \SoapClient */
    private $soapClient;

    private $method;
    private $params = [];

    public function __construct(Config $config) {

        $this->config = $config;
        $this->signer = new Signer($this->config->signerPrivateKeyPath, $this->config->signerPrivateKeyPassword, $this->config->signerGpPublicKeyPath);
        $this->signer->setLogPath($this->config->signerLogPath);
        $this->soapClient = new \SoapClient($this->config->wsdlPath, ['exceptions' => false]);

    }

    public function call($method, array $params) {

        $this->method = $method;
        $this->params = $params;
        $this->request = null;
        $this->response = null;

        try {

            // create Request
            $this->request = new Request($method, $this->config->provider, $this->config->merchantNumber, $params, $this->soapWrapperNameIrregulars);

            // sign Request
            $requestSignature = $this->signer->sign($this->request->getParams(), Signer::SIGNER_BASE64_DISABLE);
            $this->request->setSignature($requestSignature);

            // set Request data to SoapClient
            $this->soapClient->__setLocation($this->config->serviceUrl);
            $soapResponse = $this->soapClient->__soapCall($this->request->getSoapMethod(), $this->request->getSoapData());

            // create Response
            $this->response = new Response($method, $this->request->getMessageId(), $soapResponse, $this->soapWrapperNameIrregulars);

            // verify Response
            $this->signer->verify($this->response->getParams(), $this->response->getSignature(), !$this->response->hasError() ? Signer::SIGNER_BASE64_DISABLE : Signer::SIGNER_BASE64_ENABLE);

        } catch(\SoapFault $e) {
            throw new ApiException('SOAP exception:' . $e->getMessage(), $e->getCode(), $e);
        }

        return $this->response;

    }

    public function getRequest() {

        return $this->request;

    }

    public function getResponse() {

        return $this->response;

    }

}