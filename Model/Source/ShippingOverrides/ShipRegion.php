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

class ShipRegion implements \Magento\Framework\Option\ArrayInterface
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
                'label' => ' ',
                'value' => ''
            ],
            [
                'label' =>'STREET_48_STATES',
                'value' =>'STREET_48_STATES'
            ],
            [
                'label' => 'PO_BOX_48_STATES',
                'value' =>'PO_BOX_48_STATES'
            ],
            [
                'label' => 'STREET_AK_AND_HI',
                'value' =>'STREET_AK_AND_HI'
            ],
            [
                'label' => 'PO_BOX_AK_AND_HI',
                'value' =>'PO_BOX_AK_AND_HI'
            ],
            [
                'label' => 'PO_BOX_US_PROTECTORATES',
                'value' =>'PO_BOX_US_PROTECTORATES'
            ],
            [
                'label' => 'STREET_US_PROTECTORATES',
                'value' =>'STREET_US_PROTECTORATES'
            ],
            [
                'label' => 'APO_FPO',
                'value' =>'APO_FPO'
            ],
        ];

    }

}
