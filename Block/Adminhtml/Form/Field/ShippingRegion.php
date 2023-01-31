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
class ShippingRegion extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * Customer groups cache
     *
     * @var array
     */
    private $_shippingRegion;

     private  $searchCriteriaBuilder;

     private  $_shipRegion;

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
        \Ced\Fruugo\Model\Source\ShippingOverrides\ShipRegion $shipRegion,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_shipRegion = $shipRegion;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }


    /**
     * Retrieve allowed customer groups
     *
     * @param int $groupId return name by customer group id
     * @return array|string
     */
    protected function _getShippingRegions()
    {
        if ($this->_shippingRegion === null) {
            $shipRegion = $this->_shipRegion->toOptionArray();
            $this->_shippingRegion = $shipRegion;
        }
        return $this->_shippingRegion;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->_getShippingRegions() as  $region) {
                    $this->addOption($region['value'], addslashes($region['label']));
            }
        }
        return parent::_toHtml();
    }
}
