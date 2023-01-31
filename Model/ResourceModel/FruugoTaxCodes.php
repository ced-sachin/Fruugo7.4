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


namespace Ced\Fruugo\Model\ResourceModel;

class FruugoTaxCodes extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Construct
     * @return void
     */
    public function _construct()
    {
        $this->_init('fruugo_tax_codes', 'id');
    }


}

