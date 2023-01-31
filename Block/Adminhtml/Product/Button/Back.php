<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ced\Fruugo\Block\Adminhtml\Product\Button;

/**
 * Class Back
 */
class Back extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Generic
{

 protected  $_request;


    public function __construct(
        \Magento\Framework\View\Element\UiComponent\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->context = $context;
        $this->registry = $registry;
        $this->_request = $request;
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        $pId = $this->_request->getParam('profile_id');
        $pcode = $this->_request->getParam('pcode');
        if($pcode && $pId)
        {
            return [
                'label' => __('Back'),
                'on_click' => sprintf("location.href = '%s';", $this->getUrl('fruugo/profile/edit', ['id'=> $pId,'pcode' => $pcode])),
                'class' => 'back',
                'sort_order' => 10
            ];
        }
        else{
            return [
                'label' => __('Back'),
                'on_click' => sprintf("location.href = '%s';", $this->getUrl('fruugo/profile/index')),
                'class' => 'back',
                'sort_order' => 10
            ];
        }

    }
}
