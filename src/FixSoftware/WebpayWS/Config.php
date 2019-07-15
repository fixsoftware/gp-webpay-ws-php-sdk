<?php

namespace FixSoftware\WebpayWS;

class Config {

    private $merchantNumber;
    private $provider;
    private $serviceUrl;
    private $wsdlPath;
    private $signerPrivateKeyPath;
    private $signerPrivateKeyPassword;
    private $signerGpPublicKeyPath;
    private $signerLogPath = null;

    public function __construct($config) {

        if(empty($config['merchantNumber']))
            throw new ConfigException('merchantNumber not configured');
        $this->merchantNumber = $config['merchantNumber'];

        if(empty($config['provider']))
            throw new ConfigException('provider not configured');
        $this->provider = $config['provider'];

        if(empty($config['serviceUrl']))
            throw new ConfigException('serviceUrl not configured');
        $this->serviceUrl = $config['serviceUrl'];

        if(empty($config['wsdlPath']))
            throw new ConfigException('wsdlPath not configured');
        $this->wsdlPath = $config['wsdlPath'];

        if(empty($config['signerPrivateKeyPath']))
            throw new ConfigException('signerPrivateKeyPath not configured');
        $this->signerPrivateKeyPath = $config['signerPrivateKeyPath'];

        if(empty($config['signerPrivateKeyPassword']))
            throw new ConfigException('signerPrivateKeyPassword not configured');
        $this->signerPrivateKeyPassword = $config['signerPrivateKeyPassword'];

        if(empty($config['signerGpPublicKeyPath']))
            throw new ConfigException('signerGpPublicKeyPath not configured');
        $this->signerGpPublicKeyPath = $config['signerGpPublicKeyPath'];

        if(!empty($config['signerLogPath'])) {
            $this->signerLogPath = $config['signerLogPath'];
        }

    }

    public function __get($name) {

        return $this->{$name};

    }

}
