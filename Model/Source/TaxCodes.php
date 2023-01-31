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
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Fruugo\Model\Source;

class TaxCodes extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @return array
     */
    public function getAllOptions()
    {

        return [
            [
                'value' => 'Animal',
                'label' => __('Animal')
            ],
            [
                'value' => 'ArtAndCraft',
                'label' => __('Art And Craft')
            ],
            [
                'value' => 'Baby',
                'label' => __('Baby')
            ],
            [
                'value' => 'CarriersAndAccessories',
                'label' => __('Carriers And Accessories')
            ],
            [
                'value' => 'Clothing',
                'label' => __('Clothing')
            ],
            [
                'value' => 'Electronics',
                'label' => __('Electronics')
            ],
            [
                'value' => 'FoodAndBeverage',
                'label' => __('Food And Beverage')
            ],
            [
                'value' => 'Footwear',
                'label' => __('Footwear')
            ],
            [
                'value' => 'Furniture',
                'label' => __('Furniture')
            ],
            [
                'value' => 'GardenAndPatio',
                'label' => __('Garden And Patio')
            ],
            [
                'value' => 'HealthAndBeauty',
                'label' => __('Health And Beauty')
            ],
            [
                'value' => 'Home',
                'label' => __('Home')
            ],
            [
                'value' => 'Jewelry',
                'label' => __('Jewelry')
            ],
            [
                'value' => 'Media',
                'label' => __('Media')
            ],
            [
                'value' => 'MusicalInstrument',
                'label' => __('Musical Instrument')
            ],
            [
                'value' => 'OccasionAndSeasonal',
                'label' => __('Occasion And Seasonal')
            ],
            [
                'value' => 'Office',
                'label' => __('Office')
            ],
            [
                'value' => 'Other',
                'label' => __('Other')
            ],
            [
                'value' => 'Photography',
                'label' => __('Photography')
            ],
            [
                'value' => 'SportAndRecreation',
                'label' => __('Sport And Recreation')
            ],
            [
                'value' => 'ToolsAndHardware',
                'label' => __('Tools And Hardware')
            ],
            [
                'value' => 'Toy',
                'label' => __('Toy')
            ],
            [
                'value' => 'Vehicle',
                'label' => __('Vehicle')
            ],
            [
                'value' => 'Watches',
                'label' => __('Watches')
            ]
        ];
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $options = [];
        foreach ($this->getAllOptions() as $option) {
            $options[$option['value']] = (string)$option['label'];
        }
        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {

        return $this->getOptions();
    }

    /**
     * Get fruugo product status labels array with empty value
     *
     * @return array
     */
    public function getAllOption()
    {
        $options = $this->getOptionArray();
        array_unshift($options, ['value' => '', 'label' => '']);
        return $options;
    }

    /**
     * Get fruugo product status labels array for option element
     *
     * @return array
     */
    public function getOptions()
    {
        $res = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        return $res;
    }

    /**
     * Get fruugo product status
     *
     * @param string $optionId
     * @return null|string
     */
    public function getOptionText($optionId)
    {
        $options = $this->getOptionArray();
        return isset($options[$optionId]) ? $options[$optionId] : null;
    }

}
