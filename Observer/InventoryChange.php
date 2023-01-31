<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Fruugo
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Fruugo\Observer;

use Magento\Framework\Event\ObserverInterface;

class InventoryChange implements ObserverInterface
{
    /**
     * Object Manager
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * Message Manager
     * @var \Magento\Framework\Message\ManagerInterface
     */
    public $messageManager;

    /**
     * Request
     * @var \Magento\Framework\App\RequestInterface
     */
    public $request;

    /**
     * Registry
     * @var \Magento\Framework\Registry
     */
    public $registry;

    /**
     * Data Helper
     * @var \Ced\Fruugo\Helper\Data
     */
    public $dataHelper;

    /**
     * Ced Logger
     * @var \Ced\Fruugo\Helper\CedLogger
     */
    public $cedLogger;

    protected  $fruugoHelper;

    /**
     * Json Parser
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $json;

    /**
     * ProductSaveAfter constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Ced\Fruugo\Helper\Data $data
     * @param \Ced\Fruugo\Helper\CedLogger $cedLogger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Ced\Fruugo\Helper\Data $data,
        \Ced\Fruugo\Helper\FruugoLogger $cedLogger,
        \Magento\Framework\Json\Helper\Data $json,
        \Ced\Fruugo\Helper\Fruugo $fruugoHelper

    ) {
        $this->objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->registry  = $registry;
        $this->dataHelper = $data;
        $this->cedLogger =$cedLogger;
        $this->json = $json;
        $this->fruugoHelper = $fruugoHelper;
        $this->session = $context->getSession();
    }

    /**
     * Catalog product save after event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return boolean
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();

        if(empty($product)){
            return $observer;
        }
        $product_load = $this->objectManager->create('Magento\Catalog\Model\Product')->load($product->getId());
        $productids[] = $product->getId();

        $this->session->setNoBatches(true);
        $this->dataHelper->createProductOnFruugo($product_load, array());
        
        $oldValue = 0;

        $postData = $product_load->getQuantityAndStockStatus();
        $newValue = $postData['qty'];

        $isInStock = $postData['is_in_stock'];
        //if out of stock then set value to 0
        if (!$isInStock)
            $newValue = 0;

        if ($oldValue == $newValue)
            return $observer;

        $productId = $product->getId();

        $model = $this->objectManager->create('Ced\Fruugo\Model\Productchange');
        $type = \Ced\Fruugo\Model\Productchange::CRON_TYPE_INVENTORY;
        $model->setProductChange($productId, $oldValue, $newValue, $type);
        return $observer;
    }


    public function checkForPriceChange($observer)
    {
        $product = $observer->getProduct();

        $type = \Ced\Fruugo\Model\Productchange::CRON_TYPE_PRICE;


        $helper = $this->objectManager->create('Ced\Fruugo\Helper\Data');
        $pcode = "";
        if($profile = $helper->getCurrentProfile($product->getId())){
            $pcode = $profile['profile_code'];
        }
        $configPrice = trim($helper->getConfigData($pcode, 'fruugo_configuration/productinfo_map/fruugo_product_price'));

        $priceAttr = 'special_price';
        if($configPrice == 'differ'){
            $priceAttr = trim($helper->getConfigData($pcode,'fruugo_configuration/productinfo_map/fruugo_different_price'));
            $origSpecialPrice = $product->getOrigData('special_price');
            $specialPrice = $product->getData('special_price');
        }
        if($priceAttr != 'special_price'){
            $origPrice = $product->getOrigData($priceAttr);
            $price = $product->getData($priceAttr);
        }else{
            $origPrice = $product->getOrigData($priceAttr);
            $price = $product->getData($priceAttr);
            if($price == ''){
                $priceAttr = 'price';
                $origPrice = $product->getOrigData($priceAttr);
                $price = $product->getData($priceAttr);
            }
        }
        if($origPrice != $price){
            $prices = $this->fruugoHelper->getFruugoPrice($product);
            $model = $this->objectManager->create('Ced\Fruugo\Model\Productchange');
            $model->setProductChange($product->getId(), $origPrice, $prices['splprice'], $type);
        }
    }

}
