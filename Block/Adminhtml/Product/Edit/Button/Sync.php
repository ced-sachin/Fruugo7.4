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

namespace Ced\Fruugo\Block\Adminhtml\Product\Edit\Button;

class Sync extends \Magento\Backend\Block\Widget\Container
{

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function _prepareLayout()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id == '') {
            return false;
        }

            $addButtonProps = [
            'id' => 'fruugo_product',
            'label' => __('Fruugo Sync'),
            'class' => '',
            'button_class' => 'fruugo',
            'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
            'options' => $this->_getCustomActionListOptions(),
        ];
        $this->buttonList->add('add_new', $addButtonProps);

        return parent::_prepareLayout();
    }

    /**
     * Retrieve options for 'CustomActionList' split button
     *
     * @return array
     */
    public function _getCustomActionListOptions()
    {

        /*list of button which you want to add*/
        $splitButtonOptions = [
            'fruugo_sync'=> [
                'label' => __('Sync With Fruugo'),
                'onclick' => sprintf("location.href = '%s';", $this->getUrl('fruugo/products/uploadproduct',
                    ['id' =>  $this->getRequest()->getParam('id')])
                   ),
                'default' => 'true'
            ],
            /*@ToDo remove the map action
             * 'fruugo_attr_map'=> [
                'label'=>__('Fruugo Map Attribute'),
                'onclick' => sprintf("location.href = '%s';", $this->getUrl('fruugo/attributes/map')),
            ],*/
            'fruugo_validate'=> [
                'label'=>__('Fruugo Product Validate'),
                'onclick'=> sprintf("location.href = '%s';", $this->getUrl('fruugo/products/validateproduct',
                    ['id' =>  $this->getRequest()->getParam('id')])),
            ],

        ];


        /* in above list you can also pass others attribute of buttons*/
        return $splitButtonOptions;
    }
}