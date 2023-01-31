<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ced\Fruugo\Block\Adminhtml\Form\Field;

use Magento\Framework\Api\SearchCriteriaBuilder;


/**
 * HTML select element block with customer groups options
 */
class ShippingMethod extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * @var
     */
    private $_shippingMethod;



    private  $searchCriteriaBuilder;

    private  $_shipMethod;

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Ced\Fruugo\Model\Source\ShippingOverrides\ShipMethod $shipMethod,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_shipMethod = $shipMethod;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }


    /**
     * @return array|null
     */
    protected function _getShippingMethod()
    {
        if ($this->_shippingMethod === null) {
            $shipMethod = $this->_shipMethod->toOptionArray();
            $this->_shippingMethod = $shipMethod;
        }

        return $this->_shippingMethod;
    }

    /**
     * @param $value
     * @return mixed
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->_getShippingMethod() as  $method) {
                $this->addOption($method['value'], addslashes($method['label']));
            }
        }
        return parent::_toHtml();
    }
}
