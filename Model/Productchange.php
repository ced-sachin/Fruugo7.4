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


namespace Ced\Fruugo\Model;

class Productchange extends \Magento\Framework\Model\AbstractModel
{

    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';

    const CRON_TYPE_INVENTORY = 'inventory';
    const CRON_TYPE_PRICE = 'price';
    /**
     * @var string
     */
    protected $_eventPrefix = 'jet_productchange';

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init('Ced\Fruugo\Model\ResourceModel\Productchange');
    }

    public function deleteFromProductChange($productIds, $type)
    {
        $this->_getResource()->deleteFromProductChange($productIds, $type);
        return $this;
    }

    public function setProductChange($productId, $oldValue='', $newValue='', $type){
        if ($productId <= 0) {
            return $this;
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $profileProduct = $objectManager->create('Ced\Fruugo\Model\Profileproducts')->loadByField('product_id', $productId);
        if($profileProduct && $profileProduct->getId()) {
            //$model = Mage::getModel('jet/productchange');
            $collection = $this->getCollection()->addFieldToFilter('product_id', $productId)
                ->addFieldToFilter('cron_type', $type);

            if (count($collection) > 0) {
                $this->load($collection->getFirstItem()->getId());
                if($oldValue == '') {
                    $oldValue = $collection->getFirstItem()->getOldValue();
                }
            } else {
                $this->setProductId($productId);
            }

            $this->setOldValue($oldValue);
            $this->setNewValue($newValue);
            $this->setAction(self::ACTION_UPDATE);
            $this->setCronType($type);
            $this->save();
        }
        return $this;
    }
}