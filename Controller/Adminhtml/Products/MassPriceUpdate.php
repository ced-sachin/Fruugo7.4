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

class MassPriceUpdate extends \Magento\Backend\App\Action
{
    /**
     * Result Page Factory
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * Authorization Level of a Basic Admin Session
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_Fruugo::fruugo_products_index';

    public $filter;
    
    public $dataHelper;

    /**
     * MassPrice constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Ced\Fruugo\Helper\Data $dataHelper
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->filter = $filter;
        $this->dataHelper = $dataHelper;
    }

    /**
     * Product Sync
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        if(!$this->dataHelper->checkForConfiguration()) {
            $this->messageManager->addErrorMessage(__('Products Price Sync Failed . Fruugo API not enabled or Invalid. Please check Fruugo Configuration.'));
            return $this->_redirect('*/*/index');
        }
        $dataHelper = $this->_objectManager->get('Ced\Fruugo\Helper\Data');
        $collection = $this->filter->getCollection($this->_objectManager->create('Magento\Catalog\Model\Product')
            ->getCollection());
        $productids = $collection->getAllIds();
        if (is_string($productids)) {
            $productids = explode(",", $productids);
        }

        if (count($productids) == 0) {
            $this->messageManager->addErrorMessage('No Product selected for Price Update.');
            $this->_redirect('fruugo/products/index');
        }

        if ($dataHelper->updatePriceOnFruugo($productids)) {
            $this->messageManager->addSuccessMessage(count($productids) . ' Product(s) Price Updated Successfully');
            return $this->_redirect('fruugo/products/index');
        }
        $this->messageManager->addErrorMessage('Product(s) Price Update Failed.');
        return $this->_redirect('fruugo/products/index');


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
