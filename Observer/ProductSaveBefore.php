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

class ProductSaveBefore implements ObserverInterface
{
    /**
     * Request
     * @var  \Magento\Framework\App\RequestInterface
     */
    public $request;

    /**
     * Object Manager
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * Registry
     * @var \Magento\Framework\Registry
     */
    public $registry;

    /**
     * ProductSaveBefore constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->request = $request;
        $this->registry  = $registry;
        $this->objectManager = $objectManager;
    }

    /**
     * Product SKU Change event handler
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\Framework\Event\Observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        //$req = $observer->getEvent()->getProduct();
        //$id = $req->getId();
        $product = $observer->getProduct();
        $product_available = false;
        $profileProduct = $this->objectManager->create('Ced\Fruugo\Model\Profileproducts')->loadByField('product_id', $product->getId());;
        if($profileProduct && $profileProduct->getId()) {
            $product_available = true;
        }
        if ($product_available)
        {
            $product->setData('fruugo_product_validation', 'Not-Validated');

            if($product->getTypeId() == "simple"){
                $parentIds = $this->objectManager->create('Magento\ConfigurableProduct\Model\Product\Type\Configurable')->getParentIdsByChild($product->getId());
                foreach ($parentIds as $id){
                    $product =  $this->objectManager->create('Magento\Catalog\Model\Product')->load($id);
                    $product->setData('fruugo_product_validation','Not-Validated');
                    $product->getResource()->saveAttribute($product,'fruugo_product_validation');
                }
           }
        }

        $id = $this->request->getParam('id');
        $getSku = $this->objectManager->get('Magento\Catalog\Model\Product')->load($id)->getSku();
        if ($this->registry->registry('prev_sku')) {
            $this->registry->unregister('prev_sku');
        }
        if (!empty($id)) {
            $this->registry->register('prev_sku', $getSku);
        }
        return $observer;
    }
}
