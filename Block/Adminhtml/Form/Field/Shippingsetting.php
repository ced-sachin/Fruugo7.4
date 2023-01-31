<?php

namespace Ced\Fruugo\Block\Adminhtml\Form\Field;

class Shippingsetting extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @var
     */
    protected $_shippingRegion;

    /**
     * @var
     */
    protected $_shippingMethod;

    protected $_magentoAttr;

    protected  $_enabledRenderer;

    /**
     * Retrieve group column renderer
     *
     * @return shipping
     */
    protected function _getEnabledRenderer()
    {
        if (!$this->_enabledRenderer) {
            $this->_enabledRenderer = $this->getLayout()->createBlock(
            //'Magento\CatalogInventory\Block\Adminhtml\Form\Field\Customergroup',
                'Ced\Fruugo\Block\Adminhtml\Form\Field\Enabled',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_enabledRenderer->setClass('shipping_region_select');
        }
        return $this->_enabledRenderer;
    }


    /**
     * Retrieve group column renderer
     *
     * @return shipping
     */
    protected function _getShippingRegionRenderer()
    {
        if (!$this->_shippingRegion) {
            $this->_shippingRegion = $this->getLayout()->createBlock(
                //'Magento\CatalogInventory\Block\Adminhtml\Form\Field\Customergroup',
                'Ced\Fruugo\Block\Adminhtml\Form\Field\ShippingRegion',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_shippingRegion->setClass('shipping_region_select');
        }
        return $this->_shippingRegion;
    }

    /**
     * Retrieve group column renderer
     *
     * @return shipping
     */
    protected function _getShippingMethodRenderer()
    {
        if (!$this->_shippingMethod) {
            $this->_shippingMethod = $this->getLayout()->createBlock(
            //'Magento\CatalogInventory\Block\Adminhtml\Form\Field\Customergroup',
                'Ced\Fruugo\Block\Adminhtml\Form\Field\ShippingMethod',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_shippingMethod->setClass('shipping_method_select');
        }
        return $this->_shippingMethod;
    }

    /**
     * Retrieve group column renderer
     *
     * @return shipping
     */
    protected function _getMagentoAttributeCodeRenderer()
    {
        if (!$this->_magentoAttr) {
            $this->_magentoAttr = $this->getLayout()->createBlock(
            //'Magento\CatalogInventory\Block\Adminhtml\Form\Field\Customergroup',
                'Ced\Fruugo\Block\Adminhtml\Form\Field\MagentoAttributes',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_magentoAttr->setClass('shipping_method_select');
        }
        return $this->_magentoAttr;
    }

    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {

        $this->addColumn(
            'enabled',
            ['label' => __('Enabled'), 'renderer' => $this->_getEnabledRenderer()]
        );
        $this->addColumn(
            'region',
            ['label' => __('Region'), 'renderer' => $this->_getShippingRegionRenderer()]
        );
        $this->addColumn(
            'method',
            ['label' => __('Method'), 'renderer' => $this->_getShippingMethodRenderer()]
        );
        //$this->addColumn('charges', ['label' => __('Charges')]);
        $this->addColumn(
            'magento_attribute_code',
            ['label' => __('Magento Attribute Code'), 'renderer' => $this->_getMagentoAttributeCodeRenderer()]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Rule');
    }

    /**
     * Prepare existing row data object
     *
     * @param \Magento\Framework\DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionExtraAttr = [];

        $optionExtraAttr['option_' . $this->_getEnabledRenderer()->calcOptionHash($row->getData('enabled'))] =
            'selected="selected"';

        $optionExtraAttr['option_' . $this->_getShippingRegionRenderer()->calcOptionHash($row->getData('region'))] =
            'selected="selected"';
        $optionExtraAttr['option_' . $this->_getShippingMethodRenderer()->calcOptionHash($row->getData('method'))] =
            'selected="selected"';
        $optionExtraAttr['option_' . $this->_getMagentoAttributeCodeRenderer()->calcOptionHash($row->getData('magento_attribute_code'))] =
            'selected="selected"';
        $row->setData(
            'option_extra_attrs',
            $optionExtraAttr
        );


    }
}
