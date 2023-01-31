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
 * @package     Ced_CsGroup
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Fruugo\Block\Adminhtml\Profile\Edit\Tab;

use Magento\Framework\Data\Form as DataForm;

class Mapping extends \Magento\Backend\Block\Widget\Form\Generic
{
    //protected $_formFactory;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\ObjectManagerInterface $objectInterface,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_objectManager = $objectInterface;
        parent::__construct($context,$registry, $formFactory);
    }
    protected function _prepareForm(){

        $form=$this->_formFactory->create();
        //$form = $this->getForm();

        //print_r($form);die;
        //$form = new Varien_Data_Form();
        //die;
        $profile = $this->_coreRegistry->registry('current_profile');

        $categorycss = '<style>
    #profile_category_level_1 {
        width : 100% !important;
    }
    #profile_category_level_1 option {
        width : 50px;
    }
</style>';
        $fieldset = $form->addFieldset('category', array('legend'=>__('Category Mapping')));

        $fieldset->addField(
            'profile_category_level_1',
            'select',
            [
                'name' =>'profile_category_level_1',
                'label' => __('Root Level Category'),
                'title' => __('Root Level Category'),
                'value' => $profile->getData('profile_category_level_1'),
                'required' => true,
                'values' => $this->_objectManager->create('Ced\Fruugo\Model\Source\Category\Rootlevel')->toOptionArray()
            ]
        )->setAfterElementHtml( $categorycss );
        /*$fieldset->addField(
            'profile_category_level_',
            'select',
            [
                'name' =>'profile_category_level_2',
                'label' => __('Child Level Category'),
                'title' => __('Child Level Category'),
                'required' => true,
                'values' => $this->_objectManager->create('Ced\Fruugo\Model\Source\Category\Levelone')->toOptionArray()
            ]
        );

        $fieldset->addField('category_js', 'text', [
                'label'     => __('Category JS Mapping'),
                'class'     => 'action',
                'name'      => 'category_js_mapping'
            ]
        );

        $locations = $form->getElement('category_js');
        $locations->setRenderer(
            $this->getLayout()->createBlock('Ced\Fruugo\Block\Adminhtml\Profile\Edit\Tab\Attribute\CategoryJs')
        );*/


        $fieldset->addField(
            'automplete-2',
            'text',
            [
                'name' =>'automplete-2',
                'label' => __('Search Category'),
                'title' => __('Search Category'),
                'class' => __('automplete-2')
            ]
        );
        $fieldset->addField('search_category', 'text', [
                'label'     => __('Search Category'),
                'class'     => 'action',
                'name'      => 'search_category'
            ]
        );
        $locations = $form->getElement('search_category');
        $locations->setRenderer(
            $this->getLayout()->createBlock('Ced\Fruugo\Block\Adminhtml\Profile\Edit\Tab\Search\Searchcategory')
        );

        $fieldset = $form->addFieldset('required_attributes', array('legend'=>__('Fruugo / Magento Attribute Mapping (Required/Optional mapping)')));


        $fieldset->addField('required_attribute', 'text', [
                'label'     => __('Required Attribute Mapping'),
                'class'     => 'action',
                'name'      => 'required_attribute'
            ]
        );

        $locations = $form->getElement('required_attribute');
        $locations->setRenderer(
            $this->getLayout()->createBlock('Ced\Fruugo\Block\Adminhtml\Profile\Edit\Tab\Attribute\Requiredattribute')
        );


        $fieldset = $form->addFieldset('config_attributes', array('legend'=>__('Fruugo / Magento Attribute Mapping (Variant Attribute Mapping)')));
        $fieldset->addField('config_attribute', 'text', [
                'label'     => __('Config Attribute Mapping'),
                'class'     => 'action',
                'name'      => 'required_attribute'
            ]
        );

        $locations = $form->getElement('config_attribute');
        $locations->setRenderer(
            $this->getLayout()->createBlock('Ced\Fruugo\Block\Adminhtml\Profile\Edit\Tab\Attribute\Configattribute')
        );


        $fieldset = $form->addFieldset('optional_attributes', array('legend'=>__('Fruugo / Magento Attribute Mapping (Optional mapping)')));

        $fieldset->addField('recommended_attribute', 'text', [
                'label'     => __('Optional Attribute Mapping'),
                'class'     => 'action',
                'name'      => 'recommended_attribute'
            ]
        );

        $locations = $form->getElement('recommended_attribute');
        $locations->setRenderer(
            $this->getLayout()->createBlock('Ced\Fruugo\Block\Adminhtml\Profile\Edit\Tab\Attribute\Optionalattribute')
        );



        //print_r($data->getData('in_group_vendor_old'));die;
        //$form->setValues($data->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

}