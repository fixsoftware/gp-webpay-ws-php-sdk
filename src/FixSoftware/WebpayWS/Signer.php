<?php

/**
 * Original author: Adam Stipak
 * Original package: https://packagist.org/packages/adamstipak/webpay-php
 */

namespace FixSoftware\WebpayWS;

class Signer {

  /** @var string */
  private $privateKey;

  /** @var resource */
  private $privateKeyResource;

  /** @var string */
  private $privateKeyPassword;

  /** @var string */
  private $publicKey;

  /** @var resource */
  private $publicKeyResource;

  /** @var string */
  private $logPath = null;

  const SIGNER_BASE64_DISABLE = false;
  const SIGNER_BASE64_ENABLE = true;

  public function __construct (string $privateKey, string $privateKeyPassword, string $publicKey) {
    if (!file_exists($privateKey) || !is_readable($privateKey)) {
      throw new SignerException("Private key ({$privateKey}) not exists or not readable!");
    }

    if (!file_exists($publicKey) || !is_readable($publicKey)) {
      throw new SignerException("Public key ({$publicKey}) not exists or not readable!");
    }

    $this->privateKey = $privateKey;
    $this->privateKeyPassword = $privateKeyPassword;
    $this->publicKey = $publicKey;
  }

  /**
   * @return resource
   * @throws SignerException
   */
  private function getPrivateKeyResource () {
    if ($this->privateKeyResource) {
      return $this->privateKeyResource;
    }

    $key = file_get_contents($this->privateKey);

    if (!($this->privateKeyResource = openssl_pkey_get_private($key, $this->privateKeyPassword))) {
      throw new SignerException("'{$this->privateKey}' is not valid PEM private key (or passphrase is incorrect).");
    }

    return $this->privateKeyResource;
  }

  /**
   * @param array $params
   * @return string
   */
  public function sign (array $params, $base64 = self::SIGNER_BASE64_ENABLE): string {
    $digestText = implode('|', $params);
    openssl_sign($digestText, $digest, $this->getPrivateKeyResource());
    if($base64)
      $digest = base64_encode($digest);

    $this->log([
      'digestText' => $digestText,
      'digest_base64' => $base64 ? $digest : base64_encode($digest),
      'base64' => $base64
    ]);

    return $digest;
  }

  /**
   * @param array $params
   * @param string $digest
   * @return bool
   * @throws SignerException
   */
  public function verify (array $params, $digest, $base64 = self::SIGNER_BASE64_ENABLE) {
    $data = implode('|', $params);
    $digest_original = $digest;
    if($base64)
      $digest = base64_decode($digest);

    $ok = openssl_verify($data, $digest, $this->getPublicKeyResource());

    $this->log([
      'data' => $data,
      'digest_base64' => $base64 ? $digest_original : base64_encode($digest_original),
      'result' => $ok,
      'base64' => $base64
    ]);

    if ($ok !== 1) {
      throw new SignerException("Digest is not correct!");
    }

    return true;
  }

  /**
   * @return resource
   * @throws SignerException
   */
  private function getPublicKeyResource () {
    if ($this->publicKeyResource) {
      return $this->publicKeyResource;
    }

    $fp = fopen($this->publicKey, "r");
    $key = fread($fp, filesize($this->publicKey));
    fclose($fp);

    if (!($this->publicKeyResource = openssl_pkey_get_public($key))) {
      throw new SignerException("'{$this->publicKey}' is not valid PEM public key.");
    }

    return $this->publicKeyResource;
  }

  /**
   * @param string $logPath
   */
  public function setLogPath($logPath) {
    $this->logPath = $logPath;
  }

  /**
   * @param  [type] $data
   * @throws SignerException
   */
  private function log($data) {

    if($this->logPath === null)
      return;

    $caller = !empty(debug_backtrace()[1]['function']) ? debug_backtrace()[1]['function'] : null;
    $message = Date('Y-m-d H:i:s') . ' | ' . (!empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '-') . ' | ' . $caller . ' | ' . json_encode($data) . "\n";

    $content = null;
    if(file_exists($this->logPath))
        $content = file_get_contents($this->logPath);
    if(!file_put_contents($this->logPath, $content . $message))
      throw new SignerException('Unable to log the data.');

  }

}
