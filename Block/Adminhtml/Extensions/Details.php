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

namespace Ced\Fruugo\Block\Adminhtml\Extensions;

class Details extends \Magento\Backend\Block\Widget\Container
{
    protected $_objectManager;
    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectInterface,
        array $data = []
    ) {
        $this->_objectManager = $objectInterface;
        parent::__construct($context, $data);
    }

    public function getModules(){
        $modules = $this->_objectManager->get('\Ced\Fruugo\Helper\Feed')->getCedCommerceExtensions();
        $helper = $this->_objectManager->get('\Ced\Fruugo\Helper\Data');
        $params = array();
        $args = '';
        foreach ($modules as $moduleName=>$releaseVersion)
        {
            $m = strtolower($moduleName); if(!preg_match('/ced/i',$m)){ return $this; }  $h = $helper->getStoreConfig(\Ced\Fruugo\Block\Extensions::HASH_PATH_PREFIX.$m.'_hash'); for($i=1;$i<=(int)$helper->getStoreConfig(\Ced\Fruugo\Block\Extensions::HASH_PATH_PREFIX.$m.'_level');$i++){$h = base64_decode($h);}$h = json_decode($h,true);
            if(is_array($h) && isset($h['domain']) && isset($h['module_name']) && isset($h['license']) && strtolower($h['module_name']) == $m && $h['license'] == $helper->getStoreConfig(\Ced\Fruugo\Block\Extensions::HASH_PATH_PREFIX.$m)){}else{
                $args .= $m.',';
            }   
        }
       
        $args = trim($args,',');
        return $args;
       
    }

}