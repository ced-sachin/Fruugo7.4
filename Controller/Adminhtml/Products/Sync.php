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
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Fruugo\Controller\Adminhtml\Products;

use Magento\Backend\App\Action\Context;

class Sync extends \Magento\Backend\App\Action
{
    /**
     * Result Page factory
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * Feeds Model
     * @var \Ced\Fruugo\Model\FeedsFactory
     */
    public $feedsFactory;

    /**
     * Logger
     * @var $logger \Psr\Log\LoggerInterface
     */
    public $logger;

    /**
     * Data Helper
     * @var $helper
     */
    public $helper;

    /**
     * Sync constructor.
     * @param Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Ced\Fruugo\Model\FeedsFactory $feedsFactory
     * @param \Ced\Fruugo\Helper\Data $helper
     */
    public function __construct(
        Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Ced\Fruugo\Model\FeedsFactory $feedsFactory,
        \Ced\Fruugo\Helper\Data $helper,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->feedsFactory = $feedsFactory;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->redirectFactory = $redirectFactory;
    }

    /**
     * Execute
     * @return void
     */
    public function execute()
    {
        if(!$this->helper->checkForConfiguration()) {
            return false;
        }
        $productSyncStatus = $this->syncAllProducts();
        if ($productSyncStatus) {

            $this->messageManager->addSuccessMessage('All Products Synced Successfully');

        } else {

            $this->messageManager->addErrorMessage('Fruugo Products Sync Failed');

        }

        $redirect = $this->redirectFactory->create();
        return $redirect->setPath(\Ced\Fruugo\Controller\Adminhtml\Products\Index::REDIRECT_PATH);

    }

    /**
     * Feeds Sync
     * @return bool
     */
    public function syncAllProducts()
    {
        $scopeConfigManager = $this->_objectManager
            ->create('Magento\Framework\App\Config\ScopeConfigInterface');
        //$priceSync = $scopeConfigManager->getValue('fruugoconfiguration/product_edit/update_price');
        //$inventorySync = $scopeConfigManager->getValue('fruugoconfiguration/product_edit/update_inventory');
        //$allOtherSync = $scopeConfigManager->getValue('fruugoconfiguration/product_edit/other_detail');
        try {
            $categoryIds = $this->helper->getMappedCategories();
            $categoryIds = $categoryIds->getData();
            $categoryFactory = $this->_objectManager->create('\Magento\Catalog\Model\CategoryFactory');
            $ids = [];
            foreach ($categoryIds as $categoryId) {
                $category = $categoryFactory->create()->load($categoryId['entity_id']);
                $productCollection = $category->getProductCollection()->addAttributeToSelect(
                    ['entity_id'])->load();
                foreach ($productCollection as $product) {
                    $ids[] = $product->getId();
                }
            }
            //if ($allOtherSync) {
                $this->helper->createProductOnFruugo($ids);
            /*} else {
                $this->helper->validateAllProducts($ids);
            }*/
            foreach ($ids as $key => $value) {
                $array[$key]['id'] = [$value];
            }
            //if ($inventorySync) {
                $this->helper->updateInventoryOnFruugo($array);
            //}
            //if ($priceSync) {
                $this->helper->updatePriceOnFruugo($array);
            //}
            return true;
        }
        catch (\Exception $e) {
            $this->logger->debug("Fruugo Products Sync Failed : syncAllProducts : " . $e->getMessage());
            return false;
        }
    }

    /**
     * IsALLowed
     * @return boolean
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ced_Fruugo::Fruugo');
    }

}