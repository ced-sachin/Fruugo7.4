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
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */


namespace Ced\Fruugo\Model\Source;

class ProductIdType extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * Options getter
     *
     * @return array
     */
    public function getAllOptions()
    {
        return[
            [
                'label' => 'UPC',
                'value' => 'UPC'
            ],
            [
                'label' =>'GTIN',
                'value' =>'GTIN'
            ]
        ];
    }
}
