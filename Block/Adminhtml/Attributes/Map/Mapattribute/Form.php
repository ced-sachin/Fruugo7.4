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

namespace Ced\Fruugo\Block\Adminhtml\Attributes\Map\Mapattribute;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    public $objectManager;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\ObjectManagerInterface  $objetManager,
        $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->objectManager = $objetManager;
    }

    public function _prepareForm()
    {
        $data = [];
        $form = $this->_formFactory->create( ['data' =>
                [
                    'id'            => 'edit_form',
                    'action'        => $this->getUrl('fruugo/attributes/mapsave'),
                    'method'        => 'post',
                    'use_container' => true,
                ]
            ]
        );
        $this->setForm($form);
        $fieldset = $form->addFieldset('jeterror_form', []);

        $fieldset->addField('action', 'text', [
                'label'     => __('Mapp The Fruugo Attributes'),
                'class'     => 'action',
                'name'      => 'fruugo_code'
            ]
        );

        $locations = $form->getElement('action');

        $locations->setRenderer(
            $this->getLayout()->createBlock('Ced\Fruugo\Block\Adminhtml\Attributes\Map\Mapattr')
        );

        $form->setValues($data);

        return parent::_prepareForm();
    }


}