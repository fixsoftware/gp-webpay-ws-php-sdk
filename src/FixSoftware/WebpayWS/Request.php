<?php

namespace FixSoftware\WebpayWS;

class Request {

    /** @var string */
    private $method;

    /** @var string */
    private $messageId;

    /** @var array */
    private $params = [];

    /** @var string */
    private $soapMethod;

    /** @var string */
    private $soapName;

    /** @var array */
    private $soapData;

    /** @var string */
    private $signature;

    public function __construct($method, $provider, $merchantNumber, $additionalParams) {

        $this->method = $method;
        $this->soapMethod = $this->method;
        $this->messageId = time() . mt_rand(10000, 99999) . '+' . $provider . '+' . $merchantNumber . '+' . $this->getSoapMethod();

        $this->params['messageId'] = $this->messageId;
        $this->params['provider'] = $provider;
        $this->params['merchantNumber'] = $merchantNumber;

        $this->params = array_merge($this->params, $additionalParams);

        $this->soapName = lcfirst(substr($this->method, $this->ucpos($this->method))) . 'Request';
        $this->soapData = [[$this->soapName => $this->getParams()]];

    }

    public function setSignature($signature) {

        $this->signature = $signature;
        $this->soapData[0][$this->soapName]['signature'] = $signature;

    }

    public function getParams() {

        return $this->params;

    }

    public function getMessageId() {

        return $this->messageId;

    }

    public function getSoapData() {

        return $this->soapData;

    }

    public function getSoapMethod() {

        return $this->soapMethod;

    }

    private function ucpos($subject) {
        $n = preg_match( '/[A-Z]/', $subject, $matches, PREG_OFFSET_CAPTURE );
        return $n ? $matches[0][1] : false;
    }

}