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
//class Configattribute extends \Magento\Backend\Block\Template
class Configattribute extends \Magento\Backend\Block\Widget implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface

{
    /**
     * @var string
     */
    protected $_template = 'Ced_Fruugo::profile/attribute/config_attribute.phtml';


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
            ['label' => __('Add Attribute'), 'onclick' => 'return configAttributeControl.addItem()', 'class' => 'add']
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
    public function getFruugoConfigAttributes()
    {



        /*foreach ($attributeCollections as $item) {
            $this->_fruugoAttribute[$item->getFruugoAttributeName()] = $item->getFruugoAttributeName();
            $temp = array();
            $temp['fruugo_attribute_name'] = $item->getFruugoAttributeName();
            $temp['magento_attribute_code'] = $item->getMagentoAttributeCode();
            $temp['fruugo_attribute_type'] = $item->getFruugoAttributeType();
            $configAttribute[$item->getFruugoAttributeName()] = $temp;
        }
        $temp = array();
        $temp['fruugo_attribute_name'] = 'numberOfPieces';
        $temp['magento_attribute_code'] = '';
        $temp['fruugo_attribute_type'] = '';*/
        $configAttribute = array();
        $fruugoConfigAttributes = [
            /*[
                'value' => ['fruugo_attribute_name' => 'ProductId', 'magento_attribute_code' => 'entity_id'],
                'label' => __('ProductId')
            ],*/
            [
                'value' => ['fruugo_attribute_name' => 'AttributeSize', 'magento_attribute_code' => 'fruugo_size'],
                'label' => __('AttributeSize')
            ],
            [
                'value' => ['fruugo_attribute_name' => 'AttributeColor', 'magento_attribute_code' => 'fruugo_color'],
                'label' => __('AttributeColor')
            ],
            [
                'value' => ['fruugo_attribute_name' => 'Attribute1', 'magento_attribute_code' => 'Attribute1'],
                'label' => __('Attribute1')
            ],
            [
                'value' => ['fruugo_attribute_name' => 'Attribute2', 'magento_attribute_code' => 'Attribute2'],
                'label' => __('Attribute2')
            ],
            [
                'value' => ['fruugo_attribute_name' => 'Attribute3', 'magento_attribute_code' => 'Attribute3'],
                'label' => __('Attribute3')
            ],
            [
                'value' => ['fruugo_attribute_name' => 'Attribute4', 'magento_attribute_code' => 'Attribute4'],
                'label' => __('Attribute4')
            ],
            [
                'value' => ['fruugo_attribute_name' => 'Attribute5', 'magento_attribute_code' => 'Attribute5'],
                'label' => __('Attribute5')
            ],
            [
                'value' => ['fruugo_attribute_name' => 'Attribute6', 'magento_attribute_code' => 'Attribute6'],
                'label' => __('Attribute6')
            ],
            [
                'value' => ['fruugo_attribute_name' => 'Attribute7', 'magento_attribute_code' => 'Attribute7'],
                'label' => __('Attribute7')
            ],
            [
                'value' => ['fruugo_attribute_name' => 'Attribute8', 'magento_attribute_code' => 'Attribute8'],
                'label' => __('Attribute8')
            ],
            [
                'value' => ['fruugo_attribute_name' => 'Attribute9', 'magento_attribute_code' => 'Attribute9'],
                'label' => __('Attribute9')
            ],
            [
                'value' => ['fruugo_attribute_name' => 'Attribute10', 'magento_attribute_code' => 'Attribute10'],
                'label' => __('Attribute10')
            ]
        ];

        foreach ($fruugoConfigAttributes as $item) {
            $temp = array();
            $temp['fruugo_attribute_name'] = $item['value']['fruugo_attribute_name'];
            $temp['magento_attribute_code'] = $item['value']['magento_attribute_code'];
            $temp['fruugo_attribute_type'] = 'text';
            //$temp['fruugo_attribute_enum'] = '';
            //$temp['required'] = true;
            $configAttribute[$item['value']['fruugo_attribute_name']] = $temp;
        }
        //$configAttribute['numberOfPieces'] = $temp;
        $this->_fruugoAttribute = $configAttribute;
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
            ->addFieldToFilter('frontend_input', ['in' => ['select', 'multiselect']])
            ->getItems();
        $magentoattributeCodeArray = array();
        $mattributecode = '--please select--';

       $magentoattributeCodeArray['--please select--'] = $mattributecode;
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
            $configdata = json_decode($this->_profile->getProfileAttributeMapping(), true);
            if(isset($configdata['variant_attributes']))
                $data = $configdata['variant_attributes'];
        }else{
            if(!$this->_fruugoAttribute)
                $this->_fruugoAttribute = $this->getFruugoConfigAttributes();

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
