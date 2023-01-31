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

namespace Ced\Fruugo\Controller\Adminhtml\Products;

class Upload extends \Magento\Backend\App\Action
{

    /**
     * PageFactory
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * Filter
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    public $filter;

    /**
     * Session
     * @var \Magento\Backend\Model\Session
     */
    public $session;

    /**
     * Json Factory
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    public $resultJsonFactory;

    public $dataHelper;

    public $registry;

    /**
     * Upload constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Ced\Fruugo\Helper\Data $data
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Ced\Fruugo\Helper\Data $data
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->filter = $filter;
        $this->session =  $context->getSession();
        $this->resultJsonFactory = $resultJsonFactory;
        $this->dataHelper = $data;
        $this->registry = $registry;
    }

    /**
     * Product sync
     */
    public function execute()
    {
        if(!$this->dataHelper->checkForConfiguration()) {
            $this->messageManager->addNoticeMessage(__('Fruugo API not enabled or Invalid. Please check Fruugo Configuration.'));
            $resultRedirect = $this->resultFactory->create('redirect');
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }

        $id = $this->getRequest()->getParam('id');

        if (!empty($id)) {
            if ( $this->dataHelper->createProductOnFruugo([$id])) {
                $this->messageManager->addSuccessMessage( '1 Product Uploaded Successfully');
            } else {
                $this->messageManager->addErrorMessage('Product Upload Failed.');
            }
        }
        $resultRedirect = $this->resultFactory->create('redirect');
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;


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
