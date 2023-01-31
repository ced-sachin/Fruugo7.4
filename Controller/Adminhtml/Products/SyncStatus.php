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

class SyncStatus extends \Magento\Backend\App\Action
{
    const CHUNK_SIZE = 30;
    /**
     * Result Page factory
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * Logger
     * @var $logger \Psr\Log\LoggerInterface
     */
    public $logger;

    /**
     * Data Helper
     * @var $helper
     */
    public $helper;

    /**
     * Product Factory
     * @var $productFactory
     */
    public $productFactory;

    /**
     * DirectoryList
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    public $directoryList;

    /**
     * Session
     * @var \Magento\Backend\Model\Session
     */
    public $session;

    /**
     * Filter
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    public $filter;

    public $registry;


    /**
     * SyncStatus constructor.
     * @param Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param  \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Ced\Fruugo\Helper\Data $helper
     */
    public function __construct(
        Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Ced\Fruugo\Helper\Data $helper,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->session =  $context->getSession();
        $this->productFactory = $productFactory;
        $this->directoryList = $directoryList;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->filter = $filter;
        $this->registry = $registry;
    }

    /**
     * Execute
     * @return array
     */
    public function execute()
    {
        if(!$this->helper->checkForConfiguration()) {
            $this->messageManager->addNoticeMessage(__('Fruugo API not enabled or Invalid. Please check Fruugo Configuration.'));
            return $this->_redirect('*/*/index');
        }
        $csvData = $this->getCsvData();
       /* if ($productSyncStatus) {
            $this->messageManager->addSuccessMessage('All Products\' Status Synced Successfully');
        } else {
            $this->messageManager->addErrorMessage('Fruugo Product\' Status Sync Failed');
        }*/
//        $this->_redirect('fruugo/products/index');

        $batchid = $this->getRequest()->getParam('batchid');
        if (isset($batchid)) {
            $resultJson = $this->resultJsonFactory->create();
            $productids = $this->session->getFruugoProducts();
            if (isset($productids[$batchid]) ){
                foreach ($productids[$batchid] as $id) {
                    $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($id);
                    if(isset($csvData[$product->getSku()])) {
                        $product->setFruugoProductStatus($csvData[$product->getSku()])
                            ->getResource()->saveAttribute($product,'fruugo_product_status');
                    }
                }
                return $resultJson->setData([
                    'success' => count($productids[$batchid]) . " Product(s) Status Synced Successfully",
                ]);
            }
            return $resultJson->setData([
                'error' => count($productids[$batchid]) . " Product(s) Status Sync Failed",
            ]);
        }

        $this->dataHelper = $this->_objectManager->get('Ced\Fruugo\Helper\Data');
        $collection = $this->filter->getCollection($this->_objectManager->create('Magento\Catalog\Model\Product')
            ->getCollection());
        $productids = $collection->getAllIds();

        if (count($productids) == 0) {
            $this->messageManager->addErrorMessage('No Product selected to sync.');
            return $this->_redirect('fruugo/products/index');
        }

        if (count($productids) < self::CHUNK_SIZE - 1) {
            foreach ($productids as $id) {
                $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($id);
                /*if(isset($csvData[$product->getSku()])) {
                    $product->setFruugoProductStatus($csvData[$product->getSku()])
                        ->getResource()->saveAttribute($product,'fruugo_product_status');
                }*/
                $sku = $product->getSku();
                if(isset($csvData[$sku])) {
                    $product->setFruugoProductStatus($csvData[$sku])
                        ->getResource()->saveAttribute($product,'fruugo_product_status');
                }
            }
            $this->messageManager->addSuccessMessage(count($productids) . ' Product(s) Synced Successfully');
            /*if ( $this->dataHelper->createProductOnFruugo($productids)) {

            } else {
                $this->messageManager->addErrorMessage('Product(s) Sync Failed.');
            }*/
            return $this->_redirect('fruugo/products/index');
        }

        $productids = array_chunk($productids, self::CHUNK_SIZE);
        $this->registry->register('productids', count($productids));
        $this->session->setFruugoProducts($productids);
        $resultPage = $this->resultPageFactory-> create();
        $resultPage->setActiveMenu('Ced_Fruugo::Fruugo');
        $resultPage->getConfig()->getTitle()->prepend(__('Sync Products'));
        return $resultPage;

    }

    /**
     * Feeds Sync
     * @return bool
     */
    public function getCsvData()
    {
        /*try {
            $categoryIds = $this->helper->getMappedCategories();
            $categoryIds = $categoryIds->getData();
            $categoryFactory = $this->_objectManager->create('\Magento\Catalog\Model\CategoryFactory');
            foreach ($categoryIds as $categoryId) {
                $category = $categoryFactory->create()->load($categoryId['entity_id']);
                $productCollection = $category->getProductCollection()->addAttributeToSelect(
                    ['entity_id'])->load();
                foreach ($productCollection as $product) {
                    if ($product->getTypeId() == 'configurable') {
                        $productType = $product->getTypeInstance();
                        $products = $productType->getUsedProducts($product);
                        foreach ($products as $value) {
                            $sku = $value->getSku();
                            $fruugoProduct = $this->helper->getItem($sku, true);
                            if ($fruugoProduct) {
                                $productObject = $this->productFactory->create()->load($value->getId());
                                $productObject->setData('fruugo_product_status', $fruugoProduct);
                                $productObject->save();
                            }
                        }
                    } elseif ($product->getTypeId() == 'simple') {
                        $sku = $product->getSku();
                        $fruugoProduct = $this->helper->getItem($sku, true);
                        if ($fruugoProduct) {
                            $productObject = $this->productFactory->create()->load($product->getId());
                            $productObject->setData('fruugo_product_status', $fruugoProduct);
                            $productObject->save();
                        }
                    }
                }
            }
            return true;
        }
        catch (\Exception $e) {
            $this->logger->debug("Fruugo Product's Status Sync Failed : syncAllProducts : " . $e->getMessage());
            return false;
        }*/


        $csvData = $this->session->getSyncProductCSV();
        if(!empty($csvData)) {
            return $csvData;
        }
        $csvData = array();
        $walmpro = $this->helper->getRequest('v2/getReport?type=item');
        $start = stripos($walmpro, 'ItemReport');
        $end = strpos($walmpro, 'Content-Type: text/html;charset=utf-8');

        $filename = trim(substr($walmpro, $start,$end - $start));

        $filepath = $this->directoryList->getPath('var').'/fruugo/ItemReport.zip';
        $extractTo = $this->directoryList->getPath('var').'/fruugo/';
        $this->helper->createDir();
        $file = explode('.csv',$filename);
        $filename = $file[0];
        $extractFile = $extractTo.$filename.'.csv';

        $handle = fopen($filepath,'w');
        fwrite($handle, $walmpro);
        fclose($handle);

        $zip = new \ZipArchive();
        if ($zip->open($filepath) === TRUE) {
            $zip->extractTo($extractTo);
            $zip->close();
        } else {
            $this->logger->debug('Zip Extraction Failed');
            $this->messageManager->addErrorMessage('There has been a Issue of write permission in Directory var.');
            return false;
        }

        $csvObject = $this->_objectManager->create('\Magento\Framework\File\Csv');
        try {
            $data = $csvObject->getData($extractFile);
            unset($data[0]);
            foreach ($data as $key => $value) {
                $csvData[$value[1]] = $value[6];
            }

        } catch (\Exception $e) {
            $this->logger->debug("Fruugo Product's Status Sync Failed : syncAllProducts : " . $e->getMessage());
            return false;
        }
        $this->session->setSyncProductCSV($csvData);
        return $csvData;
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