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
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Fruugo\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Directory separator shorthand
 */
if(!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * EAV setup factory
     * @var EavSetupFactory
     */
    private $eavSetupFactory;


    /**
     * directoryList
     * @var directoryList
     */
    public $directoryList;

    /**
     * InstallData constructor.
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\App\State $state
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->directoryList = $directoryList;
        $state->setAreaCode('adminhtml');
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $appPath = $this->directoryList->getRoot();

        $data = $objectManager->create('\Ced\Fruugo\Helper\Data');
        $fruugoPath =  $appPath.DS."app".DS."code".DS."Ced".DS."Fruugo".DS."Setup".DS."FruugoJson".DS;

         /*$path = $fruugoPath . "FruugoCategories.json";
        $categories = $data->loadFile($path);*/

        /*$path = $fruugoPath . "FruugoAttributes.json";
        $attributes =  $data->loadFile($path);

        $path = $fruugoPath . "FruugoConfigurableAttributes.json";
        $confattributes =  $data->loadFile($path);

        $path = $fruugoPath . "FruugoTaxCodes.json";
        $taxes = $data->loadFile($path);*/

        /*try {
            $setup->getConnection()->insertArray($setup->getTable('fruugo_categories'),
                [
                    'cat_id',
                    'parent_cat_id',
                    'name',
                    'path',
                    'level',
                    'fruugo_required_attributes',
                    'fruugo_attributes'
                ],
                $categories
            );
        } catch (\Exception $e) {

        }*/

        /*try {
            $setup->getConnection()->insertArray($setup->getTable('fruugo_attributes'),
                [
                    'fruugo_attribute_name',
                    'magento_attribute_code',
                    'fruugo_attribute_doc',
                    'is_mapped',
                    'fruugo_attribute_enum',
                    'fruugo_attribute_level',
                    'fruugo_attribute_type',
                    'fruugo_attribute_depends_on',
                ],
                $attributes
            );
        } catch (\Exception $e) {

        }

        try {
            $setup->getConnection()->insertArray($setup->getTable('fruugo_confattributes'),
                [ 'fruugo_attribute_name',
                    'magento_attribute_code',
                    'fruugo_attribute_doc',
                    'is_mapped',
                    'fruugo_attribute_enum',
                    'fruugo_attribute_level',
                    'fruugo_attribute_type',
                    'fruugo_attribute_depends_on',
                ],
                $confattributes
            );
        } catch (\Exception $e) {

        }


        try {
            $setup->getConnection()->insertArray($setup->getTable('fruugo_tax_codes'),
                [ 'id', 'tax_code', 'cat_desc', 'sub_cat_desc'], $taxes );
        }  catch (\Exception $e) {

        }*/


        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        /**
         * Add attributes to the eav/attribute
         */
        $groupName = 'Fruugo';
        $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attributeSetId = $eavSetup->getDefaultAttributeSetId($entityTypeId);
        $eavSetup->addAttributeGroup($entityTypeId, $attributeSetId, $groupName, 1000);
        $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, $groupName);

        /*$eavSetup->addAttribute('catalog_product', 'fruugo_shelf_description', [
                'group'            => 'Fruugo',
                'note'             => '1 to 1000 characters, 
                Abbreviated list of key item features in no more than three bullet points.
                 This is viewable in search, category, and shelf pages. Format bullet points with HTML.',
                'frontend_class'   => 'validate-length maximum-length-1000',
                'input'            => 'text',
                'type'             => 'text',
                'label'            => 'Fruugo Shelf Description',
                'backend'          => '',
                'visible'          => 1,
                'required'         => 0,
                'sort_order'       => 1,
                'user_defined'     => 1,
                'comparable'       => 0,
                'visible_on_front' => 0,
                'global'           => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            ]
        );

        $eavSetup->addAttribute('catalog_product', 'fruugo_productid_type', [
                'group'            => 'Fruugo',
                'note'             => 'Type of unique identifier used in the "Product ID" field.
                 Example: UPC; GTIN; ISBN; ISSN; EAN',
                'input'            => 'select',
                'type'             => 'varchar',
                'label'            => 'Fruugo Product Id Type',
                'backend'          => '',
                'visible'          => 1,
                'required'         => 0,
                'sort_order'       => 2,
                'user_defined'     => 1,
                'source'           => 'Ced\Fruugo\Model\Source\ProductIdType',
                'searchable'       => 1,
                'visible_on_front' => 0,
                'global'           => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            ]
        );

        $eavSetup->addAttribute('catalog_product', 'fruugo_productid', [
                'group'            => 'Fruugo',
                'note'             => ' 1 to 14 characters, Alphanumeric ID that uniquely identifies the product.',
                'frontend_class'   => 'validate-length maximum-length-14',
                'input'            => 'text',
                'type'             => 'varchar',
                'label'            => 'Fruugo Product Id',
                'backend'          => '',
                'visible'          => 1,
                'required'         => 0,
                'sort_order'       => 3,
                'user_defined'     => 1,
                'comparable'       => 0,
                'visible_on_front' => 0,
                'global'           => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            ]
        );

        $eavSetup->addAttribute('catalog_product', 'fruugo_brand', [
                'group'            => 'Fruugo',
                'note'             => '1 to 4000 characters',
                'frontend_class'   => 'validate-length maximum-length-4000',
                'input'            => 'text',
                'type'             => 'text',
                'label'            => 'Fruugo Brand',
                'backend'          => '',
                'visible'          => 1,
                'required'         => 0,
                'sort_order'       => 4,
                'user_defined'     => 1,
                'searchable'       => 1,
                'filterable'       => 0,
                'comparable'       => 0,
                'visible_on_front' => 0,
                'global'           => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            ]
        );

        $eavSetup->addAttribute('catalog_product', 'fruugo_product_taxcode', [
                'group'            => 'Fruugo',
                'note'             => '1 -  10 characters, Code used to identify tax properties of the product',
                'frontend_class'   => 'validate-length maximum-length-10',
                'input'            => 'text',
                'type'             => 'varchar',
                'label'            => 'Fruugo Product Tax Code',
                'backend'          => '',
                'visible'          => 1,
                'required'         => 0,
                'sort_order'       => 5,
                'user_defined'     => 1,
                'searchable'       => 1,
                'filterable'       => 0,
                'comparable'       => 0,
                'visible_on_front' => 0,
                'global'           => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            ]
        );*/

        $eavSetup->addAttribute('catalog_product', 'fruugo_product_status', [
                'group'            => 'Fruugo',
                'note'             => 'Fruugo Product Status',
                'input'            => 'select',
                'type'             => 'varchar',
                'label'            => 'Fruugo Product Status',
                'backend'          => '',
                'visible'          => 1,
                'required'         => 0,
                'sort_order'       => 5,
                'user_defined'     => 1,
                'source'           => 'Ced\Fruugo\Model\Source\ProductStatus',
                'comparable'       => 0,
                'visible_on_front' => 0,
                'global'           => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            ]
        );

        $eavSetup->addAttribute('catalog_product', 'fruugo_product_validation', [
                'group'            => 'Fruugo',
                'note'             => 'Fruugo Product Validation',
                'input'            => 'text',
                'type'             => 'text',
                'label'            => 'Fruugo Product Validation',
                'default'          => 'Not-Validated',
                'backend'          => '',
                'visible'          => 1,
                'required'         => 0,
                'sort_order'       => 5,
                'user_defined'     => 1,
                'comparable'       => 0,
                'visible_on_front' => 0,
                'global'           => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            ]
        );
        $eavSetup->addAttribute('catalog_product', 'fruugo_description', [
                'group'            => 'Fruugo',
                'note'             => 'Fruugo Description',
                'input'            => 'textarea',
                'type'             => 'text',
                'label'            => 'Fruugo Description',
                'backend'          => '',
                'frontend_class'   => 'validate-length maximum-length-5000',
                'visible'          => 1,
                'required'         => 0,
                'sort_order'       => 5,
                'user_defined'     => 1,
                'comparable'       => 0,
                'visible_on_front' => 0,
                'global'           => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            ]
        );


        $textattributeData = [
            'fruugo_price_with_vat' => 'Fruugo Price With Vat',
            'fruugo_price_without_vat' => 'Fruugo Price Without Vat',
            'fruugo_vat_rate' => 'Fruugo Vat Rate',
            'fruugo_leadtime' => 'Fruugo Lead Time',
            'fruugo_categ'=>'Fruugo Category',
            'fruugo_title'=>'Fruugo Title'
        ];
        $i = 1;
        foreach($textattributeData as $attributeKey => $attributeValue)
        {
            $eavSetup->addAttribute('catalog_product', $attributeKey, [
                    'group'            => 'Fruugo',
                    'frontend_class'   => 'validate-length maximum-length-1000',
                    'input'            => 'text',
                    'type'             => 'text',
                    'label'            => $attributeValue,
                    'backend'          => '',
                    'visible'          => 1,
                    'required'         => 0,
                    'sort_order'       => $i,
                    'user_defined'     => 1,
                    'comparable'       => 0,
                    'visible_on_front' => 0,
                    'global'           => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
            $i += 5;
        }
        $eavSetup->addAttribute('catalog_product', 'fruugo_validation_errors', [
            'group'            => 'Fruugo',
            'note'             => 'Fruugo Product Validation Error',
            'input'            => 'textarea',
            'type'             => 'text',
            'label'            => 'Fruugo Product Validation Error',
            'backend'          => '',
            'visible'          => 1,
            'required'         => 0,
            'sort_order'       => 5,
            'user_defined'     => 1,
            'comparable'       => 0,
            'visible_on_front' => 0,
            'global'           => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
        ]
    );

        $eavSetup->addAttribute('catalog_product', 'fruugo_size', [
                'group'            => 'Fruugo',
                'input'            => 'select',
                'type'             => 'varchar',
                'label'            => 'Fruugo Size',
                'backend'          => '',
                'visible'          => 1,
                'required'         => 0,
                'sort_order'       => 2,
                'user_defined'     => 1,
                'source'           => 'Ced\Fruugo\Model\Source\Product\Size',
                'searchable'       => 1,
                'visible_on_front' => 0,
                'global'           => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            ]
        );
        $eavSetup->addAttribute('catalog_product', 'fruugo_color', [
                'group'            => 'Fruugo',
                'input'            => 'select',
                'type'             => 'varchar',
                'label'            => 'Fruugo Color',
                'backend'          => '',
                'visible'          => 1,
                'required'         => 0,
                'sort_order'       => 2,
                'user_defined'     => 1,
                'source'           => 'Ced\Fruugo\Model\Source\Product\Color',
                'searchable'       => 1,
                'visible_on_front' => 0,
                'global'           => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            ]
        );
        $eavSetup->addAttribute('catalog_product', 'fruugo_country', [
                'group'            => 'Fruugo',
                'input'            => 'multiselect',
                'type'             => 'text',
                'label'            => 'Fruugo Available Country',
                'backend'          => '',
                'visible'          => 1,
                'required'         => 0,
                'sort_order'       => 2,
                'user_defined'     => 1,
                'source'           => 'Ced\Fruugo\Model\Source\Product\AvailableCountry',
                'searchable'       => 1,
                'visible_on_front' => 0,
                'global'           => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            ]
        );
        $eavSetup->addAttribute('catalog_product', 'fruugo_discount_with_vat', [
                'group'            => 'Fruugo',
                'input'            => 'text',
                'type'             => 'text',
                'label'            => 'Fruugo Discount Price With Vat',
                'backend'          => '',
                'visible'          => 1,
                'required'         => 0,
                'sort_order'       => 50,
                'user_defined'     => 1,
                'comparable'       => 0,
                'visible_on_front' => 0,
                'frontend_class'   => 'validate-number',
                'global'           => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            ]
        );
        $eavSetup->addAttribute('catalog_product', 'fruugo_discount_without_vat', [
                'group'            => 'Fruugo',
                'input'            => 'text',
                'type'             => 'text',
                'label'            => 'Fruugo Discount Price Without Vat',
                'backend'          => '',
                'visible'          => 1,
                'required'         => 0,
                'sort_order'       => 60,
                'user_defined'     => 1,
                'comparable'       => 0,
                'visible_on_front' => 0,
                'frontend_class'   => 'validate-number',
                'global'           => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            ]
        );
        $eavSetup->addAttribute('catalog_product', 'fruugo_dis_start_date', [
                'group'            => 'Fruugo',
                'input'            => 'date',
                'type'             => 'datetime',
                'label'            => 'Fruugo Discount Start Date',
                'backend'          => '',
                'visible'          => 1,
                'required'         => 0,
                'sort_order'       => 70,
                'user_defined'     => 1,
                'comparable'       => 0,
                'visible_on_front' => 0,
                'frontend_class'   => 'validate-date',
                'global'           => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            ]
        );
        $eavSetup->addAttribute('catalog_product', 'fruugo_dis_end_date', [
                'group'            => 'Fruugo',
                'input'            => 'date',
                'type'             => 'datetime',
                'label'            => 'Fruugo Discount End Date',
                'backend'          => '',
                'visible'          => 1,
                'required'         => 0,
                'sort_order'       => 80,
                'user_defined'     => 1,
                'comparable'       => 0,
                'visible_on_front' => 0,
                'frontend_class'   => 'validate-date',
                'global'           => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            ]
        );
        $eavSetup->addAttribute('catalog_product', 'fruugo_restockdate', [
                'group'            => 'Fruugo',
                'input'            => 'date',
                'type'             => 'datetime',
                'label'            => 'Fruugo Restock Date',
                'backend'          => '',
                'visible'          => 1,
                'required'         => 0,
                'sort_order'       => 90,
                'user_defined'     => 1,
                'comparable'       => 0,
                'visible_on_front' => 0,
                'frontend_class'   => 'validate-date',
                'global'           => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            ]
        );



        /*$category = $objectManager->create('\Ced\Fruugo\Helper\Category');
        $categories = $category->catlist();
        $setup->getConnection()->query($categories);*/

        $dataHelper = $objectManager->create('\Ced\Fruugo\Helper\Data');
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $dataHelper->registerDomain($storeManager->getStore()->getBaseUrl());


    }
}


