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

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */

class InstallSchema implements InstallSchemaInterface
{
    /**
     *
     * {@inheritdoc} @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'fruugo_categories'
         */
        $table = $installer->getConnection()->newTable( $installer->getTable( 'fruugo_categories' ) )
            ->addColumn( 'id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
                'ID'
            )
            ->addColumn( 'cat_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false,
                'default' => '0'
            ],
                'Category Id'
            )
            ->addColumn( 'parent_cat_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false
            ],
                'Parent Category Id'
            )
            ->addColumn( 'name', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'nullable' => false,
            ],
                'Name'
            )
            ->addColumn( 'path', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false,
            ],
                'path'
            )
            ->addColumn( 'level', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, [
                'default' => '0'
            ],
                'Level'
            )
            ->addColumn( 'fruugo_required_attributes', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'nullable' => true,
            ],
                'Fruugo Required Attributes'
            )
            ->addColumn( 'fruugo_attributes', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'nullable' => true,
            ],
                'Fruugo Attributes'
            )
            ->addColumn( 'magento_cat_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'nullable' => true,
            ],
                'Magento Category Id'
            )
            ->addIndex(
                'path',
                ['path'],
                ['type'	=>
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ]
            );

        $installer->getConnection()->createTable( $table );

        /**
         * Create table 'fruugo_attributes'
         */
        $table = $installer->getConnection()->newTable( $installer->getTable( 'fruugo_attributes' ) )
            ->addColumn( 'id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
                'ID'
            )
            ->addColumn( 'fruugo_attribute_name', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => true,
            ],
                'Fruugo Attribute Name'
            )
            ->addColumn( 'magento_attribute_code', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => true,
            ], 'Magento Attribute Code'
            )
            ->addColumn( 'fruugo_attribute_doc', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'nullable' => true,
            ],
                'Fruugo Attribute Documentation'
            )

            ->addColumn( 'is_mapped', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, [
                'unsigned' => true,
                'nullable' => false,
                'default' => '0'
            ],
                'Is Mapping Attribute'
            )
            ->addColumn( 'fruugo_attribute_enum', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'nullable' => true
            ],
                'Fruugo Attribute Enumeration Value'
            )

            ->addColumn( 'fruugo_attribute_level', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false,
                'default' => 'optional'
            ],
                'Fruugo Attribute Level'
            )
            ->addColumn( 'fruugo_attribute_type', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false,
                'default' => 'string'
            ],
                'Fruugo Attribute Type String/Boolean/Integer/Decimal/Double'
            )
            ->addColumn( 'fruugo_attribute_depends_on', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'nullable' => true
            ],
                'Fruugo Attribute Depends On'
            )
            ->addIndex(
                'fruugo_attribute_name',
                ['fruugo_attribute_name'],
                ['type'	=>
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ]
            );
        $installer->getConnection()->createTable( $table );

        $table = $installer->getConnection()->newTable( $installer->getTable( 'fruugo_feeds' ) )
            ->addColumn( 'id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
                'ID'
            )->addIndex(
                'fruugo_feeds', //table name
                'id',    // index name
                [
                    'id'   // filed or column name
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT //type of index
            )
            ->addColumn( 'feed_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false,
                'default' => '0'
            ],
                'Feed Id'
            )->addIndex(
                'fruugo_feeds', //table name
                'feed_id',    // index name
                [
                    'feed_id'   // filed or column name
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT //type of index
            )
            ->addColumn( 'feed_status', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false,
                'default' => 'ERROR'
            ],
                'Feed Status'
            )->addIndex(
                'fruugo_feeds', //table name
                'feed_status',    // index name
                [
                    'feed_status'   // filed or column name
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT //type of index
            )
            ->addColumn( 'feed_source', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false,
                'default' => 'API'
            ],
                'Feed Source'
            )->addIndex(
                'fruugo_feeds', //table name
                'feed_source',    // index name
                [
                    'feed_source'   // filed or column name
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT //type of index
            )
            ->addColumn( 'feed_type', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => true,
            ],
                'Feed Type'
            )->addIndex(
                'fruugo_feeds', //table name
                'feed_type',    // index name
                [
                    'feed_type'   // filed or column name
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT //type of index
            )
            ->addColumn( 'items_received', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, [
                'default' => '0'
            ],
                'Items Received'
            )->addIndex(
                'fruugo_feeds', //table name
                'items_received',    // index name
                [
                    'items_received'   // filed or column name
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT //type of index
            )
            ->addColumn( 'items_succeeded', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, [
                'default' => '0'
            ],
                'Items Succeeded'
            )->addIndex(
                'fruugo_feeds', //table name
                'items_succeeded',    // index name
                [
                    'items_succeeded'   // filed or column name
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT //type of index
            )
            ->addColumn( 'items_failed', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, [
                'default' => '0'
            ],
                'Items Failed'
            )->addIndex(
                'fruugo_feeds', //table name
                'items_failed',    // index name
                [
                    'items_failed'   // filed or column name
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT //type of index
            )
            ->addColumn( 'items_processing', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, [
                'default' => '0'
            ],
                'Items Processing'
            )->addIndex(
                'fruugo_feeds', //table name
                'items_processing',    // index name
                [
                    'items_processing'   // filed or column name
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT //type of index
            )
            ->addColumn( 'feed_date', \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME, null, [
                'default' => null,
            ],
                'Feed Date'
            )->addIndex(
                'fruugo_feeds', //table name
                'feed_date',    // index name
                [
                    'feed_date'   // filed or column name
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT //type of index
            )
            ->addColumn( 'feed_file', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'default' => null,
                'nullable' => true,

            ],
                'Upload File Path'
            )
            ->addColumn( 'feed_errors', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'default' => null,
                'nullable' => true,

            ],
                'Feed Errors'
            )
            ->addIndex(
                'feed_id',
                ['feed_id'],
                ['type'	=>
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ]
            );
        $installer->getConnection()->createTable( $table );

        /**
         * Create table 'fruugo_attributes'
         */
        $table = $installer->getConnection()->newTable( $installer->getTable( 'fruugo_attributes' ) )
            ->addColumn( 'id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
                'ID'
            )
            ->addColumn( 'fruugo_attribute_name', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => true,
            ],
                'Fruugo Attribute Name'
            )
            ->addColumn( 'magento_attribute_code', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => true,
            ], 'Magento Attribute Code'
            )
            ->addColumn( 'fruugo_attribute_doc', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'nullable' => true,
            ],
                'Fruugo Attribute Documentation'
            )

            ->addColumn( 'is_mapped', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, [
                'unsigned' => true,
                'nullable' => false,
                'default' => '0'
            ],
                'Is Mapping Attribute'
            )
            ->addColumn( 'fruugo_attribute_enum', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'nullable' => true
            ],
                'Fruugo Attribute Enumeration Value'
            )

            ->addColumn( 'fruugo_attribute_level', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false,
                'default' => 'optional'
            ],
                'Fruugo Attribute Level'
            )
            ->addColumn( 'fruugo_attribute_type', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false,
                'default' => 'string'
            ],
                'Fruugo Attribute Type String/Boolean/Integer/Decimal/Double'
            )
            ->addColumn( 'fruugo_attribute_depends_on', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'nullable' => true
            ],
                'Fruugo Attribute Depends On'
            )
            ->addIndex(
                'fruugo_attribute_name',
                ['fruugo_attribute_name'],
                ['type'	=>
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ]
            );
        $installer->getConnection()->createTable( $table );

     /**
         * Create table 'fruugo_confattributes'
         */
        $table = $installer->getConnection()->newTable( $installer->getTable( 'fruugo_confattributes' ) )
            ->addColumn( 'id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
                'ID'
            )
            ->addColumn( 'fruugo_attribute_name', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => true,
            ],
                'Fruugo Attribute Name'
            )
            ->addColumn( 'magento_attribute_code', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => true,
            ], 'Magento Attribute Code'
            )
            ->addColumn( 'fruugo_attribute_doc', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'nullable' => true,
            ],
                'Fruugo Attribute Documentation'
            )

            ->addColumn( 'is_mapped', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, [
                'unsigned' => true,
                'nullable' => false,
                'default' => '0'
            ],
                'Is Mapping Attribute'
            )
            ->addColumn( 'fruugo_attribute_enum', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'nullable' => true
            ],
                'Fruugo Attribute Enumeration Value'
            )

            ->addColumn( 'fruugo_attribute_level', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false,
                'default' => 'optional'
            ],
                'Fruugo Attribute Level'
            )
            ->addColumn( 'fruugo_attribute_type', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false,
                'default' => 'string'
            ],
                'Fruugo Attribute Type String/Boolean/Integer/Decimal/Double'
            )
            ->addColumn( 'fruugo_attribute_depends_on', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'nullable' => true
            ],
                'Fruugo Attribute Depends On'
            )
            ->addIndex(
                'fruugo_attribute_name',
                ['fruugo_attribute_name'],
                ['type'	=>
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ]
            );
        $installer->getConnection()->createTable( $table );

        /**
         * Create table 'fruugo_order_detail'
         */
        $table = $installer->getConnection()->newTable($installer->getTable ('fruugo_order_detail'))
            ->addColumn('id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
                'ID'
            )->addIndex(
                'fruugo_order_detail', //table name
                'id',    // index name
                [
                    'id'   // filed or column name
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT //type of index
            )
            ->addColumn('merchant_order_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,null, [
                'unsigned' => true,
                'nullable' => true
            ],
                'Merchant Order Id'
            )->addIndex(
                'fruugo_order_detail', //table name
                'merchant_order_id',    // index name
                [
                    'merchant_order_id'   // filed or column name
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT //type of index
            )
            ->addColumn('deliver_by', \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,null, [
                'unsigned' => true,
                'nullable' => true
            ],
                'Deliver By'
            )->addIndex(
                'fruugo_order_detail', //table name
                'deliver_by',    // index name
                [
                    'deliver_by'   // filed or column name
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT //type of index
            )
            ->addColumn('order_place_date', \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,null, [
                'unsigned' => true,
                'nullable' => true
            ],
                'Order Place Date'
            )->addIndex(
                'fruugo_order_detail', //table name
                'order_place_date',    // index name
                [
                    'order_place_date'   // filed or column name
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT //type of index
            )
            ->addColumn('magento_order_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,null,[
                'unsigned' => true,
                'nullable' => true
            ],
                'Magento Order Id'
            )->addIndex(
                'fruugo_order_detail', //table name
                'magento_order_id',    // index name
                [
                    'magento_order_id'   // filed or column name
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT //type of index
            )
            ->addColumn('status', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,null, [
                'nullable' => true
            ],
                'status'
            )->addIndex(
                'fruugo_order_detail', //table name
                'status',    // index name
                [
                    'status'   // filed or column name
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT //type of index
            )
            ->addColumn('order_data', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null,[
                'nullable' => true
            ],
                'Order Data'
            )->addIndex(
                'fruugo_order_detail', //table name
                'order_data',    // index name
                [
                    'order_data'   // filed or column name
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT //type of index
            )
            ->addColumn('shipment_data', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,null, [
                'nullable' => true
            ],
                'Shipping Data'
            )->addIndex(
                'fruugo_order_detail', //table name
                'shipment_data',    // index name
                [
                    'shipment_data'   // filed or column name
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT //type of index
            )
            ->addColumn('purchase_order_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,null, [
                'nullable' => true
            ],
                'Reference Order Id'
            )->addIndex(
                'fruugo_order_detail', //table name
                'reference_order_id',    // index name
                [
                    'reference_order_id'   // filed or column name
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT //type of index
            )
            ->addColumn('customer_cancelled', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, [
                'nullable' => true
            ],
                'Customer Cancelled'
            )->addColumn(
                'acknowledge_data',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [
                    'nullable' => true,
                    'default'  => NULL,
                ],
                'Acknowledge Data'
            )->addIndex(
                'fruugo_order_detail', //table name
                'customer_cancelled', // index name
                [
                    'customer_cancelled'   // filed or column name
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT //type of index
            );

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'fruugo_order_import_error'
         */
        $table = $installer->getConnection()->newTable($installer->getTable('fruugo_order_import_error'))
            ->addColumn('id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
                'ID'
            )
            ->addColumn('purchase_order_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'unsigned' => true,
                'nullable' => false
            ],
                'Purchase Order Id'
            )
            ->addColumn('reference_number', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false,
                'default' => '0'
            ],
                'Reference_Number'
            )
            ->addColumn('reason', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'unsigned' => true,
                'nullable' => false
            ],
                'Reason'
            )
            ->addColumn('order_data', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'unsigned' => true,
                'nullable' => false
            ],
                'Order Data'
            );

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'fruugo_order_import_error'
         */
        $table = $installer->getConnection()->newTable($installer->getTable('fruugo_order_import_error'))
            ->addColumn('id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
                'ID'
            )
            ->addColumn('merchant_order_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'unsigned' => true,
                'nullable' => false
            ],
                'Merchant Order Id'
            )
            ->addColumn('reference_number', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false,
                'default' => '0'
            ],
                'Reference_Number'
            )
            ->addColumn('reason', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'unsigned' => true,
                'nullable' => false
            ],
                'Reason'
            )
            ->addColumn('order_item_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'unsigned' => true,
                'nullable' => false
            ],
                'order_item_id'
            );

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'fruugo_tax_codes'
         */

        $table = $installer->getConnection()->newTable($installer->getTable('fruugo_tax_codes'))
            ->addColumn('id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
                'ID'
            )
            ->addColumn('tax_code', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'unsigned' => true,
                'nullable' => false
            ],
                'Tax Code'
            )
            ->addColumn('cat_desc', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'nullable' => true
            ],
                'Tax Description'
            )->addColumn('sub_cat_desc', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, [
                'nullable' => true
            ],
                'Tax Sub-Cat Description'
            )->addIndex(
                $installer->getIdxName(
                        'fruugo_tax_codes',
                        [
                            'cat_desc',
                            'sub_cat_desc'
                        ],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                    ),
                    [
                        'cat_desc',
                        'sub_cat_desc'
                    ],
                    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT]
                )->setComment(
                    'Tax Code'
                );

        $installer->getConnection()->createTable($table);
        /**
         * Create table 'fruugo_refund_table'
         */
        $table = $installer->getConnection()->newTable($installer->getTable ('fruugo_refund_table'))
            ->addColumn('id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
                'ID'
            )
            ->addColumn('magento_order_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'unsigned' => true,
                'nullable' => false,

            ],
                'magento_order_id'
            )
            ->addColumn('quantity_returned', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'unsigned' => true,
                'nullable' => false,

            ],
                'quantity_returned'
            )
            ->addColumn('refund_quantity', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'unsigned' => true,
                'nullable' => false,

            ],
                'refund_quantity'
            )
            ->addColumn('refund_reason', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false,

            ],
                'refund_reason'
            )
            ->addColumn('msg_to_customer', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false,

            ],
                'msg_to_customer'
            )
            ->addColumn('msg_to_fruugo', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false,

            ],
                'msg_to_fruugo'
            )
            ->addColumn('refund_tax', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false,

            ],
                'refund_tax'
            )
            ->addColumn('refund_shippingcost', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false,

            ],
                'refund_shippingcost'
            )
            ->addColumn('refund_shippingtax', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false,

            ],
                'refund_shippingtax'
            )
            ->addColumn('refund_purchaseOrderId', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false,

            ],
                'refund_purchaseOrderId'
            )
            ->addColumn('refund_customerOrderId', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false,

            ],
                'refund_customerOrderId'
            )
            ->addColumn('refund_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [
                'nullable' => false,

            ],
                'refund_id'
            )
            ->addColumn('refund_status', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255,[
                'nullable' => false,

            ],
                'refund_status'
            )
            ->addColumn('saved_data', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,null, [
                'nullable' => false,

            ],
                'saved_data'
            );

        $installer->getConnection()->createTable($table);
    }
}
