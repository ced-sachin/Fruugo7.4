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

namespace Ced\Fruugo\Model\Source;

class FruugoSwatchAttributes implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $collection =  $objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection');
        $collection
            //->addFieldToFilter('is_configurable', 1)
            ->addFieldToFilter('is_global', 1)
            ->addFieldToFilter('frontend_input', ['in' => ['select', 'boolean']]);
        $option = [];
        foreach ($collection as $attribute){
            $option[] = ['label' => $attribute->getAttributeCode(),
                'value' => $attribute->getAttributeCode()];
        }

        return $option;

    }

}
