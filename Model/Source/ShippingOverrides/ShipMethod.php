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

namespace Ced\Fruugo\Model\Source\ShippingOverrides;

class ShipMethod implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => '',
                'value' => ''
            ],
            [
                'label' =>'STANDARD',
                'value' =>'STANDARD'
            ],
            [
                'label' => 'EXPEDITED',
                'value' =>'EXPEDITED'
            ],
            [
                'label' => 'FREIGHT',
                'value' =>'FREIGHT'
            ],
            [
                'label' => 'ONE_DAY',
                'value' =>'ONE_DAY'
            ],
            [
                'label' => 'FREIGHT_WITH_WHITE_GLOVE',
                'value' =>'FREIGHT_WITH_WHITE_GLOVE'
            ],
            [
                'label' => 'VALUE (Free Shipping)',
                'value' =>'VALUE'
            ]
        ];

    }

}
