<?php

namespace FixSoftware\WebpayWS;

class Response {

    // NICE TO HAVE: Primary return codes
    // NICE TO HAVE: Secondary return codes

    // Master recurring statuses
    const STATUS_MASTER_RECURRING_CREATED = 'CR';
    const STATUS_MASTER_RECURRING_PENDING_SETTLEMENT = 'PS';
    const STATUS_MASTER_RECURRING_VALID = 'OK';
    const STATUS_MASTER_RECURRING_CANCELED_BY_MERCHANT = 'CM';
    const STATUS_MASTER_RECURRING_CANCELED_BY_ISSUER = 'CI';
    const STATUS_MASTER_RECURRING_CANCELED_BY_CARDHOLDER = 'CC';
    const STATUS_MASTER_RECURRING_EXPIRED_CARD = 'EC';
    const STATUS_MASTER_RECURRING_EXPIRED_NO_PAYMENT = 'EP';

    /** @var string */
    private $method;

    /** @var string */
    private $messageId;

    /** @var array */
    private $params = [];

    /** @var string */
    private $soapWrapperName;

    /** @var array */
    private $soapWrapperNameIrregulars = [];

    /** @var array */
    private $soapData;

    /** @var string */
    private $soapMethod;

    /** @var string */
    private $signature;

    /** @var string|false */
    private $error = false;

    public function __construct($method, $messageId, $soapData, $soapWrapperNameIrregulars = []) {

        $this->soapWrapperNameIrregulars = $soapWrapperNameIrregulars;

        $this->method = $method;
        $this->messageId = $messageId;
        $this->soapWrapperName = $this->getSoapWrapperName();

        if(isset($soapData->faultcode)) {

            if(!isset($soapData->detail) || empty($soapData->detail))
                throw new ResponseException('Intenal error: ' . $soapData->faultcode . ': ' . $soapData->faultstring);

            $_detail = (array) $soapData->detail;
            $_soapWrapperName = array_keys($_detail)[0];
            $this->soapData = $_detail;

            $this->error = $_soapWrapperName . ': ' . $soapData->faultstring . ' (' . $soapData->faultcode . ')';

        } else {

            $this->soapData = (array) $soapData;

            if(empty($this->soapData[$this->soapWrapperName]))
                throw new ResponseException('Response name was not found in the response.');

            $_soapWrapperName = $this->soapWrapperName;

            $this->error = false;

        }

        $this->soapData[$_soapWrapperName] = (array) $this->soapData[$_soapWrapperName];

        if($this->soapData[$_soapWrapperName]['messageId'] !== $this->messageId)
            throw new RequestException('messageId in the response is not the same as messageId in the request');

        $this->signature = $this->soapData[$_soapWrapperName]['signature'];

        $_soapDataParams = $this->soapData[$_soapWrapperName];
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

    public function getSoapWrapperName() {

        if(isset($this->soapWrapperNameIrregulars[$this->method]))
            return $this->soapWrapperNameIrregulars[$this->method] . 'Response';

        return lcfirst(substr($this->method, $this->ucpos($this->method))) . 'Response';

    }

    private function ucpos($subject) {
        $n = preg_match( '/[A-Z]/', $subject, $matches, PREG_OFFSET_CAPTURE );
        return $n ? $matches[0][1] : false;
    }

}