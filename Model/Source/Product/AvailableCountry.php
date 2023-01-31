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

namespace Ced\Fruugo\Model\Source\Product;

class AvailableCountry extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @return array
     */
    public function getAllOptions()
    {
        return [
            [
                'value' => ' ',
                'label' => __('')
            ],
            [
                'value' => 'GB',
                'label' => __('United Kingdom')
            ],
            [
                'value' => 'IE',
                'label' => __('Ireland')
            ],
            [
                'value' => 'FR',
                'label' => __('France')
            ],
            [
                'value' => 'DE',
                'label' => __('Germany')
            ],
            [
                'value' => 'ES',
                'label' => __('Spain')
            ],
            [
                'value' => 'PT',
                'label' => __('Portugal')
            ],
            [
                'value' => 'BE',
                'label' => __('Belgium')
            ],
            [
                'value' => 'NL',
                'label' => __('Netherlands')
            ],
            [
                'value' => 'LU',
                'label' => __('Luxembourg')
            ],
            [
                'value' => 'PL',
                'label' => __('Poland')
            ],
            [
                'value' => 'AT',
                'label' => __('Austria')
            ],
            [
                'value' => 'IT',
                'label' => __('Italy')
            ],
            [
                'value' => 'DK',
                'label' => __('Denmark')
            ],
            [
                'value' => 'SE',
                'label' => __('Sweden')
            ],
            [
                'value' => 'FI',
                'label' => __('Finland')
            ],
            [
                'value' => 'CZ',
                'label' => __('Czechia')
            ],
            [
                'value' => 'EE',
                'label' => __('Estonia')
            ],
            [
                'value' => 'GR',
                'label' => __('Greece')
            ],
            [
                'value' => 'HU',
                'label' => __('Hungary')
            ],
            [
                'value' => 'LV',
                'label' => __('Latvia')
            ],
            [
                'value' => 'LT',
                'label' => __('Lithuania')
            ],
            [
                'value' => 'RO',
                'label' => __('Romania')
            ],
            [
                'value' => 'SK',
                'label' => __('Slovakia')
            ],
            [
                'value' => 'NO',
                'label' => __('Norway')
            ],
            [
                'value' => 'CH',
                'label' => __('Switzerland')
            ],
            [
                'value' => 'RU',
                'label' => __('Russia   ')
            ],
            [
                'value' => 'ZA',
                'label' => __('South Africa ')
            ],
            [
                'value' => 'US',
                'label' => __('United States of America ')
            ],
            [
                'value' => 'CA',
                'label' => __('Canada')
            ],
            [
                'value' => 'AU',
                'label' => __('Australia')
            ],
            [
                'value' => 'NZ',
                'label' => __('New Zealand')
            ],
            [
                'value' => 'CN',
                'label' => __('China')
            ],
            [
                'value' => 'JP',
                'label' => __('Japan')
            ],
            [
                'value' => 'IN',
                'label' => __('India')
            ],
            [
                'value' => 'SA',
                'label' => __('Saudi Arabia')
            ],
            [
                'value' => 'QA',
                'label' => __('Qatar')
            ],
            [
                'value' => 'BH',
                'label' => __('Bahrain')
            ],
            [
                'value' => 'AE',
                'label' => __('United Arab Emirates')
            ],
            [
                'value' => 'EG',
                'label' => __('Egypt')
            ],
            [
                'value' => 'KW',
                'label' => __('Kuwait')
            ],
            [
                'value' => 'IL',
                'label' => __('Israel')
            ],
            [
                'value' => 'TR',
                'label' => __('Turkey')
            ],
            [
                'value' => 'MY',
                'label' => __('Malaysia')
            ],
            [
                'value' => 'PH',
                'label' => __('Philippines')
            ],
            [
                'value' => 'SG',
                'label' => __('Singapore')
            ],
            [
                'value' => 'KR',
                'label' => __('South Korea')
            ],
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
