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

class ProductStatus extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @return array
     */
    public function getAllOptions()
    {

        return [
            [
                'value' => 'UNPUBLISHED',
                'label' => __('UNPUBLISHED')
            ],
            [
                'value' => 'STAGE',
                'label' => __('STAGE')
            ],
            [
                'value' => 'transmit',
                'label' => __('Transmit')
            ],
            [
                'value' => 'data_fix',
                'label' => __('Data Fix')
            ],
            [
                'value' => 'ingestion_in_progress',
                'label' => __('Ingestion in Progress')
            ],
            [
                'value' => 'publish_in_progress',
                'label' => __('Publish in Progress')
            ],
            [
                'value' => 'PUBLISHED',
                'label' => __('PUBLISHED')
            ],
            [
                'value' => 'system_error',
                'label' => __('System Error')
            ],
            [
                'value' => 'data_fix_ticket',
                'label' => __('Data Fix - Ticket')
            ],
            [
                'value' => 'system_error_ticket',
                'label' => __('System Error - Ticket')
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
