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
use Magento\Framework\View\Result\PageFactory;

class MassValidate extends \Magento\Backend\App\Action
{
    const CHUNK_SIZE = 1;

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
     * MassValidate constructor.
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
        \Ced\Fruugo\Helper\Data $data,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->filter = $filter;
        $this->session =  $context->getSession();
        $this->resultJsonFactory = $resultJsonFactory;
        $this->dataHelper = $data;
        $this->registry = $registry;
        $this->redirectFactory = $redirectFactory;
    }

    /**
     * Product sync
     */
    public function execute()
    {

        /**
         * Import product one by one for Validate Mass Action
         */
        if(!$this->dataHelper->checkForConfiguration()) {
            $this->messageManager->addErrorMessage(__('Products Upload Failed. Fruugo API not enabled or Invalid. Please check Fruugo Configuration.'));
            $redirect = $this->redirectFactory->create();
            return $redirect->setPath(\Ced\Fruugo\Controller\Adminhtml\Products\Index::REDIRECT_PATH);
            /*$resultRedirect = $this->resultFactory->create('redirect');
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;*/
        }


        $batchid = $this->getRequest()->getParam('batchid');

        if (isset($batchid)) {
            $resultJson = $this->resultJsonFactory->create();
            $productids = $this->session->getFruugoProducts();
            $errorMsg = Null;
            if (isset($productids[$batchid]) && $resultData = $this->dataHelper->validateAllProducts($productids[$batchid],false)) {
                if(isset($resultData['errors'])) {

                    foreach ($resultData['errors'] as $sku => $data){
                        $errorMsg .= "<br><ul class='all-validation-errors'><li style='list-style: none'><b>SKU: '".$sku."'</b> - </li>";

                        if(isset($data[0])) {

                            $errorMsg .="<ul class='sub-products'>";
                            foreach ($data as $sku => $subError) {
                                try{
                                    $subErrors = isset($subError['sku']) ? $subError['sku']: $subError;
                                       $errorMsg .=" <b>SKU : ".$subErrors." </b>";
                                       if(is_array($subError['errors'])) {
                                           foreach ($subError['errors'] as $attribute => $error) {
                                               $errorMsg .="<li>".$attribute." - ".$error."</li>";
                                           }
   
                                       }
                                   }
                               catch(\Exception $e){
                                  // print_r($subError);die(__FILE__);
                               }
                            }
                            $errorMsg .="</ul>";

                        } else {
                            try{
                                foreach ($data['errors'] as $key =>  $error) {
                                    $errorMsg .= "<li>".$key.' - '.$error."</li>";
                                }
                            } catch (\Exception $e) {
//                                print_r($resultData);die('check');
                            }

                        }
                        $errorMsg .="</ul>";
                    }
                }

                if(isset($errorMsg) && $errorMsg)
                {
                    return $resultJson->setData([
                        'error' => $errorMsg/*count($productids[$batchid]) . " Product(s) Validation Failed"*/,
                    ]);
                }

                return $resultJson->setData([
                    'success' => count($productids[$batchid]) . " Product(s) Validated successfully",
                ]);
            }




        }

        $this->dataHelper = $this->_objectManager->get('Ced\Fruugo\Helper\Data');
        $collection = $this->filter->getCollection($this->_objectManager->create('Magento\Catalog\Model\Product')
            ->getCollection());

        $productids = $collection->getAllIds();

        if (count($productids) == 0) {
            $this->messageManager->addErrorMessage('No Product selected to validate.');
            $redirect = $this->redirectFactory->create();
            return $redirect->setPath(\Ced\Fruugo\Controller\Adminhtml\Products\Index::REDIRECT_PATH);

        }
        if (count($productids) < self::CHUNK_SIZE - 1) {
            if ( $this->dataHelper->validateAllProducts($productids,false)) {
                $this->messageManager->addSuccessMessage(count($productids) . ' Products Validated Successfully');
            } else {
                $this->messageManager->addErrorMessage('Products Validation Failed.');
            }
            //return $this->_redirect('fruugo/products/index');
            $redirect = $this->redirectFactory->create();
            return $redirect->setPath(\Ced\Fruugo\Controller\Adminhtml\Products\Index::REDIRECT_PATH);

        }

        $productids = array_chunk($productids, self::CHUNK_SIZE);
        $this->registry->register('productids', count($productids));
        $this->session->setFruugoProducts($productids);
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_Fruugo::Fruugo');
        $resultPage->getConfig()->getTitle()->prepend(__('Validate Products'));
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