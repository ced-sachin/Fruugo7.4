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

class FetchFeeds
{
    /**
     * Logger
     * @var \Psr\Log\LoggerInterface
     */
    public $logger;

    /**
     * @var \Ced\Fruugo\Helper\Data
     */
    protected $helperData;

    /**
     * FetchFeeds constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Ced\Fruugo\Helper\Data $helperData
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Ced\Fruugo\Helper\Data $helperData
    ) {
        $this->logger = $logger;
        $this->helperData = $helperData;
    }

    public function execute()
    {
        if($this->helperData->checkForConfiguration()) {
            $feeds = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Ced\Fruugo\Controller\Adminhtml\Products\SyncFeeds')
                ->fetchFeeds();
            $this->logger->debug("Fruugo Cron : FetchFeeds Executed : " . $feeds);
            return  $feeds;
        }else{
            $this->logger->debug("Fruugo Cron : FetchFeeds Discarded : Fruugo API not enabled or Invalid. Please check Fruugo Configuration" );
        }



    }
}
