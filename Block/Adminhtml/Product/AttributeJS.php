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

namespace Ced\Fruugo\Block\Adminhtml\Product;

class AttributeJS extends \Magento\Backend\Block\Template
{
    /**
     * Template Path
     * @var string
     */
    public $_template="product/attributejs.phtml";

    /**
     * Object Manger
     * @var  \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public $objectManager;

    /**
     * Constructor
     * @param  \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Backend\Block\Template\Context $context,
        $data = []
    ) {
        $this->objectManager = $objectManager;
        parent::__construct($context, $data);
    }

    /**
     * Magento Contructor
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('product/attributejs.phtml');
    }
}