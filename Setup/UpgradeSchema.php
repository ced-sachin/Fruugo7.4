<?php
namespace Ced\Fruugo\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements  UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup,
                            ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            // Get module table
            $tableName = $setup->getTable('fruugo_profile');

            // Check if the table already exists
            if ($setup->getConnection()->isTableExists($tableName) != true) {
                // Create tutorial_simplenews table
                $table = $setup->getConnection()
                    ->newTable($tableName)
                    ->addColumn(
                        'id',
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'identity' => true,
                            'unsigned' => true,
                            'nullable' => false,
                            'primary' => true
                        ],
                        'ID'
                    )
                    ->addColumn(
                        'profile_code',
                        Table::TYPE_TEXT,
                        40,
                        ['nullable' => false, 'default' => ''],
                        'Profile Code'
                    )
                    ->addColumn(
                        'profile_status',
                        Table::TYPE_SMALLINT,
                        null,
                        ['unsigned' => true, 'nullable' => true, 'default' => 0],
                        'Profile Status'
                    )
                    ->addColumn(
                        'profile_name',
                        Table::TYPE_TEXT,
                        100,
                        ['nullable' => true, 'default' => ''],
                        'Profile Name'
                    )
                    /*->addColumn(
                        'store_id',
                        Table::TYPE_TEXT,
                        10,
                        ['nullable' => true],
                        'Store Id'
                    )*/
                    ->addColumn(
                        'profile_category_level_1',
                        Table::TYPE_TEXT,
                        200,
                        ['nullable' => true, 'default' => ''],
                        'Profile Category Level 1'
                    )
                    ->addColumn(
                        'profile_category_level_2',
                        Table::TYPE_TEXT,
                        200,
                        ['nullable' => true, 'default' => ''],
                        'Profile Category Level 2'
                    )
                    ->addColumn(
                        'profile_attribute_mapping',
                        Table::TYPE_TEXT,
                        null,
                        ['nullable' => true, 'default' => ''],
                        'Profile Attribute Mapping'
                    )
                    ->addColumn(
                        'inventory_setting',
                        Table::TYPE_TEXT,
                        200,
                        ['nullable' => true, 'default' => ''],
                        'Inventory Setting'
                    )
                    ->addColumn(
                        'inventory_threshold_value',
                        Table::TYPE_INTEGER,
                        null,
                        [
                        'nullable' => true,
                        'default' => 1
                        ],
                        'Inventory Threshold Value'
                    )
                    ->addColumn(
                        'fixed_threshold_value',
                        Table::TYPE_INTEGER,
                        null,
                        [
                        'nullable' => true,
                        'default' => 1
                        ],
                        'Fixed Threshold Value'
                    )
                    ->addIndex(
                        $setup->getIdxName(
                            'fruugo_profile',
                            ['profile_code'],
                            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                        ),
                        ['profile_code'],
                        ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
                    )
                    ->setComment('Profile Table')
                    ->setOption('type', 'InnoDB')
                    ->setOption('charset', 'utf8');
                $setup->getConnection()->createTable($table);

            }


            // Get module table
            $tableName = $setup->getTable('fruugo_profile_products');

            // Check if the table already exists
            if ($setup->getConnection()->isTableExists($tableName) != true) {
                // Create tutorial_simplenews table
                $table = $setup->getConnection()
                    ->newTable($tableName)
                    ->addColumn(
                        'id',
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'identity' => true,
                            'unsigned' => true,
                            'nullable' => false,
                            'primary' => true
                        ],
                        'ID'
                    )
                    ->addColumn(
                        'profile_id',
                        Table::TYPE_INTEGER,
                        null,
                        ['unsigned' => true, 'nullable' => false],
                        'Profile Id'
                    )
                    ->addColumn(
                        'product_id',
                        Table::TYPE_INTEGER,
                        null,
                        ['unsigned' => true, 'nullable' => false],
                        'Profile Status'
                    )
                    ->addForeignKey(
                        $setup->getFkName('fruugo_profile_products', 'profile_id', 'fruugo_profile', 'id'),
                        'profile_id',
                        $setup->getTable('fruugo_profile'),
                        'id',
                        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                    )
                    ->addIndex(
                        $setup->getIdxName(
                            'fruugo_profile_products',
                            ['profile_id', 'product_id'],
                            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                        ),
                        ['profile_id', 'product_id'],
                        ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
                    )
                    ->addIndex(
                        $setup->getIdxName(
                            'fruugo_profile_products',
                            ['product_id'],
                            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                        ),
                        ['product_id'],
                        ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
                    )
                    ->setComment('Profile Products Table')
                    ->setOption('type', 'InnoDB')
                    ->setOption('charset', 'utf8');
                $setup->getConnection()->createTable($table);

            }

            // Get module table
            $tableName = $setup->getTable('fruugo_logs');
            // Check if the table already exists
            if ($setup->getConnection()->isTableExists($tableName) != true) {
                // Create tutorial_simplenews table
                $table = $setup->getConnection()
                    ->newTable($tableName)
                    ->addColumn(
                        'id',
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'identity' => true,
                            'unsigned' => true,
                            'nullable' => false,
                            'primary' => true
                        ],
                        'ID'
                    )
                    ->addColumn(
                        'log_type',
                        Table::TYPE_TEXT,
                        null,
                        ['unsigned' => true, 'nullable' => false],
                        'Log Type'
                    )
                    ->addColumn(
                        'log_sub_type',
                        Table::TYPE_TEXT,
                        null,
                        ['unsigned' => true, 'nullable' => false],
                        'Profile Status'
                    )
                    ->addColumn(
                        'log_date',
                        Table::TYPE_TEXT,
                        null,
                        ['unsigned' => true, 'nullable' => false],
                        'Log Date'
                    )
                    ->addColumn(
                        'log_comment',
                        Table::TYPE_TEXT,
                        null,
                        ['unsigned' => true, 'nullable' => false],
                        'Log Comment'
                    )
                    ->addColumn(
                        'log_value',
                        Table::TYPE_TEXT,
                        null,
                        ['unsigned' => true, 'nullable' => false],
                        'Profile Status'
                    )
                    ->setComment('Fruugo Logs Table');
                $setup->getConnection()->createTable($table);
            }

            // Get module table
            $tableName = $setup->getTable('fruugo_product_change');
            // Check if the table already exists
            if ($setup->getConnection()->isTableExists($tableName) != true) {
                // Create tutorial_simplenews table
                $table = $setup->getConnection()
                    ->newTable($tableName)
                    ->addColumn(
                        'id',
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'identity' => true,
                            'unsigned' => true,
                            'nullable' => false,
                            'primary' => true
                        ],
                        'ID'
                    )
                    ->addColumn(
                        'product_id',
                        Table::TYPE_INTEGER,
                        null,
                        ['unsigned' => true, 'nullable' => false],
                        'Profile Status'
                    )
                    ->addColumn(
                        'old_value',
                        Table::TYPE_TEXT,
                        100,
                        ['nullable' => true, 'default' => ''],
                        'Old Value'
                    )
                    ->addColumn(
                        'new_value',
                        Table::TYPE_TEXT,
                        100,
                        ['nullable' => true, 'default' => ''],
                        'New Value'
                    )
                    ->addColumn(
                        'action',
                        Table::TYPE_TEXT,
                        50,
                        ['nullable' => true, 'default' => ''],
                        'Action'
                    )
                    ->addColumn(
                        'cron_type',
                        Table::TYPE_TEXT,
                        50,
                        ['nullable' => true, 'default' => ''],
                        'Cron type'
                    )
                    ->setComment('Fruugo Product Change')
                    ->setOption('type', 'InnoDB')
                    ->setOption('charset', 'utf8');
                $setup->getConnection()->createTable($table);

            }

            $setup->endSetup();

        } if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $tableName = $setup->getTable('fruugo_category_list');
            // Check if the table already exists
            if ($setup->getConnection()->isTableExists($tableName) != true) {
                // Create tutorial_simplenews table
                $table = $setup->getConnection()
                    ->newTable($tableName)
                    ->addColumn(
                        'id',
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'identity' => true,
                            'unsigned' => true,
                            'nullable' => false,
                            'primary' => true
                        ],
                        'ID'
                    )
                    ->addColumn(
                        'csv_cat_id',
                        Table::TYPE_TEXT,
                        null,
                        ['unsigned' => true, 'nullable' => false],
                        'Cat Id'
                    )
                    ->addColumn(
                        'name',
                        Table::TYPE_TEXT,
                        null,
                        ['unsigned' => true, 'nullable' => false],
                        'Category Name'
                    )
                    ->addColumn(
                        'path',
                        Table::TYPE_TEXT,
                        null,
                        ['unsigned' => true, 'nullable' => false],
                        'Category Path'
                    )
                    ->addColumn(
                        'csv_parent_id',
                        Table::TYPE_TEXT,
                        null,
                        ['unsigned' => true, 'nullable' => false],
                        'CSV Parent Id'
                    )
                    ->addColumn(
                        'level',
                        Table::TYPE_TEXT,
                        null,
                        ['unsigned' => true, 'nullable' => false],
                        'Level'
                    )
                    ->addColumn(
                        'fruugo_tax_code',
                        Table::TYPE_TEXT,
                        null,
                        ['unsigned' => true, 'nullable' => false],
                        'Tax Codes'
                    )
                    ->addColumn(
                        'attribute_ids',
                        Table::TYPE_TEXT,
                        null,
                        ['unsigned' => true, 'nullable' => false],
                        'Attribute Ids'
                    )
                    ->addColumn(
                        'attributes',
                        Table::TYPE_TEXT,
                        null,
                        ['unsigned' => true, 'nullable' => false],
                        'Attributes'
                    )
                    ->addColumn(
                        'created_category',
                        Table::TYPE_INTEGER,
                        null,
                        ['unsigned' => true, 'nullable' => false],
                        'Created Category'
                    )
                    ->addColumn(
                        'status',
                        Table::TYPE_TEXT,
                        null,
                        ['unsigned' => true, 'nullable' => false],
                        'Status'
                    )
                    ->setComment('Fruugo Category Table');
                $setup->getConnection()->createTable($table);
            }
        }
    }
}