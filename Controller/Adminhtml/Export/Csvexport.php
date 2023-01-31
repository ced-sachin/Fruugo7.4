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
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Fruugo\Controller\Adminhtml\Export;

use Magento\Framework\App\Filesystem\DirectoryList;

class Csvexport extends \Magento\Backend\App\Action
{


    /** @var \Magento\Framework\Stdlib\DateTime\DateTime $date */
    public $date;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Ced\Fruugo\Model\Profileproducts $locationFactory,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Framework\File\Csv $csvParser
    ) {
        parent::__construct($context);
        $this->fileDriver = $fileDriver;
        $this->date = $date;
        $this->csvParser = $csvParser;
        $this->_fileFactory = $fileFactory;
        $this->_locationFactory = $locationFactory;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR); // VAR Directory Path
    }

    public function execute()
    {

        $name = 'fruugo_product_errors';
        $filepath = 'export/custom' . $name . '.csv';
        $filterArray = [
            'start_date' => $this->prepareDate(1, 'second'),
            'end_date' => $this->prepareDate(1, 'day')
        ];
        // $this->productFeedHelper->updateErrorsCsvFromFeed($filterArray);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $objectManager->get('Ced\Fruugo\Ui\DataProvider\Products\DataProvider')->getData();
        //echo '<pre>'; print_r($product['items']);die('ppppj');
        
        foreach ($product['items'] as $key) {

            $product = $objectManager->create('Magento\Catalog\Model\Product')->load($key['entity_id']);
            $product_error = json_decode($product->getData('fruugo_validation_errors'), true);
            if (isset($product_error['0']['errors']))
                $arr[] = ['entity_id' => $key['entity_id'], 'sku' => $key['sku'], 'error' => json_encode($product_error['0']['errors'])];
        }
if(!isset($arr)){
    $arr[] = ['entity_id' => '1', 'sku' => 'SAMPLE', 'error' => 'SAMPLE'];

}
        
        $newErrors = array_values($arr);

      
        $this->directory->create('export');

        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();

        //column name dispay in your CSV

        $columns = [
            'entity_id',
            'sku',
            'error'
        ];

        foreach ($columns as $column) {
            $header[] = $column; //storecolumn in Header array
        }

        $stream->writeCsv($header);

        foreach ($newErrors as $newError) {
            $stream->writeCsv($newError);
        }

        $content = [];
        $content['type'] = 'filename'; // must keep filename
        $content['value'] = $filepath;
        $content['rm'] = '0'; //remove csv from var folder

        $csvfilename = $name . '.csv';
        return $this->_fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);
    }


    public function prepareDate($numberOfWeek, $intervalType = 'day')
    {
        $date = ($numberOfWeek && $intervalType)
            ? $this->date->gmtDate('Y-m-d\TH:i:s\Z', strtotime("-{$numberOfWeek} {$intervalType}"))
            : false;
        return $date;
    }


    /**
     * Retrieve data from file
     *
     * @return string[]
     */
    public function getFileData($file)
    {
        $data = [];
        if ($this->fileDriver->isExists($file)) {
            $this->csvParser->setDelimiter(',');
            $data = $this->csvParser->getData($file);
        }
        return $data;
    }
}
