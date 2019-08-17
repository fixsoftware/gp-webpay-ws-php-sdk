# GP Webpay WebService PHP SDK

PHP SDK for [GP Webpay WebService payments](http://www.gpwebpay.cz).

For HTTPS API, please use [GP Webpay PHP SDK](https://github.com/newPOPE/gp-webpay-php-sdk).

Before implementation, please read the docs from vendor first!

!! This SDK is not full featured yet. It's tested for recurring payments only but it's simply extensible. Feel free to create pull-request if you extend it in any way.

## Installation

```sh
composer require fixsoftware/webpay-ws-php
```

## Setup

```php
$webpayWSConfig = new FixSoftware\WebpayWS\Config([
    'merchantNumber' => $merchantNumber, // Merchant number
    'provider' => $provider, // Your paygate provider number from documentation
    'serviceUrl' => $webpayUrl, // URL of webpay WS service
    'wsdlPath' => $wsdlPath, // WSDL file path (absolute)
    'signerPrivateKeyPath' => $signerPrivateKeyPath, // Path to the private key (absolute)
    'signerPrivateKeyPassword' => $signerPrivateKeyPassword, // Password for private key
    'signerGpPublicKeyPath' => $signerGpPublicKeyPath, // Path to the public key (absolute)
    'signerLogPath' => $signerLogPath // Path to log file for logging requests and responses signed/verified by Signer
]);
```

## How to make a call

```php
$webpayWS = new FixSoftware\WebpayWS\Api($webpayWSConfig);

// process master payment status
try {

    // call WS for master payment status
    $response = $this->webpayWS->call(
        $webserviceMethod, // WS action name
        [
            // getMasterPaymentStatus params (see WS docs)
        ]
    );
    $response_params = $response->getParams(); // gets response parameters

    // in case of WS error (WS error response)
    if($response->hasError()) {
        $error = $response->getError();
        // do something
    }

// in case of exception (SDK exception)
} catch(FixSoftware\WebpayWS\ApiException $e) {
    $exception = $e->getMessage();
    // do something
}

```

## Response status constants

```
FixSoftware\WebpayWS\Response::STATUS_MASTER_RECURRING_CREATED
FixSoftware\WebpayWS\Response::STATUS_MASTER_RECURRING_PENDING_SETTLEMENT
FixSoftware\WebpayWS\Response::STATUS_MASTER_RECURRING_CANCELED_BY_MERCHANT
FixSoftware\WebpayWS\Response::STATUS_MASTER_RECURRING_CANCELED_BY_ISSUER
FixSoftware\WebpayWS\Response::STATUS_MASTER_RECURRING_CANCELED_BY_CARDHOLDER
FixSoftware\WebpayWS\Response::STATUS_MASTER_RECURRING_EXPIRED_CARD
FixSoftware\WebpayWS\Response::STATUS_MASTER_RECURRING_EXPIRED_NO_PAYMENT
FixSoftware\WebpayWS\Response::STATUS_MASTER_RECURRING_VALID
```