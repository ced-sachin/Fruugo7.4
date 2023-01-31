<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ced\Fruugo\Block\Adminhtml\Profile\Widget\Grid\Massaction;

class Extended extends \Magento\Backend\Block\Widget\Grid\Massaction\Extended
{

    protected $_objectManager;
    protected $_template = 'Ced_Fruugo::widget/grid/massaction.phtml';

    public function getSelectedJson()
    {
        return join(",", $this->_getProducts());
    }

    public function _getProducts($isJson=false)
    {
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        if ($this->getRequest()->getPost('in_profile_products') != "") {
            return explode(",", $this->getRequest()->getParam('in_profile_products'));
        }

        $profileId = $this->getRequest()->getParam('id');
        $productIds  = $this->_objectManager->create('\Ced\Fruugo\Model\Profileproducts')->getProfileProducts($profileId);

        if (sizeof($productIds) > 0) {
            $products = $this->_objectManager->create('\Magento\Catalog\Model\Product')
                ->getCollection()
                ->addAttributeToFilter('visibility', array('neq' => 1))
                ->addAttributeToFilter('type_id', array('simple','configurable'))
                ->addFieldToFilter('entity_id', array('in' => $productIds));
            if ($isJson) {
                $jsonProducts = array();
                foreach($products as $product)  {
                    $jsonProducts[$product->getEntityId()] = 0;
                }
                return $this->_jsonEncoder->encode((object)$jsonProducts);
            } else {
                $jsonProducts = array();
                foreach($products as $product)  {
                    $jsonProducts[$product->getEntityId()] = $product->getEntityId();
                }
                return $jsonProducts;
            }
        } else {
            if ($isJson) {
                return '{}';
            } else {
                return array();
            }
        }
    }


    /**
     * @return string
     */
    public function getCustomGridIdsJson()
    {
        if (!$this->getUseSelectAll()) {
            return '';
        }

        /** @var \Magento\Framework\Data\Collection $allIdsCollection **/
        $allIdsCollection = clone $this->getParentBlock()->getCollection();
        $gridIds = $allIdsCollection->clear()->setPageSize(0)->getAllIds();

        if (!empty($gridIds)) {
            return join(",", $gridIds);
        }
        return '';
    }
}
