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

class FruugoLanguage extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /*public function toOptionArray()
    {
            $_options = array(
                array(
                    'label' => 'en',
                    'value' => 'en'
                ),
                 array(
                    'label' =>'fr',
                    'value' =>'fr'
                ),
                array(
                    'label' => 'de',
                    'value' =>'de'
                ),
                array(
                    'label' => 'es',
                    'value' =>'es'
                ),
                array(
                    'label' => 'pt',
                    'value' =>'pt'
                ),
                array(
                    'label' => 'de',
                    'value' =>'de'
                ),
                array(
                    'label' => 'nl',
                    'value' =>'nl'
                ),
                array(
                    'label' => 'pl',
                    'value' =>'pl'
                ),
                array(
                    'label' => 'it',
                    'value' =>'it'
                ),
                array(
                    'label' => 'da',
                    'value' =>'da'
                ),
                array(
                    'label' => 'sv',
                    'value' =>'sv'
                ),
                array(
                    'label' => 'fi',
                    'value' =>'fi'
                ),
                array(
                    'label' => 'sv',
                    'value' =>'sv'
                ),
                array(
                    'label' => 'no',
                    'value' =>'no'
                ),
               array(
                    'label' => 'ru',
                    'value' =>'ru'
                ),
                array(
                    'label' => 'zh',
                    'value' =>'zh'
                ),array(
                    'label' => 'jp',
                    'value' =>'jp'
                ),array(
                    'label' => 'hi',
                    'value' =>'hi'
                ),
                array(
                    'label' => 'ar',
                    'value' =>'ar'
                ),
            );
        return $_options;
    }*/


    public function getAllOptions()
    {
        $_options = array(
            array(
                'label' => 'en',
                'value' => 'en'
            ),
            array(
                'label' =>'fr',
                'value' =>'fr'
            ),
            array(
                'label' => 'de',
                'value' =>'de'
            ),
            array(
                'label' => 'es',
                'value' =>'es'
            ),
            array(
                'label' => 'pt',
                'value' =>'pt'
            ),
            array(
                'label' => 'de',
                'value' =>'de'
            ),
            array(
                'label' => 'nl',
                'value' =>'nl'
            ),
            array(
                'label' => 'pl',
                'value' =>'pl'
            ),
            array(
                'label' => 'it',
                'value' =>'it'
            ),
            array(
                'label' => 'da',
                'value' =>'da'
            ),
            array(
                'label' => 'sv',
                'value' =>'sv'
            ),
            array(
                'label' => 'fi',
                'value' =>'fi'
            ),
            array(
                'label' => 'cs',
                'value' =>'cs'
            ),
            array(
                'label' => 'el',
                'value' =>'el'
            ),
            array(
                'label' => 'hu',
                'value' =>'hu'
            ),
            array(
                'label' => 'sv',
                'value' =>'sv'
            ),
            array(
                'label' => 'no',
                'value' =>'no'
            ),
            array(
                'label' => 'ru',
                'value' =>'ru'
            ),
            array(
                'label' => 'zh',
                'value' =>'zh'
            ),array(
                'label' => 'jp',
                'value' =>'jp'
            ),array(
                'label' => 'hi',
                'value' =>'hi'
            ),
            array(
                'label' => 'ar',
                'value' =>'ar'
            ),
            array(
                'label' => 'he',
                'value' =>'he'
            ),
            array(
                'label' => 'tr',
                'value' =>'tr'
            ),
            array(
                'label' => 'ko',
                'value' =>'ko'
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
