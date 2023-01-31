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
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Fruugo\Cron;

class FetchFruugoOrders
{
    /**
     * Logger
     * @var \Psr\Log\LoggerInterface
     */
    public $logger;

    /**
     * Config Manager
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfigManager;

    /**
     * Registry Manager
     * @var \Magento\Framework\Registry
     */
    public $registry;

    public  $helperData;

    /**
     * Object Manager
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * Cron Constructor
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Ced\Fruugo\Helper\FruugoLogger $logger,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        \Ced\Fruugo\Helper\Data $helperData
    ) {
        $this->logger = $logger;
        $this->scopeConfigManager = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->registry = $registry;
        $this->objectManager = $objectManager;
        $this->helperData = $helperData;
    }

    public function execute()
    {
        if($this->helperData->checkForConfiguration()) {
            if ($this->scopeConfigManager->getValue('fruugoconfiguration/fruugo_cron_settings/order_cron') == '1') {
                $this->objectManager
                    ->get('\Ced\Fruugo\Helper\Order')
                    ->fetchLatestFruugoOrders();
                //$this->objectManager->get('\Ced\Fruugo\Helper\ShipmentHelper')->execute();
                $this->logger->logger("Fruugo Cron" , "Order Cron" , 'Success',' All Good Here');
                return true;
            } else {
                $this->logger->logger("Fruugo Cron" , "Order Cron" , 'Disabled from Config','Please Enable Fruugo Order Cron from Fruugo Configuration');
                return false;
            }
        }else {
            $this->logger->logger("Fruugo Cron" , "Order Cron" , 'API Details are not valid','Check API details in Fruugo Configuration');
            return false;
        }

    }
}
