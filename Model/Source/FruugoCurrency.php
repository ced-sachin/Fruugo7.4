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
  * @author      CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
  * @license      http://cedcommerce.com/license-agreement.txt
  */
namespace Ced\Fruugo\Model\Source;

class FruugoCurrency extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /*public function toOptionArray()
    {
            $_options = array(
                array(
                    'label' => 'GBP',
                    'value' => 'GBP'
                ),
                 array(
                    'label' =>'EUR',
                    'value' =>'EUR'
                ),
                array(
                    'label' => 'PLN',
                    'value' =>'PLN'
                ),
                array(
                    'label' => 'DKK',
                    'value' =>'DKK'
                ),
                array(
                    'label' => 'SEK',
                    'value' =>'SEK'
                ),
                array(
                    'label' => 'NOK',
                    'value' =>'NOK'
                ),
                array(
                    'label' => 'CHF',
                    'value' =>'CHF'
                ),
                array(
                    'label' => 'RUB',
                    'value' =>'RUB'
                ),
                array(
                    'label' => 'ZAR',
                    'value' =>'ZAR'
                ),
                array(
                    'label' => 'USD',
                    'value' =>'USD'
                ),
                array(
                    'label' => 'CAD',
                    'value' =>'CAD'
                ),
                array(
                    'label' => 'AUD',
                    'value' =>'AUD'
                ),
                array(
                    'label' => 'NZD',
                    'value' =>'NZD'
                ),
                array(
                    'label' => 'CNY',
                    'value' =>'CNY'
                ),
               array(
                    'label' => 'JPY',
                    'value' =>'JPY'
                ),
                array(
                    'label' => 'INR',
                    'value' =>'INR'
                ),array(
                    'label' => 'SAR',
                    'value' =>'SAR'
                ),array(
                    'label' => 'QAR',
                    'value' =>'QAR'
                ),
                array(
                    'label' => 'BHD',
                    'value' =>'BHD'
                ),
                 array(
                    'label' => 'AED',
                    'value' =>'AED'
                ),
                  array(
                    'label' => 'EGP',
                    'value' =>'EGP'
                ),
                   array(
                    'label' => 'KWD',
                    'value' =>'KWD'
                ),
            );
        return $_options;
    }*/


    public function getAllOptions()
    {
        $_options = array(
            array(
                'label' =>'EUR',
                'value' =>'EUR'
            ),
            array(
                'label' => 'GBP',
                'value' => 'GBP'
            ),
            array(
                'label' => 'PLN',
                'value' =>'PLN'
            ),
            array(
                'label' => 'DKK',
                'value' =>'DKK'
            ),
            array(
                'label' => 'SEK',
                'value' =>'SEK'
            ),
            array(
                'label' => 'CZK',
                'value' =>'CZK'
            ),
            array(
                'label' => 'RON',
                'value' =>'RON'
            ),
            array(
                'label' => 'NOK',
                'value' =>'NOK'
            ),
            array(
                'label' => 'CHF',
                'value' =>'CHF'
            ),
            array(
                'label' => 'RUB',
                'value' =>'RUB'
            ),
            array(
                'label' => 'ZAR',
                'value' =>'ZAR'
            ),
            array(
                'label' => 'USD',
                'value' =>'USD'
            ),
            array(
                'label' => 'CAD',
                'value' =>'CAD'
            ),
            array(
                'label' => 'AUD',
                'value' =>'AUD'
            ),
            array(
                'label' => 'NZD',
                'value' =>'NZD'
            ),
            array(
                'label' => 'CNY',
                'value' =>'CNY'
            ),
            array(
                'label' => 'JPY',
                'value' =>'JPY'
            ),
            array(
                'label' => 'INR',
                'value' =>'INR'
            ),array(
                'label' => 'SAR',
                'value' =>'SAR'
            ),array(
                'label' => 'QAR',
                'value' =>'QAR'
            ),
            array(
                'label' => 'BHD',
                'value' =>'BHD'
            ),
            array(
                'label' => 'AED',
                'value' =>'AED'
            ),
            array(
                'label' => 'EGP',
                'value' =>'EGP'
            ),
            array(
                'label' => 'KWD',
                'value' =>'KWD'
            ),
            array(
                'label' => 'ILS',
                'value' =>'ILS'
            ),
            array(
                'label' => 'TRY',
                'value' =>'TRY'
            ),
            array(
                'label' => 'MYR',
                'value' =>'MYR'
            ),
            array(
                'label' => 'PHP',
                'value' =>'PHP'
            ),
            array(
                'label' => 'SGD',
                'value' =>'SGD'
            ),
            array(
                'label' => 'KRW',
                'value' =>'KRW'
            ),
        );
        return $_options;
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
