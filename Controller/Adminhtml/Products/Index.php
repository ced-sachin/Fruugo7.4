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

namespace Ced\Fruugo\Controller\Adminhtml\Products;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    /**
     * ResultPageFactory
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * REDIRECT_PATH
     * @var RedirectPath
     */
    const REDIRECT_PATH = 'fruugo/products/index';

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @throws NotFoundException
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory

    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Execute
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        if(!$this->_objectManager->create('\Ced\Fruugo\Helper\Data')->checkForConfiguration()) {
            $url = $this->getUrl('adminhtml/system_config/edit/section/fruugoconfiguration');
            $this->messageManager->addNotice(__('Fruugo API not enabled or Invalid. Please check Fruugo <a target="_blank" href="'.$url.'">Configuration</a>.'));
        }
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */

        /*$profileId = $this->getRequest()->getParam('profile_id');
        $profile = $this->_objectManager->get('Ced\Fruugo\Model\Profile')->load($profileId);


        if(!$profile->getId()){
            $this->messageManager->addError(__('Profile can not found.'));
            return $this->_redirect('fruugo/profile/index');
        }
        if($profile->getId()){
            $pcode = $this->getRequest()->getParam('pcode');
            if(!$profile->getProfileStatus()){
                $this->messageManager->addError(__('Profile is disabled please enable and try again.'));
                return $this->_redirect('fruugo/profile/edit', ['pcode' => $pcode]);
            }

            $profileproduct = $this->_objectManager->create("Ced\Fruugo\Model\Profileproducts")->getProfileProducts($profileId);
            if(count($profileproduct)==0){
                $this->messageManager->addError(__('Profile does not associated with any products. Please assign some product then try to upload.'));
                return $this->_redirect('fruugo/profile/edit', ['pcode' => $pcode]);
            }
        }*/
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_Fruugo::Fruugo');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Products'));
        return $resultPage;
    }

    /**
     * IsALLowed
     * @return boolean
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ced_Fruugo::Fruugo');
    }
}