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

namespace Ced\Fruugo\Cron;

class UpdateInventory
{
    /**
     * Logger
     * @var \Psr\Log\LoggerInterface
     */
    public $logger;

    /**
     * OM
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * Config Manager
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfigManager;

    /**
     * Config Manager
     * @var \Ced\Fruugo\Helper\Data
     */
    public $helper;

    /**
     * DirectoryList
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    public $directoryList;

    /**
     * @var
     */
    public $helperData;

    public $productchange;

    /**
     * UploadProducts constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Ced\Fruugo\Helper\FruugoLogger $logger,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Ced\Fruugo\Helper\Data $helperData,
        \Ced\Fruugo\Model\Productchange $productchange
    ) {
        $this->scopeConfigManager = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->objectManager = $objectManager;
        $this->helper = $this->objectManager->get('Ced\Fruugo\Helper\Data');
        $this->logger = $logger;
        $this->directoryList = $directoryList;
        $this->helperData = $helperData;
        $this->productchange = $productchange;
    }


    /**
     * Execute
     * @return bool
     */
    public function execute()
    {
        //if($this->helperData->checkForConfiguration()) {
            $scopeConfigManager = $this->objectManager
                ->create('Magento\Framework\App\Config\ScopeConfigInterface');
            $autoSync = $scopeConfigManager->getValue('fruugoconfiguration/fruugo_cron_settings/inventory_cron');
            if ($autoSync) {
                $collection = $this->productchange->getCollection();
                $type = \Ced\Fruugo\Model\Productchange::CRON_TYPE_INVENTORY;
                $collection->addFieldToFilter('cron_type', $type);
                $ids = [];
                foreach ($collection as $pchange){
                    $ids[]['id']= $pchange->getProductId();
                }
                $inventory = $this->objectManager
                    ->get('Ced\Fruugo\Helper\Data')
                    ->updateInventoryOnFruugo($ids);

                if($inventory){
                    $this->productchange->deleteFromProductChange($ids, $type);
                    $this->logger->logger("Fruugo Cron" , "Fruugo Inventory Cron" , 'Success - '.var_export($inventory),' Inventory Cron Success');
                    return true;
                }
                $this->logger->logger("Fruugo Cron" , "Fruugo Inventory Cron" , 'Failure - '.var_export($inventory,true),' Inventory Cron Failure');
                return false;

            } else {
                $this->logger->logger("Fruugo Cron" , "Fruugo Inventory Cron" , 'Disabled',' Inventory Cron Failure');
                return false;
                }
            /*}
            else{
                $this->logger->logger("Fruugo Cron" , "Fruugo Inventory Cron" , 'Not Processed','Check API details in Fruugo Configuration');
                return false;
            }*/
    }

    /**
     * CSV Data
     * @return array
     */
    public function getCsvData()
    {
        /*$csvData = $this->session->getSyncProductCSV();
        if(!empty($csvData)) {
            return $csvData;
        }*/

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
//            $this->messageManager->addErrorMessage('There has been a Issue of write permission in Directory var. Can\'t Sync Inventory');
            return false;
        }

        $csvObject = $this->objectManager->create('\Magento\Framework\File\Csv');
        try {
            $data = $csvObject->getData($extractFile);
            unset($data[0]);
            foreach ($data as $key => $value) {
                $product = $this->objectManager->create('\Magento\Catalog\Model\Product');
//                echo '<pre>';
//                print_r($value);die;
                $entityID = $product->getIdBySku($value[1]);
                //->loadByAttribute('sku',$value[1]);
                if($entityID) {
                    $csvData[] = ['id' => $entityID];
                }
            }
        } catch (\Exception $e) {
            $this->logger->debug("Fruugo Product's Inventory Cron Failed : getCsvData : " . $e->getMessage());
            return false;
        }
//        $this->session->setSyncProductCSV($csvData);
        return $csvData;
    }
}
