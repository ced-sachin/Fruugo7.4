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
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Fruugo\Helper\Product;


class Base
{
    /**
     * Current Product Object
     * @var $productOject
     */
    public $productOject;

    /**
     * All Fruugo-Magento Mapped Attributes
     * @var $attributes
     */
    public $attributes;

    /**
     * All Fruugo-Magento Mapped Configurable Attributes
     * @var $configurableAttributes
     */
    public $configurableAttributes = [];

    /**
     * Logger
     * @var \Psr\Log\LoggerInterface
     */
    public $logger;

    /**
     * Object Manager
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /*
     * @var
     */
    public $confAttributeFlag = array('size','clothingSize','assembledProductLength','assembledProductWidth','assembledProductHeight',
        'babyClothingSize','shoeSize','inseam','waistSize','neckSize','hatSize','pantySize',
        'sockSize','braSize','braBandSize','braCupSize','screenSize','resolution','ramMemory',
        'hardDriveCapacity','cableLength','digitalFileFormat','physicalMediaFormat',
        'platform','edition','shoeWidth','heelHeight','capacity','bedSize','ringSize',
        'karats','carats','chainLength','audioPowerOutput','occasion','numberOfSheets',
        'envelopeSize','focalLength','displayResolution','sportsTeam','sportsLeague','grade'
        ,'volts','amps','watts','workingLoadLimit','gallonsPerMinute','vehicleYear',
        'engineModel','vehicleMake','vehicleModel','watchBandMaterial'
    );
    /*
     * @var
     */
    public $scopeConfigManager;

    /*
     * @var
     */
    public $swatchEnabled;
    /**
     * Base constructor.
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->logger = $logger;
        $this->objectManager = $objectManager;
        $this->scopeConfigManager = $this->objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->swatchEnabled = $this->scopeConfigManager->getValue('fruugoconfiguration/productinfo_map/fruugo_product_swatches');
    }

    /**
     * Extract Magento Select Type Attribute Value
     * @param string|[] $product
     * @return string|[]
     */
    public function extractSelectValues($product = [])
    {
        foreach ($this->attributes as $value) {
            try {
                if ($value != 'blank') {
                    $attr = $this->productObject->getResource()->getAttribute($value);
                    if ($attr && ($attr->usesSource())) { // || $attr->getFrontendInput()
                        $product[$value] =
                            $attr->getSource()->getOptionText($this->productObject->getData($value));
                        if ($product[$value] == 'No') {
                            $product[$value] = 'false';
                        } elseif ($product[$value] == 'Yes') {
                            $product[$value] = 'true';
                        }
                    }
                }
            }
            catch (\Exception $e) {
                $this->logger->debug('Fruugo extractSelectValues (Base)  Helper : ' . $e->getMessage());
            }
        }
        return $product;
    }

    /**
     * Generate Array
     * @param null $name
     * @param string|[] $value
     * @return array|bool
     */
    public function generateArray($name = null, $value = null)
    {
        try {
            if ($name != null) {
                $attributeArray = explode("/", $name);
                /*if (in_array($attributeArray[0], $this->configurableAttributes)) {
                    return [];
                }*/
                if (count($attributeArray) == 1) {
                    $returnArray = [
                        $attributeArray[0] => (string)$value,
                    ];
                    return $returnArray;
                }
                if (count($attributeArray) == 2) {
                    if (is_array($value)) {
                        $returnArray[$attributeArray[0]]['_attribute'] = [];
                        foreach ($value as $key => $val) {
                            $returnArray[$attributeArray[0]]['_value'][$key][$attributeArray[1]] = (string)$val;
                        }

                    } else {
                        $returnArray = [$attributeArray[0] => [
                            $attributeArray[1] => (string)$value,
                        ]];

                    }
                    return $returnArray;

                }
                if (count($attributeArray) == 3) {
                    $returnArray = [$attributeArray[0] => [
                        $attributeArray[1] => [
                            $attributeArray[2] => (string)$value,
                        ],
                    ]];
                    return $returnArray;
                }
            }
        }
        catch (\Exception $e) {
            $this->logger->debug('Fruugo generateArray (Base) Attributes Helper : ' . $e->getMessage());
        }
        return false;
    }
}