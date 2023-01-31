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

namespace Ced\Fruugo\Block\Adminhtml\Category\Grid;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class DeleteButton
 * @package Magento\Customer\Block\Adminhtml\Edit
 */
class ResetButton extends \Magento\Backend\Block\Widget\Container implements ButtonProviderInterface
{
    /**
     * Button to Render
     * @return string|[]
     */
    public function getButtonData()
    {
        $resetConfirmMsg = 'Are You sure To Reset the Category Mappings?';
        $data = [
            'label' => __('Reset Mapping'),
            'class' => 'action-primary',
            'id' => 'delete',                
            'on_click' => 'deleteConfirm(\'' . $resetConfirmMsg . '\', \'' . $this->getUrl('*/categories/reset') .
                '\')',
            'sort_order' => 0,
        ];
       
        return $data;
    }
}
