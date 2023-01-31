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
//class Requiredattribute extends \Magento\Backend\Block\Template

class Requiredattribute extends \Magento\Backend\Block\Widget implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{

    /**
     * @var string
     */
    protected $_template = 'Ced_Fruugo::profile/attribute/required_attribute.phtml';


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
            ['label' => __('Add Attribute'), 'onclick' => 'return requiredAttributeControl.addItem()', 'class' => 'add']
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
    public function getFruugoAttributes()
    {
        //$parentCategory = "Vehicle";
        //$childCategory = "LandVehicles";

        /*if($this->_profile && $this->_profile->getId()>0){
            $parentCategory = $this->_profile->getData('profile_category_level_1');
            $childCategory =  $this->_profile->getData('profile_category_level_2');
        }else{
            $parentCategory = $this->getPId();
            $childCategory =  $this->getCId();
        }

      //  echo $parentCategory ." - ". $childCategory;die ("nooooo");

        $requiredAttribute = array();
        $attribute = $this->_objectManager->create('Ced\Fruugo\Model\Categories')->getCollection()
            ->addFieldToFilter('parent_cat_id', $parentCategory)
          //  ->addFieldToFilter('cat_id', $childCategory)->getFirstItem();
          ->addFieldToFilter('path', $childCategory)->getFirstItem();


        if($attribute && $attribute->getId()){
            $model = $this->_objectManager->create('Ced\Fruugo\Model\Attributes');
            $fruugoRequiredAttributes = explode(",", $attribute->getData('fruugo_required_attributes'));
            foreach ($fruugoRequiredAttributes as $item) {
                //$requiredAttribute[$item] = $item;
                $magentoAttr = $model->loadByField('fruugo_attribute_name', $item);
                $temp = array();
                $temp['fruugo_attribute_name'] = $item;
                $temp['magento_attribute_code'] = $magentoAttr->getMagentoAttributeCode();
                $temp['fruugo_attribute_type'] = $magentoAttr->getFruugoAttributeType();
                $temp['fruugo_attribute_enum'] = $magentoAttr->getFruugoAttributeEnum();
                $temp['required'] = true;
                $requiredAttribute[$item] = $temp;
            }
        }


        $attributeCollections = $model =  $this->_objectManager->create('Ced\Fruugo\Model\Attributes')->getCollection();
        $optionalAttribues = array();
        foreach ($attributeCollections as $item) {
            if(!isset($requiredAttribute[$item->getFruugoAttributeName()]))
                $optionalAttribues[$item->getFruugoAttributeName()] = ['fruugo_attribute_name' => $item->getFruugoAttributeName(),
                                                                        'fruugo_attribute_type' => $item->getFruugoAttributeType(),
                                                                        'fruugo_attribute_enum' => $item->getFruugoAttributeEnum(),];
        }
        $this->_fruugoAttribute[] = array(
            'label' => __('Required Attributes'),
            'value' => $requiredAttribute
        );


        $this->_fruugoAttribute[] = array(
            'label' => __('Optional Attributes'),
            'value' => $optionalAttribues
        );*/


        $requiredAttribute = array();
        $fruugoRequiredAttributes = [
            /*[
                'value' => ['fruugo_attribute_name' => 'ProductId', 'magento_attribute_code' => 'entity_id'],
                'label' => __('ProductId')
            ],*/
            [
                'value' => ['fruugo_attribute_name' => 'SkuId', 'magento_attribute_code' => 'sku'],
                'label' => __('SkuId')
            ],
            [
                'value' => ['fruugo_attribute_name' => 'EAN', 'magento_attribute_code' => 'ean'],
                'label' => __('EAN')
            ],
            [
                'value' => ['fruugo_attribute_name' => 'Brand', 'magento_attribute_code' => 'brand'],
                'label' => __('Brand')
            ],
            /*[
                'value' => ['fruugo_attribute_name' => 'Imageurl1', 'magento_attribute_code' => 'image'],
                'label' => __('Imageurl1')
            ],*/
            [
                'value' => ['fruugo_attribute_name' => 'StockQuantity', 'magento_attribute_code' => 'quantity_and_stock_status'],
                'label' => __('StockQuantity')
            ],
            [
                'value' => ['fruugo_attribute_name' => 'Title', 'magento_attribute_code' => 'name'],
                'label' => __('Title')
            ],
            [
                'value' => ['fruugo_attribute_name' => 'Description', 'magento_attribute_code' => 'description'],
                'label' => __('Description')
            ],
            [
                'value' => ['fruugo_attribute_name' => 'NormalPriceWithoutVAT', 'magento_attribute_code' => 'price'],
                'label' => __('NormalPriceWithoutVAT')
            ],
            [
                'value' => ['fruugo_attribute_name' => 'NormalPriceWithVAT', 'magento_attribute_code' => 'price'],
                'label' => __('NormalPriceWithVAT')
            ],
            [
                'value' => ['fruugo_attribute_name' => 'VATRate', 'magento_attribute_code' => 'fruugo_vat_rate'],
                'label' => __('VATRate')
            ],
        ];
        foreach ($fruugoRequiredAttributes as $item) {
            //$requiredAttribute[$item] = $item;
            //$magentoAttr = $model->loadByField('houzz_attribute_name', $item);
            $temp = array();

            $temp['fruugo_attribute_name'] = $item['value']['fruugo_attribute_name'];
            $temp['magento_attribute_code'] = $item['value']['magento_attribute_code'];
            $temp['fruugo_attribute_type'] = 'text';
            $temp['fruugo_attribute_enum'] = '';
            $temp['required'] = true;
            $requiredAttribute[$item['value']['fruugo_attribute_name']] = $temp;
        }
        $this->_fruugoAttribute[] = array(
            'label' => __('Required Attributes'),
            'value' => $requiredAttribute
        );


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

        $mattributecode = '--please select--';

        $magentoattributeCodeArray[''] = $mattributecode;
        $magentoattributeCodeArray['default'] = "--Set Default Value--";

        foreach ($attributes as $attribute){
            $magentoattributeCodeArray[$attribute->getAttributecode()] = $attribute->getAttributecode();
        }

        return $magentoattributeCodeArray;
    }


    public function getFruugoAttributeValuesMapping(){

        //return $this->_fruugoAttribute[0]['value'];
        $data = array();
        if($this->_profile && $this->_profile->getId()>0){
            $data = json_decode($this->_profile->getProfileAttributeMapping(), true);

            if(isset($data['required_attributes']) && isset($data['required_attributes']))
                $data = array_merge($data['required_attributes'], $data['optional_attributes']);
        }else{
            if(!$this->_fruugoAttribute)
                $this->_fruugoAttribute = $this->getFruugoAttributes();

            $model = $this->_objectManager->create('Ced\Fruugo\Model\Attributes');
            if(count($this->_fruugoAttribute[0]['value'])>0){
                $data = $this->_fruugoAttribute[0]['value'];

                /*foreach($this->_fruugoAttribute[0]['value'] as $fruugoAttr){
                    $magentoAttr = $model->loadByField('fruugo_attribute_name', $fruugoAttr)->getMagentoAttributeCode();
                    $temp = array();
                    $temp['fruugo_attribute_name'] = $fruugoAttr;
                    $temp['magento_attribute_code'] = $magentoAttr;
                    $temp['required'] = true;
                    $data[] = $temp;
                }*/
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
