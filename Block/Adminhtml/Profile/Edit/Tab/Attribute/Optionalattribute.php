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

namespace Ced\Fruugo\Block\Adminhtml\Profile\Edit\Tab\Attribute;

/**
 * Rolesedit Tab Display Block.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
//class Optionalattribute extends \Magento\Backend\Block\Template
class Optionalattribute extends \Magento\Backend\Block\Widget implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface

{
    /**
     * @var string
     */
    protected $_template = 'Ced_Fruugo::profile/attribute/optional_attribute.phtml';


    protected  $_objectManager;

    protected  $_coreRegistry;

    protected  $_profile;

    protected  $_fruugoAttribute;


    public function __construct(\Magento\Backend\Block\Template\Context $context,
                                \Magento\Framework\ObjectManagerInterface $objectManager,
                                \Magento\Framework\Registry $registry,
                                array $data = []

    )
    {
        $this->_objectManager = $objectManager;
        $this->_coreRegistry = $registry;

        $this->_profile = $this->_coreRegistry->registry('current_profile');

        parent::__construct($context, $data);
    }

    /**
     * Prepare global layout
     * Add "Add tier" button to layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            ['label' => __('Add Attribute'), 'onclick' => 'return optionalAttributeControl.addItem()', 'class' => 'add']
        );
        $button->setName('add_required_item_button');

        $this->setChild('add_button', $button);
        return parent::_prepareLayout();
    }

    /**
     * Retrieve 'add group price item' button HTML
     *
     * @return string
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }



    /**
     * Retrieve fruugo attributes
     *
     * @param int|null $groupId  return name by customer group id
     * @return array|string
     */
    public function getFruugoOptionalAttributes()
    {
        /*$attributeCollections = $this->_objectManager->create('Ced\Fruugo\Model\Confattributes')->getCollection();
        $optionalAttribute = [];
        foreach ($attributeCollections as $item) {
            $this->_fruugoAttribute[$item->getFruugoAttributeName()] = $item->getFruugoAttributeName();
            $temp = array();
            $temp['fruugo_attribute_name'] = $item->getFruugoAttributeName();
            $temp['magento_attribute_code'] = $item->getMagentoAttributeCode();
            $temp['fruugo_attribute_type'] = $item->getFruugoAttributeType();
            $optionalAttribute[$item->getFruugoAttributeName()] = $temp;
        }
        $temp = array();
        $temp['fruugo_attribute_name'] = 'numberOfPieces';
        $temp['magento_attribute_code'] = '';
        $temp['fruugo_attribute_type'] = '';*/
        $optionalAttribute = array();
        $fruugoOptionalAttributes = [
            /*[
                'value' => ['fruugo_attribute_name' => 'ProductId', 'magento_attribute_code' => 'entity_id'],
                'label' => __('ProductId')
            ],*/
            [
                'value' => ['fruugo_attribute_name' => 'DiscountPriceWithoutVAT', 'magento_attribute_code' => 'fruugo_discount_without_vat'],
                'label' => __('DiscountPriceWithoutVAT')
            ],
            [
                'value' => ['fruugo_attribute_name' => 'DiscountPriceWithVAT', 'magento_attribute_code' => 'fruugo_discount_with_vat'],
                'label' => __('DiscountPriceWithVAT')
            ],
            [
                'value' => ['fruugo_attribute_name' => 'ISBN', 'magento_attribute_code' => 'fruugo_book_number'],
                'label' => __('ISBN')
            ],
            [
                'value' => ['fruugo_attribute_name' => 'Manufacturer', 'magento_attribute_code' => 'fruugo_manufacturer'],
                'label' => __('Manufacturer')
            ],
            [
                'value' => ['fruugo_attribute_name' => 'RestockDate', 'magento_attribute_code' => 'fruugo_restockdate'],
                'label' => __('RestockDate')
            ],
            [
                'value' => ['fruugo_attribute_name' => 'LeadTime', 'magento_attribute_code' => 'fruugo_leadtime'],
                'label' => __('LeadTime')
            ],
            [
                'value' => ['fruugo_attribute_name' => 'PackageWeight', 'magento_attribute_code' => 'fruugo_package_weight'],
                'label' => __('PackageWeight')
            ],
            [
                'value' => ['fruugo_attribute_name' => 'Country', 'magento_attribute_code' => 'fruugo_country'],
                'label' => __('Country')
            ],
            [
                'value' => ['fruugo_attribute_name' => 'DiscountPriceStartDate', 'magento_attribute_code' => 'fruugo_dis_start_date'],
                'label' => __('DiscountPriceStartDate')
            ],
            [
                'value' => ['fruugo_attribute_name' => 'DiscountPriceEndDate', 'magento_attribute_code' => 'fruugo_dis_end_date'],
                'label' => __('DiscountPriceEndDate')
            ],
        ];
        foreach ($fruugoOptionalAttributes as $item) {
            $temp = array();
            $temp['fruugo_attribute_name'] = $item['value']['fruugo_attribute_name'];
            $temp['magento_attribute_code'] = $item['value']['magento_attribute_code'];
            $temp['fruugo_attribute_type'] = 'text';
            //$temp['fruugo_attribute_enum'] = '';
            //$temp['required'] = true;
            $optionalAttribute[$item['value']['fruugo_attribute_name']] = $temp;
        }
        //$optionalAttribute['numberOfPieces'] = $temp;
        $this->_fruugoAttribute = $optionalAttribute;
        return $this->_fruugoAttribute;
    }


    /**
     * Retrieve magento attributes
     *
     * @param int|null $groupId  return name by customer group id
     * @return array|string
     */
    public function getMagentoAttributes()
    {
        $attributes = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection')
            ->getItems();
        $magentoattributeCodeArray = array();
        foreach ($attributes as $attribute) {
            $magentoattributeCodeArray[$attribute->getAttributecode()] = $attribute->getAttributecode();
        }


        /*$attributes = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addFieldToFilter('frontend_input', ['in' => ['select', 'multiselect']])
            ->getItems();
        $mattributecode =
            $model->getMagentoAttributeCode()!=' ' ? $model->getMagentoAttributeCode() : '--please select--';
        if ($mattributecode == '--please select--') {
            $magentoattributeCodeArray[''] = $mattributecode;
        } else {
            $magentoattributeCodeArray[$mattributecode] = $mattributecode;
        }*/

        /*foreach ($attributes as $attribute) {
            $magentoattributeCodeArray[$attribute->getAttributecode()] = $attribute->getAttributecode();
        }*/





        return $magentoattributeCodeArray;
    }


    public function getFruugoAttributeValuesMapping(){
        $data = array();
        if($this->_profile && $this->_profile->getId()>0){
            $optionaldata = json_decode($this->_profile->getProfileAttributeMapping(), true);
            if(isset($optionaldata['recommended_attribute']))
                $data = $optionaldata['recommended_attribute'];
        }else{
            if(!$this->_fruugoAttribute)
                $this->_fruugoAttribute = $this->getFruugoOptionalAttributes();

            //$collection = $this->_objectManager->create('Ced\Fruugo\Model\Confattributes')->getCollection()->addFieldToFilter('magento_attribute_code', array('neq' => 'NULL' ));
            foreach($this->_fruugoAttribute as $key => $value){
                if(isset($value['magento_attribute_code']) && $value['magento_attribute_code']!=""){
                    $data[] = $value;

                }
            }
        }

        return $data;
    }


    /**
     * Render form element as HTML
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

}
