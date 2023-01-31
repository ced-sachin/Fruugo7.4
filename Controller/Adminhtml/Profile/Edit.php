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
namespace Ced\Fruugo\Controller\Adminhtml\Profile;
 
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
 
 
class Edit extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    protected $_entityTypeId;
    protected $_coreRegistry;
    
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    
    
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $coreRegistry,
        PageFactory $resultPageFactory
    		
    ) {

        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry     = $coreRegistry;
    }
    /**
     * Index action
     *
     * @return void
     */
    
    public function execute()
    {
        /* Replacing pcode with id for massaction in block grid */
        if(!$this->_objectManager->create('\Ced\Fruugo\Helper\Data')->checkForConfiguration()) {
            $url = $this->getUrl('adminhtml/system_config/edit/section/fruugoconfiguration');
            $this->messageManager->addNotice(__('Fruugo API not enabled or Invalid. Please check Fruugo <a target="_blank" href="'.$url.'">Configuration</a>.'));
        }

    	$profileId = $this->getRequest()->getParam('id');
    	if($profileId)
    	{
    	$profile = $this->_objectManager->create('Ced\Fruugo\Model\Profile')->load($profileId);
    	$this->getRequest()->setParam('is_profile',1);
    	$this->_coreRegistry->register('current_profile', $profile);



    	
    	if ($profile->getId() && !empty($profile)) {
    		$breadCrumb      = __('Edit Profile');
    		$breadCrumbTitle = __('Edit Profile');
    	} else {
    		$breadCrumb = __('Add New Profile');
    		$breadCrumbTitle = __('Add New Profile');
    	}
    	$item=$profile->getId() ? $profile->getProfileName() : __('New Profile');
    	$resultPage = $this->resultPageFactory->create();
    	$resultPage->getConfig()->getTitle()->prepend($profile->getId() ? $profile->getProfileName() : __('New Profile'));
    	/*$resultPage->getLayout()
    	->getBlock('profile_edit_js')
    	->setIsPopup((bool)$this->getRequest()->getParam('popup'));*/
    	return $resultPage;
    	}
    	
    	else
    	{

            $profile = $this->_objectManager->create('Ced\Fruugo\Model\Profile');
            $this->_coreRegistry->register('current_profile', $profile);


            $breadCrumb = __('Add New Profile');
    		$breadCrumbTitle = __('Add New Profile');
    		$item= __('New Profile');
    		$resultPage = $this->resultPageFactory->create();
    		$resultPage->getConfig()->getTitle()->prepend( __('New Profile'));
    		$resultPage->getLayout()
    		->getBlock('profile_edit_js')
    		->setIsPopup((bool)$this->getRequest()->getParam('popup'));
    		return $resultPage;
    	}
    }   
}