<?php
namespace Ced\Fruugo\Helper\Validator;

class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
     /**
     * Get path to merged config schema
     *
     * @return string
     */
    public function getSchema()
    {
//        return realpath(__DIR__ . '/../../etc/fruugo/prices/BulkPriceFeed.xsd');
        return realpath(__DIR__ . '/../../etc/fruugo/mp/MPItemFeed.xsd');
//        return realpath(__DIR__ . '/../../etc/fruugo/inventory/InventoryFeed.xsd');
    }

    /**
     * Get path to pre file validation schema
     *
     * @return null
     */
    public function getPerFileSchema()
    {
        return null;
    }
}
