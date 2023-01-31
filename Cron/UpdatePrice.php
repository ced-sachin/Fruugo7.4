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

class UpdatePrice
{
    /**
     * Logger
     * @var \Psr\Log\LoggerInterface
     */
    public $logger;

    /**
     * OM
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    public $helperData;

    public  $productchange;

    /**
     * UploadProducts constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Ced\Fruugo\Helper\FruugoLogger $logger,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Ced\Fruugo\Helper\Data $helperData,
        \Ced\Fruugo\Model\Productchange $productchange
    ) {

        $this->objectManager = $objectManager;
        $this->logger = $logger;
        $this->helperData = $helperData;
        $this->productchange = $productchange;
    }


    /**
     * Execute
     * @return bool
     */
    public function execute()
    {

        if($this->helperData->checkForConfiguration()) {
            $scopeConfigManager = $this->objectManager
                ->create('Magento\Framework\App\Config\ScopeConfigInterface');
            $autoSync = $scopeConfigManager->getValue('fruugoconfiguration/fruugo_cron_settings/price_cron');
            //$priceSync = $scopeConfigManager->getValue('fruugoconfiguration/product_edit/update_price');

            if ($autoSync) {
                $type = \Ced\Fruugo\Model\Productchange::CRON_TYPE_PRICE;
                $collection = $this->productchange->getCollection();
                $collection->addFieldToFilter('cron_type', $type);
                $ids = [];
                foreach ($collection as $pchange){
                    $ids[]['id']= $pchange->getProductId();
                }


                $price = \Magento\Framework\App\ObjectManager::getInstance()
                    ->get('Ced\Fruugo\Helper\Data')
                    /*->updatePriceOnFruugo($ids);*/
                    ->createProductOnFruugo($ids);
                if($price){
                    $this->productchange->deleteFromProductChange($ids, $type);
                    $this->logger->logger("Fruugo Cron" , "Fruugo Price Cron" , 'Success - '.var_export($price),' Price Cron Success');
                    return true;
                }
                $this->logger->logger("Fruugo Cron" , "Fruugo Price Cron" , 'Failure - '.var_export($price,true),' Price Cron Failure');
                return false;
            } else {
                $this->logger->logger("Fruugo Cron" , "Fruugo Price Cron" , 'Disabled',' Price Cron Failure');
                return false;
            }
        }
        else{
            $this->logger->logger("Fruugo Cron" , "Fruugo Price Cron" , 'Not Processed','Check API details in Fruugo Configuration');
            return false;
        }
    }
}
