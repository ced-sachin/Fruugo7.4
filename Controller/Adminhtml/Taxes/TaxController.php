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

namespace Ced\Fruugo\Controller\Adminhtml\Taxes;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\PageFactory;

class TaxController extends \Magento\Backend\App\Action
{
    /**
     * RPF
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @throws NotFoundException
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory

    )
    {
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
        $resultPage = $this->resultPageFactory->create();
        /*$resultPage->setActiveMenu('Ced_Fruugo::fruugo_knowledge_base');
        $resultPage->getConfig()->getTitle()->prepend(__('Fruugo Knowledge Base'));*/
        $resultPage->setActiveMenu('Ced_Fruugo::Taxes');
        $resultPage->getConfig()->getTitle()->prepend(__('Fruugo Taxes'));
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