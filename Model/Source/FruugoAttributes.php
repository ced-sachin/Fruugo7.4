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

class FruugoAttributes implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {
        $fruugoAttributes = [
            [
                'value' => 'EAN',
                'label' => __('EAN')
            ],
            [
                'value' => 'Brand',
                'label' => __('Brand')
            ],
            [
                'value' => 'StockQuantity',
                'label' => __('StockQuantity')
            ],
            [
                'value' => 'Title',
                'label' => __('Title')
            ],
            [
                'value' => 'Description',
                'label' => __('Description')
            ],
            [
                'value' => 'NormalPriceWithoutVAT',
                'label' => __('NormalPriceWithoutVAT')
            ],
            [
                'value' => 'NormalPriceWithVAT',
                'label' => __('NormalPriceWithVAT')
            ],
            [
                'value' => 'VATRate',
                'label' => __('VATRate')
            ],
            [
                'value' => 'DiscountPriceWithoutVAT',
                'label' => __('DiscountPriceWithoutVAT')
            ],
            [
                'value' => 'DiscountPriceWithVAT',
                'label' => __('DiscountPriceWithVAT')
            ],
            [
                'value' => 'ISBN',
                'label' => __('ISBN')
            ],
            [
                'value' => 'Manufacturer',
                'label' => __('Manufacturer')
            ],
            [
                'value' => 'RestockDate',
                'label' => __('RestockDate')
            ],
            [
                'value' => 'LeadTime',
                'label' => __('LeadTime')
            ],
            [
                'value' => 'PackageWeight',
                'label' => __('PackageWeight')
            ],
            [
                'value' => 'Country',
                'label' => __('Country')
            ],
            [
                'value' => 'DiscountPriceStartDate',
                'label' => __('DiscountPriceStartDate')
            ],
            [
                'value' => 'DiscountPriceEndDate',
                'label' => __('DiscountPriceEndDate')
            ]
        ];
        return $fruugoAttributes;
    }

}
