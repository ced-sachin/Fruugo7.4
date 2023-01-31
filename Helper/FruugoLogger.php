<?php

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
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Fruugo\Helper;

class FruugoLogger extends \Monolog\Logger
{
    /*
     * Debug Flag
     */
    public $debugMode;

    /**
     * Object Manager
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * Config Manager
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfigManager;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
        $this->scopeConfigManager = $this->objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->debugMode = $this->scopeConfigManager->getValue('fruugoconfiguration/fruugosetting/debug');
    }
    public function logger(
        $type = "Test",
        $subType = "Test",
        $response = array(),
        $comment = ""
    ) {
        if($this->debugMode) {
            $this->objectManager->create('\Ced\Fruugo\Model\FruugoLogs')
                ->setLogType($type)
                ->setLogSubType($subType)
                ->setLogDate( date("d-m-y H:i:s"))
                ->setLogValue($response)
                ->setLogComment($comment)
                ->save();
            return true;
        }
        return false;
    }
}