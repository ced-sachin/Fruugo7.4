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

class ValidateProduct extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_Fruugo::fruugo_products_uploadproduct';


    /**
     * ValidateProduct constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }


    /**
     * Product Validate on Catalog Form
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $dataHelper = $this->_objectManager->get('Ced\Fruugo\Helper\Data');
        $productids[] = $this->getRequest()->getParam('id');
        if ($dataHelper->validateAllProducts($productids)) {
            $this->messageManager->addSuccessMessage(count($productids) .
                ' Product Successfully Validated for Fruugo ');
            return $this->_redirect('catalog/product/edit/id/'. $productids[0]);
        }
        $this->messageManager->addErrorMessage('Product Validation Failed for Fruugo .');
        return $this->_redirect('catalog/product/edit/id/' . $productids[0]);
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
