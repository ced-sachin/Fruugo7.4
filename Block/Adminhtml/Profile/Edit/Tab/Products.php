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

class Products extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_coreRegistry;
    protected $_objectManager;
    protected $_massactionBlockName = 'Magento\Backend\Block\Widget\Grid\Massaction\Extended';


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager
    )
    {

        $this->_coreRegistry = $registry;
        $this->_objectManager = $objectManager;

        parent::__construct($context,$backendHelper);
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('asc');
        $this->setId('groupVendorPpcode');
        $this->_massactionBlockName = 'Ced\Fruugo\Block\Adminhtml\Profile\Widget\Grid\Massaction\Extended';
//        $this->_template = 'Ced_Fruugo::widget/grid/massaction.phtml';
        $this->setDefaultFilter(array('massaction'=>1));
        $this->setUseAjax(true);
    }

    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'massaction') {
            $inProfileIds = $this->getProducts();
            $inProfileIds=array_filter($inProfileIds);
            if (empty($inProfileIds)) {
                $inProfileIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in'=>$inProfileIds));
            }
            else {
                if($inProfileIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', array('nin'=>$inProfileIds));
                }
            }
        }
        else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _prepareCollection()
    {
        $profileCode = $this->getRequest()->getParam('id');
        $this->_coreRegistry->register('Id', $profileCode);

        $collection = $this->_objectManager->create('Magento\Catalog\Model\Product')
            ->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('visibility',  array('neq' => 1))
            ->addAttributeToFilter('type_id',  array('simple', 'configurable'));

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {


        /*if ($this->massAction == 0) {
            $this->addColumn('in_profile_products', array(
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'onclick' => 'vendorvalidate()',
                'values' => $this->getProducts(),
                'align' => 'center',
                'index' => 'entity_id',
                'field_name' => 'in_profile[]',
            ));
        }*/
        $this->addColumn('entity_id', array(
            'header'    => __('Product Id'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'entity_id',
            'filter_index' => 'entity_id',
            'type'	  => 'number',
        ));


        $this->addColumn('sku', array(
            'header'      =>__('SKU'),
            'align'         => 'left',
            'type'          => 'text',
            'index'         => 'sku',
            'filter_index' => 'sku',
        ));

        $this->addColumn('name', array(
            'header'      =>__('Product Name'),
            'align'         => 'left',
            'type'          => 'text',
            'index'         => 'name',
            'filter_index' => 'name',
        ));
        $this->addColumn('type_id', [
                'header'        => __('Type'),
                'align'     	=> 'left',
                'index'         => 'type_id',
                'type'          => 'options',
                'options'		=> $this->_objectManager->get('Magento\Catalog\Model\Product\Type')->getOptionArray(),
                'header_css_class' => 'col-group',
                'column_css_class' => 'col-group'
            ]
        );
        $this->addColumn(
            'category', 
            array(
                'header'    => __('Category'),
                'index'     => 'category',
                'sortable'  => false,
                // 'width' => '50px',
                'type'  => 'options',
                'options'   => $this->_objectManager->create('Ced\Fruugo\Model\Source\Category')->getAllOptions(),
                'renderer'  => 'Ced\Fruugo\Block\Adminhtml\Profile\Renderer\Category',
                'filter_condition_callback' => array($this, 'filterCallback'),
                ),
            'name'
        );


        $this->addColumn('status', array(
            'header'        => __('Product Status'),
            'align'     	=> 'left',
            'index'         => 'status',
            'filter_index'  => 'status',
            'type'          => 'options',
            'options'		=> $this->_objectManager->get('Magento\Catalog\Model\Product\Attribute\Source\Status')->getOptionArray(),
        ));

        $attributeSet = $this->_objectManager->get('Magento\Catalog\Model\Product\AttributeSet\Options')->toOptionArray();
        $values = [];
        foreach ($attributeSet as $val){
            $values[$val['value']] = $val['label'];
        }

        /*$this->addColumn('set_name', array(
            'header'        => __('Attrib. Set Name'),
            'align'     	=> 'left',
            'index'         => 'attribute_set_id',
            'filter_index'  => 'attribute_set_id',
            'type'          => 'options',
            'options'		=> $values,
        ));*/

        $store = $this->_storeManager->getStore();
        $this->addColumn('price', array(
            'header'      =>__('Price'),
            'align'         => 'left',
            'type'          => 'price',
            'currency_code' => $store->getBaseCurrency()->getCode(),
            'index'         => 'price',
            'filter_index' => 'price',
        ));
        /*
                   $this->addColumn('qty', array(
                       'header'      =>__('QTY'),
                       'align'         => 'left',
                       'type'          => 'number',
                       'index'         => 'qty',
                       'filter_index' => 'qty',
                   ));
           */


        /*
         $this->addColumn('ggcode_actions',
                 array(
                         'header'=>Mage::helper('adminhtml')->__('Actions'),
                         'width'=>5,
                         'sortable'=>false,
                         'filter'    =>false,
                         'type' => 'action',
                         'actions'   => array(
                                 array(
                                         'caption' => Mage::helper('adminhtml')->__('Remove'),
                                         'onClick' => 'group.deleteFromGroup($group_id);'
                                 )
                         )
                 )
         );
        */




        return parent::_prepareColumns();
    }

    public function getGridUrl(){
        return $this->getUrl('*/*/editProfileProductGrid',
            array('_secure'=>true, '_current'=>true)
        );
    }


    public function getProducts($json=false)
    {
        if ( $this->getRequest()->getPost('in_profile_products') != "" ) {
            return $this->getRequest()->getPost('in_profile_products');
        }

        $this->_objectManager=\Magento\Framework\App\ObjectManager::getInstance();

        $profileId = $this->getRequest()->getParam('id');
        $profile = $this->_coreRegistry->registry('current_profile');


        $products=$this->_objectManager->get('Ced\Fruugo\Model\Profileproducts')->getProfileProducts($profileId);
        if (sizeof($products) > 0) {
            if ( $json ) {
                $jsonProducts = Array();
                foreach($products as $productId) $jsonProducts[$productId] = 0;
                return Mage::helper('core')->jsonEncode((object)$jsonProducts);
            } else {
                return array_values($products);
            }
        } else {
            if ( $json ) {
                return '{}';
            } else {
                return array();
            }
        }


    }

    public function isPartUppercase($string) {
        return (bool) preg_match('/[A-Z]/', $string);
    }

    /**
     * @return array
     */
    protected function _getSelectedProducts()
    {
        $products = $this->getRequest()->getPost('selected_products');
        if ($products === null) {
            //$products = $this->getCategory()->getProductsPosition();
            //return array_keys($products);
        }
        return $products;
    }


    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('entity_id[]');

        $this->getMassactionBlock()->addItem(
            'addproduct', array(
                'label' => __('Add Products'),
                'url' => $this->getUrl('fruugo/profile/save'),
            )
        );
        return $this;
    }

    public function filterCallback($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        //$_category = $this->_objectManager->create('\Magento\Catalog\Model\Category')->load($value);
        $collection->addCategoriesFilter(['in' => $value]);
        return $collection;
    }
}