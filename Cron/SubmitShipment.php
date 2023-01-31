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

class SubmitShipment
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


    public  $helperData;

    /**
     * Cron Constructor
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Ced\Fruugo\Helper\FruugoLogger $logger,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Ced\Fruugo\Helper\Data $helperData
    ) {
        $this->logger = $logger;
        $this->scopeConfigManager = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->helperData = $helperData;
    }

    public function execute()
    {
        if($this->helperData->checkForConfiguration()) {
            if ($this->scopeConfigManager->getValue('fruugoconfiguration/fruugo_cron_settings/automated_shipment') == '1') {
                $order = \Magento\Framework\App\ObjectManager::getInstance()
                    ->get('\Ced\Fruugo\Helper\Order')
                    ->fetchLatestFruugoOrders();
                $this->logger->debug("Fruugo Cron : FetchFruugoOrders Executed : " . $order);
                return $order;
            } else {
                return 'Order Cron Disabled from Fruugo Config';
            }
        }else{
            $this->logger->debug("Fruugo Cron : FetchFruugoOrders Discarded : Fruugo API not enabled or Invalid. Please check Fruugo Configuration" );
        }

    }
}
