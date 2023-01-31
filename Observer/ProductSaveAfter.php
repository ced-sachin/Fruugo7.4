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

class ProductSaveAfter implements ObserverInterface
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
            return false;
        }

        $productids[] = $product->getId();

        $this->session->setNoBatches(true);
        $this->dataHelper->createProductOnFruugo($productids, array());

        $prevSku = $this->registry->registry('prev_sku');
        $newSku = null;
        $cache = $this->objectManager->create('\Magento\Framework\App\Cache');
        $cacheArray = ($cache->load('ced_validate') && !is_null($cache->load('ced_validate'))) ? $this->json->jsonDecode($cache->load('ced_validate')) : array();
        if(isset($cacheArray[$product->getId()])) {
            unset($cacheArray[$product->getId()]);
            $cache->save($this->json->jsonEncode($cacheArray),'ced_validate');
        }
        if (empty($prevSku)) {
            $prevSku = $product->getSku();
        } else {
            $newSku = $product->getSku();
        }

        if ((!empty($newSku) && $prevSku != $newSku)) {
            $prevSkuExist = $this->dataHelper->getItem($prevSku);
            if ($prevSkuExist) {
                $additionalAttributes = [
                    '_attribute' => [],
                    '_value' => []
                ];
                $additionalAttributes['_value'][0] =
                    [
                        'additionalProductAttribute' => [
                            'productAttributeName' => 'sku_override',
                            'productAttributeValue' => 'true',
                        ]
                    ];
                if ($product->getTypeId() == 'simple' && $product->getVisibility() == 1) {
                    $parentIds = $this->objectManager
                        ->get('Magento\ConfigurableProduct\Model\Product\Type\Configurable')
                        ->getParentIdsByChild($product->getId());
                    if (!empty($parentIds)) {
                        foreach ($parentIds as $parentId) {
                            $isMapped = $this->dataHelper->getFruugoCategory($parentId);
                            if ($isMapped) {
                                $this->dataHelper->createProductOnFruugo([$parentId], $additionalAttributes);
                            }
                        }
                    }
                } elseif ($product->getTypeId() == 'simple') {
                    $this->dataHelper->createProductOnFruugo([$product->getId()], $additionalAttributes);
                }

            }
        }
        if($product->getTypeId() == 'simple') {
            $inventory_setting = false;

            $helper = $this->objectManager->create('Ced\Fruugo\Helper\Data');
            $pcode = "";
            if ($profile = $helper->getCurrentProfile($product->getId())) {
                $inventory_setting = $profile['inventory_setting'];
                $inventory_threshold_value = $profile['inventory_threshold_value'];
                $fixed_threshold_value = $profile['fixed_threshold_value'];
            }

            
            //capture stock change
            $orgQty = $product->getOrigData('quantity_and_stock_status');
            $oldValue = (int)$orgQty['qty'];

            $postData = $this->request->getParam('product');
            $newValue = (int)$postData['quantity_and_stock_status']['qty'];

            $isInStock = (boolean)$postData['quantity_and_stock_status']['is_in_stock'];
            //if out of stock then set value to 0
            if (!$isInStock)
                $newValue = 0;
            if ($inventory_setting && $newValue < $inventory_threshold_value ) {
                $newValue=$fixed_threshold_value;
                $productId = $product->getId();
                $model = $this->objectManager->create('Ced\Fruugo\Model\Productchange');
                $type = \Ced\Fruugo\Model\Productchange::CRON_TYPE_INVENTORY;
                $model->setProductChange($productId, $oldValue, $newValue, $type);
                $this->checkForPriceChange($observer);

            }else{
                if ($oldValue == $newValue)
                    return false;
                $productId = $product->getId();
                $model = $this->objectManager->create('Ced\Fruugo\Model\Productchange');
                $type = \Ced\Fruugo\Model\Productchange::CRON_TYPE_INVENTORY;
                $model->setProductChange($productId, $oldValue, $newValue, $type);
                $this->checkForPriceChange($observer);
            }

        }

        $this->registry->unregister('prev_sku');
        return true;
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
