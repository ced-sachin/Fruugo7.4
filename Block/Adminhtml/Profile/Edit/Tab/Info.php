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



class Info extends \Magento\Backend\Block\Widget\Form\Generic
{
	//protected $_formFactory;
    protected  $_store;
	
public function __construct(
    		\Magento\Backend\Block\Widget\Context $context,
    		\Magento\Framework\Registry $registry,
    		\Magento\Framework\Data\FormFactory $formFactory,
            \Magento\Store\Model\System\Store $store,
            \Magento\Framework\ObjectManagerInterface $objectInterface,
    		array $data = []
    ) {
    	$this->_coreRegistry = $registry;
    	$this->_objectManager = $objectInterface;
    	$this->_store = $store;
    	parent::__construct($context,$registry, $formFactory);
    }
	protected function _prepareForm(){
		
		$form=$this->_formFactory->create();
		//$form = $this->getForm();
		
		//print_r($form);die;
		//$form = new Varien_Data_Form();
		//die;
		$profile = $this->_coreRegistry->registry('current_profile');
	  
		$fieldset = $form->addFieldset('profile_info', array('legend'=>__('Profile Information')));
	
		$fieldset->addField('profile_code', 'text',
				array(
						'name'      => "profile_code",
						'label'     => __('Profile Code'),
						'note'  	=> __('For internal use. Must be unique with no spaces'),
						'class' 	=> 'validate-code',
						'required'  => true,
						'value'     => $profile->getData('profile_code'),
				)
		);
	
		$fieldset->addField('profile_name', 'text',
				array(
						'name'      => "profile_name",
						'label'     => __('Profile Name'),
						'class'     => '',
						'required'  => true,
						'value'    =>$profile->getData('profile_name'),
				)
		);

        /*$fieldset->addField('store_id', 'select',
            array(
                'name'      => "store_id",
                'label'     => __('Store View'),
                'class'     => '',
                'required'  => true,
                'value' => $profile->getData('store_id'),
                'values' => $this->_store->getStoreValuesForForm(false, true),
                'note'  	=> __('Specific store view information of products will send to fruugo'),
            )
        );*/

        $fieldset->addField('profile_status', 'select',
            array(
                'name'      => "profile_status",
                'label'     => __('Profile Status'),
                'class'     => '',
                'required'  => true,
                'value' => $profile->getData('profile_status'),
                'values' => $this->_objectManager->get('Ced\Fruugo\Model\Source\Profile\Status')->getOptionArray(),
                'note'  	=> __('Specific store view information of products will send to fruugo'),
            )
        );


        $fieldset->addField('in_profile_product', 'hidden',
				array(
						'name'  => 'in_profile_product',
						'id'    => 'in_profile_product',
				)
		);
		
	
		$fieldset->addField('in_profile_product_old', 'hidden', array('name' => 'in_profile_product_old','id'=>"in_profile_product_old"));
	
		if ($profile->getId()) {
			$form->getElement('profile_code')->setDisabled(1);
		}
		//print_r($data->getData('in_group_vendor_old'));die;
		//$form->setValues($data->getData());
		$this->setForm($form);
	
		return parent::_prepareForm();
	}
	
}