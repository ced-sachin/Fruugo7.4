<?php

/**
 * The MIT License (MIT)

 *Copyright (c) 2015 Phillip Shipley

 *Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without
 * limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following
 * conditions:
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of
 * the Software.
 */

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Fruugo
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Fruugo\Helper;
use phpseclib\Crypt\RSA;


class Signature extends \Magento\Framework\App\Helper\AbstractHelper
{

    const CUSTOMER_ID_PATH = 'fruugoconfiguration/fruugosetting/customer_id';
    const PRIVATE_KEY_PATH = 'fruugoconfiguration/fruugosetting/private_key';
    const BASE_REQUEST_URL_PATH = 'fruugoconfiguration/fruugosetting/api_url';

    /**
     * Consumer ID provided by Developer Portal
     * @var string $consumerId
     */
    public $consumerId;

    /**
     * Base64 Encoded Private Key provided by Developer Portal
     * @var string $privateKey
     */
    public $privateKey;

    /**
     * Request URL  of API request being made
     * @var string $requestUrl
     */
    public $requestUrl;

    /**
     * HTTP Request Method for API call (GET/POST/PUT/DELETE/OPTIONS/PATCH)
     * @var string $requestMethod
     */
    public $requestMethod;

    /**
     * Timestamp of certificate generation
     * @var integer $timestamp
     */
    public $timestamp = 0;

    /**
     * Signature constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
    }



    /**
     * Get signature with optional timestamp. If using Signature class as object, you can repeatedly call this
     * method to get a new signature without having to provide $consumerId, $privateKey, $requestUrl, $requestMethod
     * every time.
     * @param string $requestUrl
     * @param string $requestMethod
     * @param null $timestamp
     * @param null $consumerId
     * @param null $privateKey
     * @return \Exception|string
     */
    public function getSignature(
        $requestUrl,
        $requestMethod='GET',
        $timestamp=null,
        $consumerId=null,
        $privateKey=null
    ) {

        //$this->requestUrl = 'https://marketplace.fruugoapis.com/' . $requestUrl;
        $this->requestUrl = $this->scopeConfig->getValue(self::BASE_REQUEST_URL_PATH) . $requestUrl;
        if (preg_match('/^https/', $requestUrl)) {
            $this->requestUrl = $requestUrl;
        }
        $this->requestMethod =  $requestMethod;
        $this->consumerId = is_null($consumerId) ? $this->scopeConfig->getValue(self::CUSTOMER_ID_PATH) : $consumerId;
        $this->privateKey = is_null($privateKey) ? $this->scopeConfig->getValue(self::PRIVATE_KEY_PATH) : $privateKey;
        $this->timestamp = $timestamp;

        if (is_null($this->timestamp) || !is_numeric($this->timestamp)) {
            $this->timestamp = $this->getMilliseconds();
        }
        return $this->calculateSignature(
            $this->requestUrl,
            $this->requestMethod,
            $this->consumerId,
            $this->privateKey,
            $this->timestamp
        );
    }

    /**
     * Static method for quick calls to calculate a signature.
     * @param string $requestUrl
     * @param string $requestMethod
     * @param string $consumerId
     * @param string $privateKey
     * @param null $timestamp
     * @return \Exception|string
     * @link https://developer.fruugoapis.com/#authentication

     */
    public function calculateSignature($requestUrl, $requestMethod, $consumerId, $privateKey, $timestamp=null)
    {
        $this->timestamp = $timestamp;
        if (is_null($this->timestamp) || !is_numeric($this->timestamp)) {
            $this->timestamp = $this->getMilliseconds();
        }

        /**
         * Append values into string for signing
         */
        $message = $consumerId."\n".$requestUrl."\n".strtoupper($requestMethod)."\n".$this->timestamp."\n";
        /**
         * Get RSA object for signing
         */
        $rsa = new RSA();
        $decodedPrivateKey = base64_decode($privateKey);
        $rsa->setPrivateKeyFormat(RSA::PRIVATE_FORMAT_PKCS8);
        $rsa->setPublicKeyFormat(RSA::PRIVATE_FORMAT_PKCS8);
        /**
         * Load private key
         */
        try {
            if ($rsa->loadKey($decodedPrivateKey, RSA::PRIVATE_FORMAT_PKCS8)) {
                /**
                 * Make sure we use SHA256 for signing
                 */
                $rsa->setHash('sha256');
                $rsa->setSignatureMode(RSA::SIGNATURE_PKCS1);
                $signed = $rsa->sign($message);
                /**
                 * Return Base64 Encode generated signature
                 */
                return base64_encode($signed);
            } else {
                throw new \Exception("Unable to load private key");
            }
        }
        catch (\Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * Get current timestamp in milliseconds
     * @return float
     */
    public function getMilliseconds()
    {
        return date_timestamp_get(date_create())*1000;
        //return round(microtime(true) * 1000);
    }


}
