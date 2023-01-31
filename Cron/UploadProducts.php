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

class UploadProducts
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

    /**
     * Data Helper
     * @var $helper
     */
    public $helper;

    /**
     * UploadProducts constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Ced\Fruugo\Helper\Data $helper
    ) {

        $this->objectManager = $objectManager;
        $this->logger = $logger;
        $this->helper = $helper;
    }

    /**
     * Execute
     * @return boolean
     */
    public function execute()
    {
        if($this->helper->checkForConfiguration()) {
            $scopeConfigManager = $this->objectManager
                ->create('Magento\Framework\App\Config\ScopeConfigInterface');
            //$autoSync = $scopeConfigManager->getValue('fruugoconfiguration/product_edit/auto_sync');

            // if ($autoSync) {
            $categoryIds = $this->helper->getMappedCategories();
            $categoryIds = $categoryIds->getData();
            $categoryFactory = $this->objectManager->create('\Magento\Catalog\Model\CategoryFactory');
            $ids = [];
            foreach ($categoryIds as $categoryId) {
                $category = $categoryFactory->create()->load($categoryId['entity_id']);
                $productCollection = $category->getProductCollection()->addAttributeToSelect(
                    ['entity_id'])->load();
                foreach ($productCollection as $product) {
                    $ids[] = $product->getId();
                }
            }
            $products = $this->helper->createProductOnFruugo($ids);

            $this->logger->debug("Fruugo Cron : UploadProducts Executed : " . $products);
            return $products;
            /*} else {
                $this->logger->debug("Fruugo Cron : UploadProducts: AutoSync or Other Details Sync Disabled ");
            }*/
            return false;
        }else{
            $this->logger->debug("Fruugo Cron : Update Price Sync Discarded : Fruugo API not enabled or Invalid. Please check Fruugo Configuration" );
            }

    }
}
