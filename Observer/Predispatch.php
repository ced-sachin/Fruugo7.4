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

namespace Ced\Fruugo\Observer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
class Predispatch  implements ObserverInterface
{
    protected $_feed;
    protected $_backendAuthSession;
    protected $_objectManager;
    
    public function __construct(
        \Ced\Fruugo\Model\Feed $_feed,
        \Magento\Framework\ObjectManagerInterface $objectInterface,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Ced\Fruugo\Helper\Data $helper,
        \Magento\Framework\UrlInterface $urlRedirect,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->_url = $urlRedirect;
        $this->_request = $request;
        $this->_feed = $_feed;
        $this->_backendAuthSession = $backendAuthSession;
        $this->_objectManager = $objectInterface;
        $this->dataHelper = $helper;
    }

    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if($this->_request->getModuleName() != 'fruugo') {
            return $this;
        }
        if ($this->_backendAuthSession->isLoggedIn()) {
            $this->_feed->checkUpdate();
            $licenceState = $this->dataHelper->checkForLicence();
            if(!$licenceState){
                $RedirectUrl= $this->_url->getUrl('adminhtml/system_config/edit/section/cedcore');
                $observer->getControllerAction()
                    ->getResponse()
                    ->setRedirect($RedirectUrl);
            }
        }
        return $this;
    }
}
