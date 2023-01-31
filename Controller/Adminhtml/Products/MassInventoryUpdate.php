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

class MassInventoryUpdate extends \Magento\Backend\App\Action
{

    const CHUNK_SIZE = 100;
    /**
     * Result Page Factory
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

    public $dataHelper;
    public $registry;
    public $resultJsonFactory;

    /**
     * MassInventory constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Ced\Fruugo\Helper\Data $dataHelper
    ) {
        ini_set('display_errors', 'true');
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->filter = $filter;
        $this->registry = $registry;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->dataHelper = $dataHelper;
        $this->session =  $context->getSession();

    }

    /**
     * Product Sync
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /*if(!$this->dataHelper->checkForConfiguration()) {
            $this->messageManager->addErrorMessage(__('Products Inventory Sync Failed . Fruugo API not enabled or Invalid. Please check Fruugo Configuration.'));*/
            //return $this->_redirect('*/*/index');
        //}
        //test code start
        $batchid = $this->getRequest()->getParam('batchid');

        if (isset($batchid) ) {

            $this->session->setAllBatchCompleted(false);
            if(empty($this->session->getResponseSession()))
                $this->session->setResponseSession([]);
            $resultJson = $this->resultJsonFactory->create();
            $productids = $this->session->getFruugoProducts();
            if(!isset($productids[$batchid+1]) || count($productids[$batchid+1]) < 1) {
                $this->session->setAllBatchCompleted(true);
                //$prod = $this->session->getResponseSession();
                //echo "<pre>";print_r($prod);die('d');
            }
            if (isset($productids[$batchid]) && $this->dataHelper->updateInventoryOnFruugo($productids[$batchid])) {
                return $resultJson->setData([
                    'success' => count($productids[$batchid]) . " Product(s) Inventory Updated Successfully",
                ]);
            }
            /*
            return $resultJson->setData([
                'error' => count($productids[$batchid]) . " Product(s) Upload Failed",
            ]);*/
        }
        //test code end

        $dataHelper = $this->_objectManager->get('Ced\Fruugo\Helper\Data');
        $collection = $this->filter->getCollection($this->_objectManager->create('Magento\Catalog\Model\Product')
            ->getCollection());
        $productids = $collection->getAllIds();
        $array = [];
        foreach ($productids as $key => $value) {
            $array[$key]['id'] = $value;
        }
        $productids = $array;
        if (is_string($productids)) {
            $productids = explode(",", $productids);
        }

        if (count($productids) == 0) {
            $this->messageManager->addErrorMessage('No Product selected for Inventory Update.');
            $this->_redirect('fruugo/products/index');
        }
        if (count($productids) < self::CHUNK_SIZE - 1)
        {
            $this->session->setNoBatches(true);
            if ($dataHelper->updateInventoryOnFruugo($productids)) {
                $this->messageManager->addSuccessMessage(count($productids) . ' Product(s) Inventory Updated Successfully');
                return $this->_redirect('fruugo/products/index');
            }
            else{
                $this->messageManager->addErrorMessage(' Product(s) Inventory Update Failed.');
                return $this->_redirect('fruugo/products/index');
            }
        }



        $productids = array_chunk($productids, self::CHUNK_SIZE);
        $this->registry->register('productids', count($productids));
        $this->session->setFruugoProducts($productids);
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_Fruugo::Fruugo');
        $resultPage->getConfig()->getTitle()->prepend(__('Update Inventory'));
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
