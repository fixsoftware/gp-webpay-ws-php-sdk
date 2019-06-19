<?php

namespace FixSoftware\WebpayWS;

class Response {

    /** @var string */
    private $method;

    /** @var string */
    private $messageId;

    /** @var array */
    private $params = [];

    /** @var string */
    private $soapName;

    /** @var array */
    private $soapData;

    /** @var string */
    private $soapMethod;

    /** @var string */
    private $signature;

    /** @var string|false */
    private $error = false;

    public function __construct($method, $messageId, $soapData) {

        $this->method = $method;
        $this->messageId = $messageId;
        $this->soapName = lcfirst(substr($this->method, $this->ucpos($this->method))) . 'Response';

        if(isset($soapData->faultcode)) {

            if(!isset($soapData->detail) || empty($soapData->detail))
                throw new ResponseException('Response fault: ' . $soapData->faultcode . ': ' . $soapData->faultstring);

            $_detail = (array) $soapData->detail;
            $this->error = $_soapName = array_keys($_detail)[0];
            $this->soapData = $_detail;

            $this->error = $_soapName;

        } else {

            $this->soapData = (array) $soapData;

            if(empty($this->soapData[$this->soapName]))
                throw new ResponseException('Response name was not found in the response.');

            $_soapName = $this->soapName;

            $this->error = false;

        }

        $this->soapData[$_soapName] = (array) $this->soapData[$_soapName];

        if($this->soapData[$_soapName]['messageId'] !== $this->messageId)
            throw new RequestException('messageId in the response is not the same as messageId in the request');

        $this->signature = $this->soapData[$_soapName]['signature'];

        $_soapDataParams = $this->soapData[$_soapName];
        unset($_soapDataParams['signature']);
        $this->params = $_soapDataParams;

        $this->soapMethod = $this->method;

    }

    public function getParams() {

        return $this->params;

    }

    public function getSignature() {

        return $this->signature;

    }

    public function hasError() {

        return $this->error === false ? false : true;

    }

    public function getError() {

        return $this->error;

    }

    private function ucpos($subject) {
        $n = preg_match( '/[A-Z]/', $subject, $matches, PREG_OFFSET_CAPTURE );
        return $n ? $matches[0][1] : false;
    }

}