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

namespace Ced\Fruugo\Block\Adminhtml\Confattributes\Map;

class Mapattr extends \Magento\Backend\Block\Widget implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{

    /**
     * @var string
     */
    public $_template = 'Ced_Fruugo::confattribute/mappattr.phtml';

    /**
     * Object Manager
     * @var objectManager
     */
    public $objectManager;

    /**
     * Mapattr constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objetManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface  $objetManager,
        $data = []
    ) {
        parent::__construct($context, $data);
        $this->objectManager = $objetManager;
    }

    /**
     * Render form element as HTML
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    /**
     * Get Product Attributes
     * @return string|[]
     */
    public function getProductAttribute()
    {

        $attributes = $this->objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection')
            ->addFieldToFilter('frontend_input', ['in' => ['select', 'multiselect']])
            ->getItems();
        $attributesArrays = [];
        foreach ($attributes as $attribute) {
            if ($attribute->getIsVisible()) {
                $attributesArrays[] = [
                    'label' => $attribute->getFrontendLabel(),
                    'code' => $attribute->getAttributecode(),
                    'frontend_input' => $attribute->getFrontendInput()
                ];
            }
        }
        return $attributesArrays;
    }

}
