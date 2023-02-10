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

namespace Ced\Fruugo\Helper;
use function GuzzleHttp\is_host_in_noproxy;
use \Magento\Framework\Message\Manager;
/**
 * Class Data For Fruugo Authenticated Seller Api
 * @package Ced\Fruugo\Helper
 */
ini_set('display_errors',true);
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const GET_ORDERS_SUB_URL = 'orders/download/v2?from=';
    const CONFIRM_ORDERS_SUB_URL = 'orders/confirm';
    const CANCEL_ORDERS_SUB_URL = 'orders/cancel';
    const SHIP_ORDERS_SUB_URL = 'orders/ship';
    const GET_ORDERS_RELEASED_SUB_URL = 'v3/orders/released';
    const GET_ITEMS_SUB_URL = 'v2/items';
    const GET_FEEDS_SUB_URL = 'v2/feeds';
    const GET_FEED_SUB_URL = 'v2/feeds/feeditems/';
    const GET_FEEDS_ITEMS_SUB_URL = 'v2/feeds?feedType=item';
    const GET_FEEDS_INVENTORY_SUB_URL = 'stockstatus-api';
    const GET_FEEDS_PRICE_SUB_URL = 'v2/feeds?feedType=price';
    const GET_INVENTORY_SUB_URL = 'v2/inventory';
    const GET_REPORTS_SUB_URL = 'v2/getReport';
    const UPDATE_PRICE_SUB_URL = 'v2/prices';
    const CONSUMER_CHANEL_TYPE_ID = '7b2c8dab-c79c-4cee-97fb-0ac399e17ade';

    /**
     * Curl Object
     * @var \Magento\Framework\HTTP\Adapter\Curl
     */
    public $resource;

    /**
     * Object Manager
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * Config Manager
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfigManager;

    /**
     * Json Parser
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $json;

    /**
     * Xml Parser
     * @var \Magento\Framework\Convert\Xml
     */
    public $xml;

    /**
     * DirectoryList
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    public $directoryList;

    /**
     * Date/Time
     * @var $dateTime
     */
    public $dateTime;

    /**
     * File Manager
     * @var $fileIo
     */
    public $fileIo;

    /**
     * Api Base Url
     * @var string $apiUrl
     */
    public $apiUrl;

    /**
     * Api Consumer Id
     * @var string $apiConsumerId
     */
    public $apiConsumerId;

    /**
     * Api Consumer Channel Id
     * @var string $apiConsumerChannelId
     */
    public $apiConsumerChannelId;

    /**
     * Api Private Key
     * @var string $apiPrivateKey
     */
    public $apiPrivateKey;

    /**
     * Api Signature Class Object
     * @var \Ced\Fruugo\Helper\Signature
     */
    public $apiSignature;

    /**
     * Fruugo Helper
     * @var \\Ced\Fruugo\Helper\Fruugo
     */
    public $fruugoHelper;

    /**
     * Debug Log Mode
     * @var boolean
     */
    public $debugMode;

    /**
     * InventoryFullfillmentTime in days
     * @var integer
     */
    public $fulfillmentLagTime;

    /**
     * ProductIdType [UPC/EAN/GTIN/ISSN/ISBN]
     * @var integer
     */
    public $productIdType;

    /**
     * Fruugo Logger
     * @var \Ced\Fruugo\Helper\FruugoLogger
     */
    public $fruugoLogger;

    /**
     * Selected Store Id
     * @var $selectedStore
     */
    public $selectedStore;
    /*
     * Mage Core Registry
     */
    public $registry;

    protected $_cache;

    protected $pcode;

    protected  $currentProfileId;

    protected  $_profile;
    /*
     * Magento\Framework\Message\Manager
     */
    public $messageManager;
    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\HTTP\Adapter\Curl $curl
     * @param \Magento\Framework\Json\Helper\Data $json
     * @param \Magento\Framework\Xml\Generator $generator
     * @param \Magento\Framework\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\Filesystem\Io\File $fileIo
     * @param \Ced\Fruugo\Helper\Signature $signature
     * @param \Ced\Fruugo\Helper\Fruugo $fruugoHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\HTTP\Adapter\Curl $curl,
        \Magento\Framework\Json\Helper\Data $json,
        \Magento\Framework\Xml\Generator $generator,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Io\File $fileIo,
        \Ced\Fruugo\Helper\Signature $signature,
        \Ced\Fruugo\Helper\Fruugo $fruugoHelper,
        \Ced\Fruugo\Helper\Cache $cache,
        \Magento\Backend\App\Action\Context $actionContext,
        Manager $manager,
        \Magento\Framework\UrlInterface $urlRedirect,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Catalog\Helper\Image $productImageHelper
    ) {
        parent::__construct($context);

        $this->_request = $request;
        $this->_responseFactory = $responseFactory;
        $this->_url = $urlRedirect;
        $this->objectManager = $objectManager;
        $this->resource = $curl;
        $this->json = $json;
        $this->xml = $generator;
        $this->directoryList = $directoryList;
        $this->fileIo = $fileIo;
        $this->fruugoHelper = $fruugoHelper;
        $this->_cache = $cache;
        $this->_productImageHelper = $productImageHelper;
        $this->scopeConfigManager = $this->objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_storeManager = $this->objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $this->apiUrl = $this->scopeConfigManager->getValue('fruugoconfiguration/fruugosetting/api_url');
        $this->apiUserName = $this->scopeConfigManager->getValue('fruugoconfiguration/fruugosetting/username');
        $this->apiUserPassword = $this->scopeConfigManager->getValue('fruugoconfiguration/fruugosetting/user_password');
        $this->apiConsumerId = $this->scopeConfigManager->getValue('fruugoconfiguration/fruugosetting/customer_id');
        $this->apiPrivateKey = $this->scopeConfigManager->getValue('fruugoconfiguration/fruugosetting/private_key');
        $this->apiConsumerChannelId = self::CONSUMER_CHANEL_TYPE_ID;
        $this->apiSignature = $signature;
        $this->dateTime =  $this->objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
        $this->fruugoLogger =  $this->objectManager->create('\Ced\Fruugo\Helper\FruugoLogger');
        $this->registry =  $this->objectManager->create('\Magento\Framework\Registry');
        $this->debugMode = $this->scopeConfigManager->getValue('fruugoconfiguration/fruugosetting/debug');
        $this->_ischeckmanufac=$this->scopeConfigManager->getValue('fruugoconfiguration/productinfo_map/fruugo_manufacturer');
        $this->fulfillmentLagTime = $this->scopeConfigManager->getValue
        ('fruugoconfiguration/productinfo_map/fruugo_fullfillment_lagtime');
        if (!is_numeric($this->fulfillmentLagTime) || empty($this->fulfillmentLagTime)) {
            $this->fulfillmentLagTime = '1';
        }
        $this->productIdType = $this->scopeConfigManager->getValue
        ('fruugoconfiguration/productinfo_map/fruugo_productid_type');
        $this->selectedStore =
            $this->scopeConfigManager->getValue('fruugoconfiguration/product_edit/fruugo_storeid');
        $this->selectedStore =
            !empty($this->selectedStore) ? $this->selectedStore : 0 ;
        $this->messageManager = $manager;
        $this->session =  $actionContext->getSession();
    }

    /**
     * Create fruugo directory in the specified root directory.
     * used for storing json/xml files to be synced.
     * @param string $name
     * @param string $code
     * @return array|string
     */
    public function createDir($name = 'fruugo', $code='var')
    {
        $path = $this->directoryList->getPath($code) . "/" . $name;
        if (file_exists($path)) {
            return ['status' => true,'path' => $path, 'action' => 'dir_exists'];
        } else {
            try
            {
                $this->fileIo->mkdir($path, 0775, true);
                return  ['status' => true,'path' => $path,  'action' => 'dir_created'];
            }
            catch (\Exception $e){
                return $code . '/' . $name . "Directory Creation Failed.";
            }
        }
    }

    /**
     * Create Json/Xml File
     * @param string|[] $data associative array to be converted into json or xml file
     * @param string|[] $params
     * 'type': file type json or xml, default json,
     * 'name': file name,
     * 'path': path to save file, default 'var/fruugo'
     * 'code': directory code, default 'var'
     * @return boolean
     */
    public function createFile($data, $params = [])
    {
        $type = 'json';
        $timestamp = $this->objectManager->create('\Magento\Framework\Stdlib\DateTime\DateTime');
        $name = 'fruugo_' . $timestamp->gmtTimestamp();
        $path = 'fruugo';
        $code = 'var';

        if (isset($params['type'])) {
            $type = $params['type'];
        }
        if (isset($params['name'])) {
            $name = $params['name'];
        }
        if (isset($params['path'])) {
            $path = $params['path'];
        }
        if (isset($params['code'])) {
            $code = $params['code'];
        }

        if ($type == 'xml') {
            $xmltoarray = $this->objectManager->create('Magento\Framework\Convert\ConvertArray');
            $data = $xmltoarray->assocToXml($data);
        } elseif ($type == 'json') {
            $data = $this->json->jsonEncode($data);
        } elseif ($type == 'string') {
            $data = ($data);
        }

        $dir = $this->createDir($path, $code);
        $filePath = $dir['path'];
        $fileName = $name ."." . $type;
        try {
            $this->fileIo->write($filePath . "/" . $fileName, $data);
        }
        catch (\Exception $e){
            return false;
        }

        return true;
    }

    /**
     * Load File
     * @param string $path
     * @param string $code
     * @return mixed|string
     */
    public function loadFile($path, $code = '')
    {

        if (!empty($code)) {
            $path = $this->directoryList->getPath($code) . "/" . $path;
        }

        if (file_exists($path)) {
            $pathInfo = pathinfo($path);
            if ($pathInfo['extension'] == 'json') {
                $myfile = fopen($path, "r");
                $data = fread($myfile, filesize($path));
                fclose($myfile);
                if (!empty($data)) {
                    try
                    {
                        $data = $this->json->jsonDecode($data);
                        return $data;
                    }
                    catch (\Exception $e){
                        $this->_logger->debug($e->getMessage());
                    }
                }

            }

        }
        return false;
    }

    /**
     * Post Request on https://marketplace.fruugoapis.com/
     * $params = ['file' => ""
     *             'data' => ""
     *             ''headers => ""]
     * @param string $url
     * @param string|[] $params
     * @return string
     */
    public function postRequest($url, $params = [])
    {
        try{
            $url = $this->apiUrl . $url;
            $username = $this->apiUserName;
            $password = $this->apiUserPassword;
            $headers = array();
            $headers[] = "Content-Type: application/x-www-form-urlencoded";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
            $serverOutput = curl_exec($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($serverOutput, 0, $header_size);
            $body = substr($serverOutput, $header_size);
            curl_close($ch);
            return $body;
        } catch(\Exception $e) {
            if($this->debugMode)
                $this->fruugoLogger->logger(
                    'Post Request',
                    'Exception In Function',
                    $e->getMessage(),
                    'fruugo->Helper->Data.php : postRequest()'
                );
            return false;
        }

    }


    /**
     * Post Request on https://marketplace.fruugoapis.com/
     * $params = ['file' => ""
     *             'data' => ""
     *             ''headers => ""]
     * @param string $url
     * @param string|[] $params
     * @return string
     */
    public function postRequestWithXml($url, $params = [])
    {
        try{
            $url = $this->apiUrl . $url;
            $username = $this->apiUserName;
            $password = $this->apiUserPassword;
            $headers = array();
            $headers[] = "Content-Type: application/x-www-form-urlencoded";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/XML'));
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
            $serverOutput = curl_exec($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($serverOutput, 0, $header_size);
            $body = substr($serverOutput, $header_size);
            curl_close($ch);
            return $body;
        } catch(\Exception $e) {
            if($this->debugMode)
                $this->fruugoLogger->logger(
                    'Post Request',
                    'Exception In Function',
                    $e->getMessage(),
                    'fruugo->Helper->Data.php : postRequest()'
                );
            return false;
        }

    }



    /**
     * Get Request on https://marketplace.fruugoapis.com/
     * @param string $url
     * @param string|[] $params
     * @return string
     */
    public function getRequest($url, $params = [])
    {
        try{
            //$signature = $this->apiSignature->getSignature($url, 'GET');
            //$url = $this->apiUrl . $url;
           /* $timestamp =  $this->apiSignature->timestamp;
            $consumerId = $this->apiConsumerId;
            $this->resource->setConfig(['header' => 0]);
            if (isset($params['timestamp'],$params['signature'],$params['consumer_id'])) {
                $timestamp = $params['timestamp'];
                $signature = $params['signature'];
                $consumerId = $params['consumer_id'];
                $url = $params['url'];
                $this->resource->setConfig(['header' => 1]);
            }
            $headers = [];
            $headers[] = "WM_SVC.NAME: Fruugo Marketplace";
            $headers[] = "WM_QOS.CORRELATION_ID: " . base64_encode(\phpseclib\Crypt\Random::string(16));
            $headers[] = "WM_SEC.TIMESTAMP: " . $timestamp;
            $headers[] = "WM_SEC.AUTH_SIGNATURE: " . $signature;
            $headers[] = "WM_CONSUMER.ID: " .   $consumerId;
            $headers[] = "Content-Type: application/json";
            $headers[] = "Accept: application/xml";
            if (isset($params['headers']) && !empty($params['headers'])) {
                $headers[] = $params['headers'];
            }
            $headers[] = "HOST: marketplace.fruugoapis.com";

            $this->resource->setOptions([CURLOPT_HEADER => 1, CURLOPT_RETURNTRANSFER=>'true' ]);
            //for curl https use install certificate, add certificate location to php.ini
            $this->resource->setOptions([CURLOPT_HEADER => 1, CURLOPT_RETURNTRANSFER=>'true']);
            $this->resource->write("GET", $url, '1.1', $headers);
            $serverOutput = $this->resource->read();*/
            $url = $this->apiUrl . $url;
            $username = $this->apiUserName;
            $password = $this->apiUserPassword;
            $headers = array();
            $headers[] = "Content-Type: application/x-www-form-urlencoded";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
            $serverOutput = curl_exec($ch);
            /*$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($serverOutput, 0, $header_size);
            $body = substr($serverOutput, $header_size);*/
            curl_close($ch);

            /*if (!$serverOutput) {
                return false;
            }*/
            /*$this->resource->close();*/
            return $serverOutput;
            //echo "<pre>";print_r($serverOutput);die('dfg');

        } catch(\Exception $e) {
            if($this->debugMode)
                $this->fruugoLogger->logger(
                    'Get Request',
                    'Exception In Function',
                    $e->getMessage(),
                    'fruugo->Helper->Data.php : GetRequest()'
                );
            return false;
        }
    }

    /**
     * Delete Request on https://marketplace.fruugoapis.com/
     * @param string $url
     * @param string|[] $params
     * @return string
     */
    public function deleteRequest($url, $params = [])
    {
        try{
            $signature = $this->apiSignature->getSignature($url, 'DELETE');
            $url = $this->apiUrl . $url;
            $headers = [];
            $headers[] = "WM_SVC.NAME: Fruugo Marketplace";
            $headers[] = "WM_QOS.CORRELATION_ID: " . base64_encode(\phpseclib\Crypt\Random::string(16));
            $headers[] = "WM_SEC.TIMESTAMP: " . $this->apiSignature->timestamp;
            $headers[] = "WM_SEC.AUTH_SIGNATURE: " . $signature;
            $headers[] = "WM_CONSUMER.ID: " .  $this->apiConsumerId;
            $headers[] = "Content-Type: application/json";
            $headers[] = "Accept: application/xml";
            if (isset($params['headers']) && !empty($params['headers'])) {
                $headers[] = $params['headers'];
            }
            $headers[] = "HOST: marketplace.fruugoapis.com";
            //turning off header from curl response
            $this->resource->setConfig(['header' => 0]);
            $this->resource->setOptions([CURLOPT_HEADER => 1, CURLOPT_RETURNTRANSFER=>'true' ]);
            //for curl https use install certificate, add certificate location to php.ini
            $this->resource->setOptions([
                CURLOPT_HEADER => 1,
                CURLOPT_RETURNTRANSFER=>'true',
                CURLOPT_CUSTOMREQUEST => "DELETE"
            ]);
            $this->resource->write("DELETE", $url, '1.1', $headers);
            $serverOutput = $this->resource->read();


            if (!$serverOutput) {
                return false;
            }
            $this->resource->close();
            return $serverOutput;
        } catch (\Exception $e) {
            if($this->debugMode)
                $this->logger(
                    'Delete Request',
                    'Exception In Function',
                    $e->getMessage(),
                    'fruugo->Helper->Data.php : deleteRequest()'
                );
            return false;
        }

    }

    /**
     * Get a Order
     * @param string $purchaseOrderId
     * @param string $subUrl
     * @return array|string
     */
    public function getOrder($purchaseOrderId, $subUrl = self::GET_ORDERS_SUB_URL )
    {

        $response = $this->getRequest($subUrl . '?purchaseOrderId=' . $purchaseOrderId,
            ['headers' => 'WM_CONSUMER.CHANNEL.TYPE: ' . $this->apiConsumerChannelId]);
        try {
            $response = $this->json->jsonDecode($response);
            if (isset($response['error'])) {
                throw new \Exception();
            }
            return isset($response['list'])? $response['list']:$response;
        }
        catch(\Exception $e) {
            if ($this->debugMode) {
                $this->fruugoLogger->logger('Fruugo getOrder-'.$purchaseOrderId , "Exception In Function" ,
                    var_export($response , true) ,'getSingle Order');
            }

            return false;
        }
    }

    /**
     * Get Orders
     * @param string|[] $params - date in yy-mm-dd
     * @param string $subUrl
     * @return string
     * @link  https://developer.fruugoapis.com/#get-all-orders
     */
    public function getOrders($params = ['createdStartDate' => '2017-01-01'], $subUrl = self::GET_ORDERS_SUB_URL)
    {
        //$queryString =  empty($params) ? '' : '?' . http_build_query($params);//'?limit=100&hasMoreElements=true&soIndex=1305&poIndex=2578085869769&partnerId=10000000153&sellerId=266&status=Acknowledged&createdStartDate=2017-01-30&createdEndDate=2017-03-10T09:35:25.252Z';

        /*$response = $this->getRequest($subUrl . $queryString,
            ['headers' => 'WM_CONSUMER.CHANNEL.TYPE: ' . $this->apiConsumerChannelId]);*/
       $date = date('Y-m-d');
            $newdate = strtotime ('-5 day' , strtotime ($date));
            
            $final_date = $subUrl . date('Y-m-d',$newdate).'&to='.date('Y-m-d');
        $response = $this->getRequest($subUrl . date('Y-m-d',$newdate));
        //$response = file_get_contents('fruugo_order.xml');
        try {
            $this->xml = $this->objectManager->create('Ced\Fruugo\Helper\Custom\Generator');
            $response = $this->xml->loadXML($response)->xmlToArray();
            //echo "<pre>";print_r($response);die('sd');
            //$response = $this->json->jsonDecode($response);
            if (isset($response['error'])) {
                throw new \Exception();
            }
            return isset($response['list'])? $response['list']:$response;
        }
        catch(\Exception $e) {
            if ($this->debugMode) {
                $this->fruugoLogger->logger('Fruugo getOrders' , "Exception In Function" ,
                    var_export($response , true) ,'getMultiple Orders');
            }
            return false;
        }

    }

    /**
     * Acknowledge Order
     * @param string $purchaseOrderId
     * @param string $subUrl
     * @return string
     * @link  https://developer.fruugoapis.com/#acknowledging-orders
     */
    public function acknowledgeOrder($confirmed_items , $subUrl = self::CONFIRM_ORDERS_SUB_URL)
    {
        $response = $this->postRequest($subUrl, $confirmed_items);
        try {
            $this->xml = $this->objectManager->create('Ced\Fruugo\Helper\Custom\Generator');
            $response = $this->xml->loadXML($response)->xmlToArray();
            //$response = $this->json->jsonDecode($response);
            if (isset($response['error'])) {
                throw new \Exception();
            }
            return isset($response['order']) ? $response['order'] : $response;
        }
        catch(\Exception $e){
            if ($this->debugMode) {
                $this->fruugoLogger->logger('Fruugo Order' , ' Acknowledge Order',var_export($response,true) , 'Exception-'.$e->getMessage());
            }
            return false;
        }

    }



    /**
     * Cancel Order
     * @param string $purchaseOrderId
     * @param string $subUrl
     * @return string
     * @link  https://developer.fruugoapis.com/#acknowledging-orders
     */
    public function cancelOrder($reject_items , $subUrl = self::CANCEL_ORDERS_SUB_URL)
    {
        $response = $this->postRequest($subUrl, $reject_items);
        try {
            $this->xml = $this->objectManager->create('Ced\Fruugo\Helper\Custom\Generator');
            $response = $this->xml->loadXML($response)->xmlToArray();
            //$response = $this->json->jsonDecode($response);
            if (isset($response['error'])) {
                throw new \Exception();
            }
            return isset($response['order']) ? $response['order'] : $response;
        }
        catch(\Exception $e){
            if ($this->debugMode) {
                $this->fruugoLogger->logger('Fruugo Order' , ' Acknowledge Order',var_export($response,true) , 'Exception-'.$e->getMessage());
            }
            return false;
        }

    }





    /**
     * Ship Orders
     * @param string $postData
     * @param string $subUrl
     * @return string
     * @link  https://developer.fruugoapis.com/#cancelling-order-lines
     */
    public function shipOrder($postData = null , $subUrl = self::SHIP_ORDERS_SUB_URL)
    {
        $purchaseOrderId = $postData['shipments'][0]['purchaseOrderId'];

        foreach ($postData['shipments'] as $key => $values) {
            if (!isset($values['shipment_items'])) {
                continue;
            }
            $item_confirmed = '';
            foreach ($values['shipment_items'] as $value) {
                $item_confirmed .= '&item='.$value['fruugo_prodID'].','.$value['fruugo_skuID'].','.$value['response_shipment_sku_quantity'];
            }
            $confirmed_items_arr = "orderId=" . $purchaseOrderId . $item_confirmed;

        }

        if (isset($postData['shipments'][0]['shipment_tracking_number']) && !empty($postData['shipments'][0]['shipment_tracking_number'])) {
            $confirmed_items_arr .= '&trackingCode='.$postData['shipments'][0]['shipment_tracking_number'];
        }

        if (isset($postData['shipments'][0]['shipment_tracking_url']) && !empty($postData['shipments'][0]['shipment_tracking_url'])) {
            $confirmed_items_arr .= '&trackingUrl='.$postData['shipments'][0]['shipment_tracking_url'];
        }
        $response = $this->postRequest($subUrl, $confirmed_items_arr);
        try{
            if ($this->debugMode) {
                $this->fruugoLogger->logger('Shipment Order-'.$purchaseOrderId , 'Response (Post Request)' , var_export($data,true) ,'No Exception Case | Purchase Order Id '.$purchaseOrderId );
            }
            return $response;
        }
        catch(\Exception $e){
            if ($this->debugMode) {
                $this->fruugoLogger->logger('Shipment-'.$purchaseOrderId, 'Response (Post Request)', $response, 'Exception-' . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * Reject Order
     * @param string $purchaseOrderId
     * @param string $lineNumber
     * @param string $subUrl
     * @return string
     * @link  https://developer.fruugoapis.com/#cancelling-order-lines
     */
    public function rejectOrder($purchaseOrderId , $lineNumber , $subUrl = self::GET_ORDERS_SUB_URL)
    {
        $cancelArray = [
            'ns2:orderCancellation' => [
                '_attribute' => [
                    'xmlns:ns2' => "http://fruugo.com/mp/v3/orders",
                    'xmlns:ns3' => "http://fruugo.com/",
                ],
                '_value' => [
                    'ns2:orderLines' => []
                ]
            ]
        ];
        $cancelArray['ns2:orderCancellation']['_value']['ns2:orderLines']
        ['_attribute'] = [];
        $cancelArray['ns2:orderCancellation']['_value']['ns2:orderLines']
        ['_value'][0]['ns2:orderLine']['ns2:lineNumber'] = (string)$lineNumber;
        $cancelArray['ns2:orderCancellation']['_value']['ns2:orderLines']
        ['_value'][0]['ns2:orderLine']['ns2:orderLineStatuses'] = [
            'ns2:orderLineStatus' => [
                'ns2:status' => 'Cancelled',
                'ns2:cancellationReason' => 'CANCEL_BY_SELLER',
                'ns2:statusQuantity' => [
                    'ns2:unitOfMeasurement' => 'EACH',
                    'ns2:amount' => '1'
                ]
            ]
        ];

        $customGenerator = $this->objectManager->create('Ced\Fruugo\Helper\Custom\Generator');
        $customGenerator->arrayToXml($cancelArray);
        $str = preg_replace('/(\<\?xml\ version\=\"1\.0\"\?\>)/', '<?xml version="1.0" encoding="UTF-8" ?>',
            $customGenerator->__toString());
        $params['data'] = $str;
        $params['headers'] = 'WM_CONSUMER.CHANNEL.TYPE: ' . $this->apiConsumerChannelId;
        $this->createFile($str, ['type' => 'string', 'name' => 'CancelOrder']);
        $response = $this->postRequest($subUrl.'/'.$purchaseOrderId.'/cancel',
            $params);
        try{
            $response = $this->json->jsonDecode($response);
            if (isset($response['error'])) {
                throw new \Exception();
            }
            if ($this->debugMode) {
                $this->fruugoLogger->logger('Reject Order-'.$purchaseOrderId , 'Response (Post Request)' , var_export($response , true) ,'No Exception Case | Purchase Order Id '.$purchaseOrderId );
            }
            return $response;
        }
        catch(\Exception $e){
            if ($this->debugMode) {
                $this->fruugoLogger->logger('Reject Order-'.$purchaseOrderId , 'Response (Post Request)', $response, 'Exception-' . $e->getMessage());
            }
            return false;
        }
    }


    /**
     * Reject Order
     * @param string $purchaseOrderId
     * @param string $lineNumber
     * @param string $subUrl
     * @return string
     * @link  https://developer.fruugoapis.com/#cancelling-order-lines
     */
    public function rejectOrders($purchaseOrderId , $dataship , $subUrl = self::CANCEL_ORDERS_SUB_URL)
    {
        /*$cancelArray = [
            'ns2:orderCancellation' => [
                '_attribute' => [
                    'xmlns:ns2' => "http://fruugo.com/mp/v3/orders",
                    'xmlns:ns3' => "http://fruugo.com/",
                ],
                '_value' => [
                    'ns2:orderLines' => []
                ]
            ]
        ];*/

        $counter = 0;
        $item_cancelled = '';
        foreach ($dataship['shipments'] as $values){
            if (!isset($values['cancel_items'])) {
                continue;
            }
            foreach ($values['cancel_items'] as $value) {
               /* $lineNumbers =  explode(',', $value['lineNumber']);
                $cancelArray['ns2:orderCancellation']['_value']['ns2:orderLines']['_attribute'] = [];
                foreach ($lineNumbers as $lineNumber) {
                    $cancelArray['ns2:orderCancellation']['_value']['ns2:orderLines']
                    ['_value'][$counter]['ns2:orderLine']['ns2:lineNumber'] = (string)$lineNumber;
                    $cancelArray['ns2:orderCancellation']['_value']['ns2:orderLines']
                    ['_value'][$counter]['ns2:orderLine']['ns2:orderLineStatuses'] = [
                        'ns2:orderLineStatus' => [
                            'ns2:status' => 'Cancelled',
                            'ns2:cancellationReason' => 'CANCEL_BY_SELLER',
                            'ns2:statusQuantity' => [
                                'ns2:unitOfMeasurement' => 'EACH',
                                'ns2:amount' => '1'
                            ]
                        ]
                    ];
                    $counter++;
                }*/
                $item_cancelled .= '&item='.$value['fruugo_prodID'].','.$value['fruugo_skuID'].','.$value['response_shipment_cancel_qty'];
            }
            /*$cancel_items_arr[] = array('orderId' => $purchaseOrderId,
                'item' => $item_cancelled,
                'cancellationReason' => 'out_of_stock'
            );*/
            $cancel_items_arr = "orderId=" . $purchaseOrderId . $item_cancelled . "&cancellationReason=out_of_stock";

        }
        /*if($counter == 0)
        {
            return 'no_order_lines';
        }
        $customGenerator = $this->objectManager->create('Ced\Fruugo\Helper\Custom\Generator');
        $customGenerator->arrayToXml($cancelArray);
        $str = preg_replace('/(\<\?xml\ version\=\"1\.0\"\?\>)/', '<?xml version="1.0" encoding="UTF-8" ?>',
            $customGenerator->__toString());
        $params['data'] = $str;
        $params['headers'] = 'WM_CONSUMER.CHANNEL.TYPE: ' . $this->apiConsumerChannelId;
        $this->createFile($str, ['type' => 'string', 'name' => 'CancelOrder']);*/
        $response = $this->postRequest($subUrl, $cancel_items_arr);
        try{
            /*$parser = $this->objectManager->create('\Magento\Framework\Xml\Parser');
            $response = str_replace('ns:2', '', $response);
            $response = $parser->loadXML($response)->xmlToArray();*/
            //$response = $this->json->jsonDecode($response);
            if (isset($response['error'])) {
                throw new \Exception();
            }
            if ($this->debugMode) {
                $this->fruugoLogger->logger('Reject Order-'.$purchaseOrderId , 'Response (Post Request)' , var_export($response , true) ,'No Exception Case | Purchase Order Id '.$purchaseOrderId );
            }
            return $response;
        }
        catch(\Exception $e){
            if ($this->debugMode) {
                $this->fruugoLogger->logger('Reject Order-'.$purchaseOrderId, 'Response (Post Request)', $response, 'Exception-' . $e->getMessage());
            }
            return false;
        }
    }
    /**
     * Refund Order
     * @param string $purchaseOrderId
     * @param string $orderData
     * @param string $subUrl
     * @return string
     * @link  https://developer.fruugoapis.com/#cancelling-order-lines
     */
    public function refundOrder($purchaseOrderId , $orderData , $subUrl = self::GET_ORDERS_SUB_URL)
    {
        $refundData = [
            'ns2:orderRefund' => [
                '_attribute' => [
                    'xmlns:ns2' => "http://fruugo.com/mp/v3/orders",
                    'xmlns:ns3' => "http://fruugo.com/",
                ],
                '_value' => [
                    'ns2:purchaseOrderId' => $purchaseOrderId,
                    'ns2:orderLines' => [
                        'ns2:orderLine' =>[
                            'ns2:lineNumber' => $orderData['lineNumber'],
                            'ns2:refunds' => [
                                'ns2:refund' => [
                                    'ns2:refundComments' => $orderData['refundComments'],
                                    'ns2:refundCharges' => [
                                        '_attribute' => [],
                                        '_value' =>[
                                            0 => [
                                                'ns2:refundCharge' => [
                                                    'ns2:refundReason' => $orderData['refundReason'],
                                                    'ns2:charge' => [
                                                        'ns2:chargeType' => 'PRODUCT',
                                                        'ns2:chargeName' => 'Item Price',
                                                        'ns2:chargeAmount' => [
                                                            'ns2:currency' => 'USD',
                                                            'ns2:amount' => $orderData['amount']
                                                        ],
                                                        'ns2:tax' => [
                                                            'ns2:taxName' => 'Item Price Tax',
                                                            'ns2:taxAmount' => [
                                                                'ns2:currency' => 'USD',
                                                                'ns2:amount' => $orderData['taxAmount']
                                                            ]
                                                        ]
                                                    ]
                                                ]
                                            ],
                                            1 =>[
                                                'ns2:refundCharge' => [
                                                    'ns2:refundReason' => 'TaxExemptCustomer',
                                                    'ns2:charge' => [
                                                        'ns2:chargeType' => 'SHIPPING',
                                                        'ns2:chargeName' => 'Shipping Price',
                                                        'ns2:chargeAmount' => [
                                                            'ns2:currency' => 'USD',
                                                            'ns2:amount' => $orderData['shipping']
                                                        ],
                                                        'ns2:tax' => [
                                                            'ns2:taxName' => 'Item Price Tax',
                                                            'ns2:taxAmount' => [
                                                                'ns2:currency' => 'USD',
                                                                'ns2:amount' => $orderData['shippingTax']
                                                            ]
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ]

                                    ]
                                ]
                            ]
                        ]
                    ],
                ]
            ]
        ];
        $customGenerator = $this->objectManager->create('Ced\Fruugo\Helper\Custom\Generator');
        $customGenerator->arrayToXml($refundData);
        $str = preg_replace('/(\<\?xml\ version\=\"1\.0\"\?\>)/', '<?xml version="1.0" encoding="UTF-8" ?>',
            $customGenerator->__toString());
        $params['data'] = $str;
        $params['headers'] = 'WM_CONSUMER.CHANNEL.TYPE: ' . $this->apiConsumerChannelId;
        $this->createFile($str, ['type' => 'string', 'name' => 'RefundOrder']);
        $response = $this->postRequest($subUrl.'/'.$purchaseOrderId.'/refund',
            $params);
        try{
            $response = $this->json->jsonDecode($response);
            if (isset($response['error'])) {
                throw new \Exception();
            }
            if ($this->debugMode) {
                $this->fruugoLogger->logger('Refund Order-'.$purchaseOrderId , 'Response (Post Request)' , var_export($response , true) ,'No Exception Case | Purchase Order Id '.$purchaseOrderId );
            }
            return $response;
        }
        catch(\Exception $e){
            if ($this->debugMode) {
                $this->fruugoLogger->logger('Refund Order-'.$purchaseOrderId , 'Response (Post Request)', $response, 'Exception-' . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * Get Reports
     * @param string|[] $params
     * @param string $subUrl
     * @return string compressed csv file
     * @link https://developer.fruugoapis.com/#get-report
     */
    public function getReports($params = [], $subUrl = self::GET_REPORTS_SUB_URL)
    {
        if (!isset($params['type']) || empty($params['type'])) {
            $params['type'] = 'item';
        }
        $queryString = empty($params) ? '' : '?' . http_build_query($params);
        $response = $this->getRequest($subUrl . $queryString);
        //csv file in response
        return $response;
    }

    /**
     * Get Item
     * @param string $sku
     * @param string $returnField
     * @param string $subUrl
     * @return string|[]
     * @link https://developer.fruugoapis.com/#get-an-item
     */
    public function getItem($sku, $returnField = null, $subUrl = self::GET_ITEMS_SUB_URL )
    {
        $response = $this->getRequest($subUrl . '?sku=' . $sku);
        try {
            $response = $this->json->jsonDecode($response);
            if (isset($response['error'])) {
                throw new \Exception();
            }
            if ($returnField) {
                return $response['MPItemView'][0]['publishedStatus'];
            }
            return $response;
        }
        catch(\Exception $e){
            if ($this->debugMode) {
                $this->fruugoLogger->logger("Fruugo: getItem:","Get Response: SKU: ".$sku   , var_export($response, true));
            }
            return false;
        }
    }

    /**
     * Get Items
     * @param string|[] $params
     * @param string $subUrl
     * @return string
     * @link https://developer.fruugoapis.com/#get-all-items
     */
    public function getItems($params = [], $subUrl = self::GET_ITEMS_SUB_URL)
    {
        if (!isset($params['limit']) || empty($params['limit'])) {
            $params['limit'] = '20';
        }
        $queryString = empty($params) ? '' : '?' . http_build_query($params);
        $response = $this->getRequest($subUrl . $queryString);
        return $response;
    }

    /**
     * Get Inventory
     * @param string $sku
     * @param string $subUrl
     * @return string
     * @link https://developer.fruugoapis.com/#get-inventory-for-an-item
     */
    public function getInventory($sku, $subUrl = self::GET_INVENTORY_SUB_URL)
    {
        $response = $this->getRequest($subUrl . '?sku=' . $sku);
        return $response;
    }

    /**
     * Get Feeds, Get Single Feed, Get Single Feed with Error Details
     * @param null $feedId
     * @param string $subUrl
     * @param boolean $includeDetails
     * @return string|boolean
     * @link https://developer.fruugoapis.com/#feeds
     */
    public function getFeeds($feedId = null, $includeDetails = false, $subUrl = self::GET_FEEDS_SUB_URL )
    {
        $response = NULL;
        try {
            if ($feedId != null) {
                $subUrl = $subUrl . '?feedId=' . $feedId;
                if ($includeDetails) {
                    $subUrl = self::GET_FEED_SUB_URL . $feedId . '?includeDetails=true';
                }
            }
            $response = $this->getRequest($subUrl);
            return $this->json->jsonDecode($response);

        } catch (\Exception $e) {
            if ($this->debugMode) {
                $this->fruugoLogger->logger("getFeeds() "," Response : " ,
                    var_export($response, true) ,
                    " Exception : " . var_export($e->getMessage(), true)
                );
            }
            return false;
        }

    }

    /**
     * Create/Update Product on Fruugo
     * @param string|[] $ids
     * @return bool
     */
    public function createProductOnFruugo($ids, $additionalOverrideAttributes = [])
    {
        $ids = $this->validateAllProducts($ids, false);
        if(isset($ids['errors'])) {
            $error = $this->session->getErrorSession();
            if(!is_array($error)) {
                $error = [];
            }
            array_push($error, $ids['errors']);
            $this->session->setErrorSession($error);
        }
        if (isset($ids['valid']) && count($ids['valid']) > 0 ) {
            $currency = $this->objectManager->create('\Magento\Store\Model\StoreManager')
                ->getStore()
                ->getBaseCurrencyCode();
            $timeStamp = (string)$this->dateTime->gmtTimestamp();
            $additionalAttributes = $additionalOverrideAttributes;

            foreach ($ids['valid'] as $id) {
                try{
                    $product = $this->objectManager->create('Magento\Catalog\Model\Product')
                        ->setStoreId($this->selectedStore)
                        ->load($id['id']);

                    //if configurable product, than load parent category
                    if (isset($id['parentid'])) {
                        //generating additional variant attributes
                        $additionalAttributes =
                            $this->prepareAdditionalAttributes
                            ($id['variantattrmapped'], $additionalOverrideAttributes, $product);
                        $id['additionalAttributes'] =   $additionalAttributes;
                    }
                    $price = $this->fruugoHelper->getFruugoPrice($product , $id['profile']);

                    if (isset($id['parentid'])) {
                        //all simple attributes needed here (Case 1)
                        $this->getCurrentProfile($id['parentid']);
                        $attributes = $this->getFruugoAttributes($id['parentid'], [
                            'required' => false, 'mapped' => true, 'validation' => false
                        ]);
                        $customAttrs = $this->getFruugoAttributes($id['parentid'], [
                            'required' => false, 'mapped' => true, 'validation' => true
                        ]);
                    } else {
                        //all simple attributes needed here (Case 1)
                        $this->getCurrentProfile($id['id']);
                        $attributes = $this->getFruugoAttributes($id['id'], [
                            'required' => false, 'mapped' => true, 'validation' => false
                        ]);
                        $customAttrs = $this->getFruugoAttributes($id['id'], [
                            'required' => false, 'mapped' => true, 'validation' => true
                        ]);
                    }
                    if (isset($id['parentid'])) {
                        $configProduct = $this->objectManager->create('Magento\Catalog\Model\Product')
                            ->setStoreId($this->selectedStore)
                            ->load($id['parentid']);
                        $fromParentAttrs = explode(',', $this->scopeConfigManager->getValue('fruugoconfiguration/product_edit/fruugo_config_attr_from_parent'));
                        $fromParentAttrs = !empty($fromParentAttrs) ? $fromParentAttrs : [];
                        $fruugoAttributesColumn = array_column($customAttrs['attributes'], 'magento_attribute_code', 'fruugo_attribute_name');
                        foreach ($fromParentAttrs as $fromParentAttr) {
                            $magentoAttr = isset($fruugoAttributesColumn[$fromParentAttr]) ? $fruugoAttributesColumn[$fromParentAttr] : '';
                            if(!empty($magentoAttr)) {
                                $configProdValue = $configProduct->getData($magentoAttr);
                                $product->setData($magentoAttr, $configProdValue);
                            }
                        }
                    }
                    $category = $attributes['category'];
                    $attributes = $attributes["attributes"];

                    $randomCounter = 0;
                    foreach ($customAttrs['attributes'] as $customAttr) {
                        if(isset($customAttr['magento_attribute_code']) &&
                            $customAttr['magento_attribute_code'] == 'default') {
                            $product->setData('ced_'.$randomCounter,$customAttr['default']);
                            $attributes[$customAttr['fruugo_attribute_name']] = 'ced_'.$randomCounter;
                            $randomCounter++;
                        }
                    }
                    $attrValueArray['ProductId'] = $product->getId();
                    foreach ($attributes as $attrKey => $attrValue) {

                        if ($attrKey == 'StockQuantity') {


                            $stockQuantityAndStatus = $this->getMagentoProductAttributeValue($product, $attrKey, $attributes);
                            if (is_array($stockQuantityAndStatus)) {
                                $threshold = $this->getCurrentProfile($id['id']);
                                if (($stockQuantityAndStatus['qty'] <= $threshold['inventory_threshold_value']) && $threshold['inventory_setting'] == '1') {

                                    $attrValueArray[$attrKey] = (int)$threshold['fixed_threshold_value'];
                                } elseif ($stockQuantityAndStatus['qty'] < 0 || $product->getStatus()=='2') {
                                    $attrValueArray[$attrKey] = '0';
                                } else {
                                    $attrValueArray[$attrKey] = (string) $stockQuantityAndStatus['qty'];
                                }

                                continue;
                            }
                            $attrValueArray[$attrKey] = $stockQuantityAndStatus;
                        }
                        $attrValueArray[$attrKey] = $this->getMagentoProductAttributeValue($product, $attrKey, $attributes);
                    }

                    $lang = $this->scopeConfigManager->getValue('fruugoconfiguration/productinfo_map/fruugo_language');
                    $currency = $this->scopeConfigManager->getValue('fruugoconfiguration/productinfo_map/fruugo_currency');

                    $uploadType = "Product";
                    $productToUpload = $productArray = null;
                    /*if ($profile=$this->getCurrentProfile($id['id'])) {
                        $inventory_setting = $profile['inventory_setting'];
                        $inventory_threshold_value = $profile['inventory_threshold_value'];
                        $fixed_threshold_value = $profile['fixed_threshold_value'];
                    }
                    if ($inventory_setting && $attrValueArray['StockQuantity'] < $inventory_threshold_value ) {
                        $attrValueArray['StockQuantity']=$fixed_threshold_value;
                    }*/
                    //$attrValueArray['StockQuantity']=
                    if($this->_ischeckmanufac && empty($attrValueArray['EAN'])){
                        $attrValueArray['EAN']='EXCEP';
                    }
                    $vat_rate = (int)$attrValueArray['VATRate'];

                    $fruugo_prod_cat = '';
                    $mapped_conf_attr = $this->scopeConfigManager->getValue('fruugoconfiguration/productinfo_map/fruugo_cat_setting');
                    if($mapped_conf_attr=='--please select--')
                    {
                        $fruugo_prod_cat = $id['profile']['profile_category_level_1'];
                    }
                    else
                    {
                        $fruugo_prod_cat =  $this->getMagentoProductAttributeValue($product, $mapped_conf_attr, array());
                    }
                    if($fruugo_prod_cat == '' || $fruugo_prod_cat == null)
                    {
                        $fruugo_prod_cat = $id['profile']['profile_category_level_1'];
                    }
                    
                    $productToUpload[$uploadType] = [
                        'ProductId' => $attrValueArray['ProductId'],
                        'SkuId' => $attrValueArray['SkuId'],
                        'EAN' => $attrValueArray['EAN'],
                        'Brand' => $attrValueArray['Brand'],
                        'Category' => $fruugo_prod_cat,
                        /*'Imageurl1' => $imageUrl,*/
                        'StockQuantity' => (string)$attrValueArray['StockQuantity'],
                        'StockStatus' => ($attrValueArray['StockQuantity'] > 0) ? 'INSTOCK' : 'OUTOFSTOCK',
                        'Description' => [
                            'Language' => $lang,
                            'Title' => htmlspecialchars($attrValueArray['Title']),
                            'Description' => $attrValueArray['Description'],
                        ],
                        'Price' => [
                            'Currency' => $currency,
                            'VATRate' => (string)$vat_rate
                        ]
                    ];
                    $productImages = array();
                    if(isset($id['parentid']) && $id['parentid']) {
                        $useConfigImage = $this->scopeConfigManager->getValue('fruugoconfiguration/product_edit/fruugo_config_image');
                        if($useConfigImage == "1") {
                            $productImages = $configProduct->getMediaGalleryImages();
                        } else {
                            $productImages = $product->getMediaGalleryImages();
                        }
                    } else {
                        $productImages = $product->getMediaGalleryImages();
                    }
                    $imgKey = 1;
                    foreach ($productImages as $image) {
                        if(!empty($image->getUrl()) && $imgKey < 6) {
                            $productToUpload[$uploadType]["Imageurl$imgKey"] = $image->getUrl();
                                $Imagerror = true;
                                $imgKey++;
                        }
                    }

                    $profileCode = $id['profile']['profile_code'];
                    $usePrice = $this->scopeConfigManager
                        ->getValue("fruugoconfiguration/$profileCode/use_vat_price");
                    if( $usePrice == 1 ) {
                        $productToUpload[$uploadType]['Price']['NormalPriceWithVAT'] = /*$attrValueArray['NormalPriceWithVAT']*/(string)round($price['price'],2);
                    } else {
                        $productToUpload[$uploadType]['Price']['NormalPriceWithoutVAT'] = /*$attrValueArray['NormalPriceWithoutVAT']*/(string)round($price['price'],2);
                    }

                    if (isset($id['parentid'])) {
                        $productToUpload[$uploadType]['Description'] = array_merge($productToUpload[$uploadType]['Description'], $additionalAttributes);
                        $productToUpload[$uploadType]['ProductId'] = $id['parentid'];
                        //$productToUpload[$uploadType]['Price']['NormalPriceWithoutVAT'] = $product->getPrice();
                    }
                    if(isset($id['profile']['profile_attribute_mapping']['recommended_attribute'])) {
                        $optionalAttributes = $this->prepareOptionalAttributes
                            ($id['profile']['profile_attribute_mapping']['recommended_attribute'], $product, $profileCode);
                        if(is_array($optionalAttributes) && count($optionalAttributes) > 0) {
                            $productToUpload[$uploadType] = array_merge_recursive($productToUpload[$uploadType], $optionalAttributes);
                        }
                    }

                    $productArray = $this->session->getResponseSession();
                    if(!is_array($productArray)) {
                        $productArray = [];
                    }
                    array_push($productArray, $productToUpload);
                    $this->session->setResponseSession($productArray);
                } catch(\Exception $e) {
                    return false;
                }

                if($this->debugMode) {
                    /*$flag = $this->validateXMlData($productToUpload['MPItemFeed']['_value'][$key],$sku);

                    if(!$flag) {
                        unset($productToUpload['Products']['_value'][$key]);
                    }*/
                }
            }
            if($this->session->getAllBatchCompleted() || $this->session->getNoBatches()) {
                $path = $this->createDir('fruugo', 'media');
                $this->xml = $this->objectManager->create('Ced\Fruugo\Helper\Custom\Generator');
                $fileContent = [];
                if(file_exists($path['path'] . '/' . 'MPProduct.xml')) {
                    $fileContent = file_get_contents($path['path'] . '/' . 'MPProduct.xml');
                }
                $preSkus =
                $oldProducts = $this->xml->loadXml($fileContent)->xmlToArray();
                $newProducts = $this->session->getResponseSession();
                if(isset($oldProducts['Products']['Product'])) {
                    if(!isset($oldProducts['Products']['Product']['0'])) {
                        $singleOrder[0] = $oldProducts['Products']['Product'];
                        unset($oldProducts['Products']['Product']);
                        $oldProducts['Products']['Product'] = $singleOrder;
                    }
                    $preSkus = array_column($oldProducts['Products']['Product'], 'SkuId');
                    foreach ($newProducts as $newProduct) {
                        if(!isset($newSkus)) {
                            $newSkus = [];
                        }
                        array_push($newSkus, $newProduct['Product']['SkuId']);
                    }
                }
                if(!isset($preSkus) || !is_array($preSkus)) {
                    $preSkus = [];
                }
                if(!isset($newSkus) || !is_array($newSkus)) {
                    $newSkus = [];
                }
                $diffSkus = array_diff($preSkus, $newSkus);
                foreach ($diffSkus as $diffkey => $diffSku) {
                    $skuProd['Product'] = $oldProducts['Products']['Product'][$diffkey];
                    array_push($newProducts, $skuProd);
                }
                $this->session->unsResponseSession();
                $productToUpload = null;
                $productToUpload = [
                    'Products' => [
                        '_attribute' => [
                        ],
                        '_value' => $newProducts
                    ]
                ];
                $itemSuccess = 1;
                $this->session->unsResponseSession();
                $this->session->unsAllBatchCompleted();
                $this->session->unsNoBatches();
                $path = $this->createDir('fruugo', 'media');
                $this->xml = $this->objectManager->create('Ced\Fruugo\Helper\Custom\Generator');
                $this->xml->arrayToXml($productToUpload)->save($path['path'] . '/' . 'MPProduct.xml');
                $errorResponse = $this->session->getErrorSession();
                $this->session->unsErrorSession();
                $response = $this->responseParse($errorResponse, 'item', 'No File Available . Please Enable Debug Mode', $itemSuccess);
            }
            return true;
            if($this->debugMode){
                $this->fileIo->cp($path['path'].'/'.'MPProduct.xml', $cpPath);
                $response = $this->responseParse($response, 'item', $cpPath);
                return $response;
            }
            $response = $this->responseParse($response, 'item', 'No File Available . Please Enable Debug Mode');
            return $response;
        }
        if($this->session->getAllBatchCompleted() && !$this->session->getNoBatches()) {
            $productToUpload = null;
            $productToUpload = [
                'Products' => [
                    '_attribute' => [
                    ],
                    '_value' => $this->session->getResponseSession()
                ]
            ];
            $itemSuccess = 1;
            $this->session->unsResponseSession();
            $this->session->unsAllBatchCompleted();
            $path = $this->createDir('fruugo', 'media');
            $this->xml = $this->objectManager->create('Ced\Fruugo\Helper\Custom\Generator');
            $this->xml->arrayToXml($productToUpload)->save($path['path'] . '/' . 'MPProduct.xml');
            $errorResponse = $this->session->getErrorSession();
            $this->session->unsErrorSession();
            $response = $this->responseParse($errorResponse, 'item', 'No File Available . Please Enable Debug Mode', $itemSuccess);
        }
        return false;
    }


    public function getMagentoProductAttributeValue($product, $code, $attribute)
    {
        if (isset($attribute[$code])&& $attribute[$code] != "default") {
            $attributeCode = $attribute[$code];
              if ((!$product->getData($attributeCode) && $product->getData($attributeCode) != '0') || $product->getData($attributeCode) == "") {
                return NULL;
            }

            $attr = $product->getResource()->getAttribute($attributeCode);
            if ($attr && ($attr->usesSource() || $attr->getData('frontend_input') == 'select')) {
                if ($attributeCode == "quantity_and_stock_status") {
                    return $product->getData($attributeCode);
                }
                $productAttributeValue = $attr->getSource()->getOptionText($product->getData($attributeCode));

            } else {
                $productAttributeValue = $product->getData($attributeCode);
            }
            return $productAttributeValue;
        } else {
            return $product->getData($code);
        }
    }

     /**
     * Update Inventory On Fruugo
     * @param string|[] $ids
     * @return bool
     */
    public function updateInventoryOnFruugo($ids = null, $data = null)
    {   
        $timeStamp = '';
        $this->xml = $this->objectManager->create('Ced\Fruugo\Helper\Custom\Generator');
        $params = array('page' => 0);
        $queryString = empty($params) ? '' : '?' . http_build_query($params);
        $response = $this->getRequest('stockstatus-api' . $queryString);
        $api_hit = 0;
        $response = $this->xml->loadXML($response)->xmlToArray();
        $pages = 0;
        if(isset($response['skus']['_attribute']['numberOfPages'])) {
            $pages = (int)$response['skus']['_attribute']['numberOfPages'];
        }
        $page_no = 0;
        $api_response = '';
       while($page_no < $pages ) {
        $timeStamp = (string)$this->dateTime->gmtTimestamp();
        if($api_hit > 0) {
            $params = array('page' => $page_no);
            $queryString = empty($params) ? '' : '?' . http_build_query($params);
            $response = $this->getRequest('stockstatus-api' . $queryString);
            $response = $this->objectManager->get('Ced\Fruugo\Helper\Custom\Generator')->loadXML($response)->xmlToArray();
        }
        $api_hit += 1;
        $page_no += 1;

        // echo '<pre>';print_r($response);die('check floww1');
        if(!isset($response['html']))
            $res = $response['skus']['_value']['sku'];
            $prod_upload = array();$Inventory = array();
        foreach ($ids as $id) 
        { 
            $product = $this->objectManager->create('Magento\Catalog\Model\Product')->load($id['id'])
                ->setStoreId($this->selectedStore);
             //update on xml feed
            $this->dataHelper = $this->objectManager->get('Ced\Fruugo\Helper\Data');
            $this->session->setNoBatches(true);
            $prod_upload[] = $id['id'];
            //update on xml feed

            // for config product code start
            if ($product->getTypeId() == 'configurable')
            {
            $productType = $product->getTypeInstance();
            $products = $productType->getUsedProducts($product);
            foreach ($products as $product)
            {
                $frugo_sku_id = '';
                if(!isset($response['html']))
                {
                foreach ($res as $key => $value) 
                   {
                    if($product->getSku() == $value['_attribute']['merchantSkuId'])
                        {
                            $frugo_sku_id = $value['_attribute']['fruugoSkuId'];
                        }
                    }
                }

                if($frugo_sku_id!='')
                {
                    $qty = (int) $this->getProductQty($id['id'],$product->getId()); 
                    $qty = ($qty < 0) ? 0 : $qty;
                    $avail = 'OUTOFSTOCK';
                    if($qty > 0)
                    {
                        $avail = 'INSTOCK';
                    }
                    $Inventory[] = array(
                        'sku' => array(
                            '_attribute' => array(
                                'fruugoSkuId' => $frugo_sku_id,
                            ),
                            '_value' => array(
                                'availability' => (string)$avail,
                                'itemsInStock' => (string)$qty,
                            ),
                        ),

                    );
                }
            }
            }
            else
            {
                $frugo_sku_id = '';
                if(!isset($response['html']))
                {
                    foreach ($res as $key => $value) {
                        if($product->getSku() == $value['_attribute']['merchantSkuId']) {
                            $frugo_sku_id = $value['_attribute']['fruugoSkuId'];
                        }
                    }
                } 
                if($frugo_sku_id!='')
                {
                    $qty = (int) $this->getProductQty($id['id'],null);
                    $qty = ($qty < 0) ? 0 : $qty;
                    $avail = 'OUTOFSTOCK';
                    if($qty > 0)
                    {
                        $avail = 'INSTOCK';
                    }
                    $Inventory[] = array(
                        'sku' => array(
                            '_attribute' => array(
                                'fruugoSkuId' => $frugo_sku_id,
                            ),
                            '_value' => array(
                                'availability' => (string)$avail,
                                'itemsInStock' => (string)$qty,
                            ),
                        ),

                    );
                }
            }
        }
        //for config product code end
           
           
       if($prod_upload!=null)
        $this->createProductOnFruugo($prod_upload);
        
        if(!isset($Inventory) || count($Inventory) <= 0) {
            if($page_no == $pages){
                return $api_response;
            }else{
                continue;
            }
                
            }
            $path = $this->createDir('fruugo', 'media');
            $path = $path['path'] . '/' . 'InventoryFeed.xml';
            $this->xml = $this->objectManager->create('\Magento\Framework\Xml\Generator');
            $inventoryArray = array(
                'skus' => array(
                    '_attribute'=>array(),
                    '_value'=> $Inventory
                ),
            );
            $this->xml->arrayToXml($inventoryArray)->save($path);
            $api_response = $this->postRequestWithXml(self::GET_FEEDS_INVENTORY_SUB_URL, $this->xml->__toString() );
            // die($response);
            $cpPath = $this->createDir('fruugo', 'media');
            $cpPath = $cpPath['path'] . '/' . 'InventoryFeed_' . $timeStamp . '.xml';
            if ($this->debugMode) {
                $this->fileIo->cp($path, $cpPath);
                if($page_no == $pages){
                    return $api_response;
                }
            }
            if($page_no == $pages){
            return $api_response;
            }
     }
   }

    public function getProductQty($productId,$childId){
        if(is_object($productId))
            $productId = $productId->getId();

        $profile = $this->getCurrentProfile($productId);
        if(!is_array($profile))
            return false;
        if($childId!=null)
        {
          $product = $this->objectManager->create('Magento\Catalog\Model\Product')
            ->setStoreId($this->selectedStore)->load($childId);
        }
        else{

        $product = $this->objectManager->create('Magento\Catalog\Model\Product')
            ->setStoreId($this->selectedStore)->load($productId);
        }
        $profileMapping = isset($profile['profile_attribute_mapping']['required_attributes']) ?
            $profile['profile_attribute_mapping']['required_attributes'] : false;
        $profileMapping = array_column($profileMapping, 'fruugo_attribute_name');
        $stockKey = array_search('StockQuantity', $profileMapping);
        $magentoAttrCode = isset($profile['profile_attribute_mapping']['required_attributes'][$stockKey]['magento_attribute_code'])
            ? $profile['profile_attribute_mapping']['required_attributes'][$stockKey]['magento_attribute_code'] : false;
        if($magentoAttrCode == 'default') {
            $stockQty = $profile['profile_attribute_mapping']['required_attributes'][$stockKey]['default'];
        } else {
            $stockQuantityAndStatus = $product->getData($magentoAttrCode);
            if(($stockQuantityAndStatus['qty']<=$profile['inventory_threshold_value'] ) && $profile['inventory_setting']=='1' ){

                $stockQty = (int)$profile['fixed_threshold_value'];
            }elseif($stockQuantityAndStatus['qty']<0 || $product->getStatus()=='2'){
                $stockQty='0';
            } else {
                $stockQty = (string) $stockQuantityAndStatus['qty'];
            }
        }
        return $stockQty;
    }

    /**
     * Update Price On Fruugo
     * //$timeStamp = (string)$this->dateTime->gmtDate('Y-m-j\TH:m:s\Z');
     * @param string|[] $ids
     * @return bool
     */
    public function updatePriceOnFruugo($ids = null)
    {
        $timeStamp = (string)$this->dateTime->gmtTimestamp();
        $priceArray = [
            'PriceFeed' => [
                '_attribute' => [
                    'xmlns:gmp' => "http://fruugo.com/",
                ],
                '_value' => [
                    0 => [
                        'PriceHeader' => [
                            'version' => '1.5',
                        ],
                    ],
                ]
            ]
        ];
        $currency = $this->objectManager->create('\Magento\Store\Model\StoreManager')
            ->getStore()
            ->getBaseCurrencyCode();

        $key = 0;
        foreach ($ids as $id) {
            $product = $this->objectManager->create('Magento\Catalog\Model\Product')
                ->load($id)->setStoreId($this->selectedStore);
            if ($product->getVisibility() == 4) {
                if ($product->getTypeId() == 'configurable') {
                    $productType = $product->getTypeInstance();
                    $products = $productType->getUsedProducts($product);
                    foreach ($products as $product) {
                        $key += 1;
                        $price = $this->fruugoHelper->getFruugoPrice($product);
                        $priceArray['PriceFeed']['_value'][$key] = [
                            'Price' => [
                                'itemIdentifier' => [
                                    'sku' => $product->getSku()
                                ],
                                'pricingList' => [
                                    'pricing' => [
                                        'currentPrice' => [
                                            'value' => [
                                                '_attribute' => [
                                                    'currency' => $currency,
                                                    'amount' => $price['splprice']
                                                ],
                                                '_value' => [

                                                ]
                                            ]
                                        ],
                                        'currentPriceType' => 'BASE',
                                        'comparisonPrice' => [
                                            'value' => [
                                                '_attribute' => [
                                                    'currency' => $currency,
                                                    'amount' => $price['price']
                                                ],
                                                '_value' => [

                                                ]
                                            ]
                                        ],
                                    ]
                                ]
                            ]
                        ];
                    }
                } elseif ($product->getTypeId() == 'simple') {
                    $key += 1;
                    $price = $this->fruugoHelper->getFruugoPrice($product);
                    $priceArray['PriceFeed']['_value'][$key] = [
                        'Price' => [
                            'itemIdentifier' => [
                                'sku' => $product->getSku()
                            ],
                            'pricingList' => [
                                'pricing' => [
                                    'currentPrice' => [
                                        'value' => [
                                            '_attribute' => [
                                                'currency' => $currency,
                                                'amount' => $price['splprice']
                                            ],
                                            '_value' => [

                                            ]
                                        ]
                                    ],
                                    'currentPriceType' => 'BASE',
                                    'comparisonPrice' => [
                                        'value' => [
                                            '_attribute' => [
                                                'currency' => $currency,
                                                'amount' => $price['price']
                                            ],
                                            '_value' => [

                                            ]
                                        ]
                                    ],
                                ]
                            ]
                        ]
                    ];
                }
            }

        }


        $path = $this->createDir('fruugo', 'var');
        $path = $path['path'] . '/' . 'PriceFeed.xml';

        $xml = $this->objectManager->create('Ced\Fruugo\Helper\Custom\Generator');
        $xml->arrayToXml($priceArray)->save($path);
//        echo $xml->arrayToXml($priceArray)->__toString();die;
        $response = $this->postRequest(self::GET_FEEDS_PRICE_SUB_URL, ['file' => $path]);

        $cpPath =  $this->createDir('fruugo', 'media');
        $cpPath = $cpPath['path'].'/'.'PriceFeed_'.$timeStamp.'.xml';
        if($this->debugMode) {
            $this->fileIo->cp($path, $cpPath);
            $response = $this->responseParse($response, 'price', 'No Feed File Available. Pleas enable Debug Mode');
            return $response;
        }
        $this->fileIo->cp($path, $cpPath);
        $response = $this->responseParse($response, 'price', $cpPath);
        return $response;
    }

    /**
     * Validate XML Data
     * @param Array
     * @return bool
     */
    public function validateXMlData($productArray , $sku) {

        if(!empty($productArray)) {
            $productToUpload = [
                'MPItemFeed' => [
                    '_attribute' => [
                        'xmlns' => 'http://fruugo.com/',
                        'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
                        'xsi:schemaLocation' => 'http://fruugo.com/ MPItem.xsd',
                    ],
                    '_value' => [
                        0 => [
                            'MPItemFeedHeader' => [
                                'version' => '2.1',
                                'requestId' =>(string)$this->apiSignature->timestamp,
                                'requestBatchId' => (string)$this->apiSignature->timestamp,
                            ],
                        ]
                    ],
                ]
            ];
            $productToUpload['MPItemFeed']['_value'][1] = $productArray;
            $path = $this->createDir('fruugo', 'var');
            $this->xml = $this->objectManager->create('Ced\Fruugo\Helper\Custom\Generator');
            $path = $path['path']. '/' . 'MPProductValidate.xml';
            $this->xml->arrayToXml($productToUpload)->save($path);

            $validateArray = $this->validateXML($path);
            if(isset($validateArray['valid']) && $validateArray['valid'] === false) {
                $product = $this->objectManager->create('Magento\Catalog\Model\Product')
                    ->loadByAttribute('sku', $sku)->setStoreId($this->selectedStore);
                $error = [
                    'sku' => $sku,
                    'id' => $product->getId(),
                    'url' => $product->getId(),
                    'errors' => $validateArray['errors']
                ];
                /*print_r($error);
                die;*/
                $product->setData('fruugo_validation_errors',$this->json->jsonEncode($error));
                $product->getResource()->saveAttribute($product,'fruugo_validation_errors');
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * validateXML
     * @param XMLfilePath
     * @return array
     */
    public function validateXML($XMLfilePath , $xsdpath = '/code/Ced/Fruugo/etc/fruugo/mp/MPItemFeed.xsd') {

        libxml_use_internal_errors(true);
        $feed = new \DOMDocument();
        $xsdpath = $this->directoryList->getPath('app').$xsdpath;
        $feed->preserveWhitespace = false;
        $result = $feed->load($XMLfilePath);

        if($result === TRUE)
        {
            if(($feed->schemaValidate($xsdpath)))
            {
                return array('valid' => true , 'errors' => NULL);
            }
            else
            {
                $return = false;
                $errors = libxml_get_errors();
                $errorList = array();
                foreach($errors as $error) {
                    $errorList[] = 'Error in XML File of Product : Required-Attribute-Empty : '.$error->message;
                    /* $errorList .= "---\n";
                     $errorList .= "Error: %s \nfile: %s, line: %s, column: %s, level: %s, code: %s\n";
                     $errorList .=   $error->message."\n";
                     $errorList .=   $error->file."\n";
                     $errorList .=   $error->line."\n";
                     $errorList .=   $error->column."\n";
                     $errorList .=   $error->level."\n";
                     $errorList .=   $error->code."\n";*/
                }
                return array('valid' => false , 'errors' => $errorList);
            }
        }
        /*else {
            $return = false;
            $errors = array(0 => "! Document is not valid");

            return array('valid' => false , 'errors' => json_encode$errors);
        }*/
        return array('valid' => true , 'errors' => NULL);

    }

    public function getConfigData($pCode, $path ){

        $value = $this->scopeConfigManager->getValue($pCode."/".$path);
        if(!$value){
            $value = $this->scopeConfigManager->getValue($path);
        }

        return $value;

    }


    public function getCurrentProfile($productId){
        //check for the profile id for the product
//        $profileId = $this->_cache->getValue(\Ced\Fruugo\Helper\Cache::PROFILE_PRODUCT_CACHE_KEY.$productId);
        $profileId = '';
        if(!$profileId){
            if($profileProduct = $this->objectManager->create('Ced\Fruugo\Model\Profileproducts')->loadByField('product_id', $productId)){
                $profileId = $profileProduct->getProfileId();
                if($profileId)
                    $this->_cache->setValue(\Ced\Fruugo\Helper\Cache::PROFILE_PRODUCT_CACHE_KEY.$productId, $profileId);
            }else{
                $this->messageManager->addErrorMessage(__('Product with Product Id %1 is not assigned with any profile yet. Associate it with profile first then upload.', $productId));
            }
        }
        
        if(!$profileId) {
            $productParents = $this->objectManager
                ->create('Magento\ConfigurableProduct\Model\Product\Type\Configurable')
                ->getParentIdsByChild($productId);
            //if($productId == 87488) {
                //echo $productId;
                //echo "<pre>";print_r($productParents);die('gh');
                if (!empty($productParents)) {
                    foreach($productParents as $productParentId) {
                        $parentProductId = $productParentId;
                        if($profileProduct = $this->objectManager->create('Ced\Fruugo\Model\Profileproducts')->loadByField('product_id', $parentProductId)){
                            $profileId = $profileProduct->getProfileId();
                            if($profileId) {
                                $this->_cache->setValue(\Ced\Fruugo\Helper\Cache::PROFILE_PRODUCT_CACHE_KEY.$productId, $profileId);
                                break;
                            }
                        }
                    }
                }
            //}
        }
        $this->currentProfileId = $profileId;
        //skip this product if the profile is not associated with this product
        if(!$profileId)
            return;

        $profile =false;

        //check for the profile for the current product
        if(!isset($this->_profile[$profileId])){

            if($profile = $this->_cache->getValue(\Ced\Fruugo\Helper\Cache::PROFILE_CACHE_KEY.$profileId)){
                $this->_profile[$profileId] = $profile;
            }else{
                $profile = $this->objectManager->create('Ced\Fruugo\Model\Profile')->load($profileId)->getData();

                $profile['profile_attribute_mapping'] = json_decode($profile['profile_attribute_mapping'], true);

                /*$requiredAttribute = [];
                foreach($profile['profile_attribute_mapping']['simple_attribute'] as $attribute){
                    if(isset($attribute['required']) && $attribute['required']==1){
                        $requiredAttribute[] = $attribute;
                    }
                }
                $profile['required_attribute'] = $requiredAttribute;*/




                $this->_cache->setValue(\Ced\Fruugo\Helper\Cache::PROFILE_CACHE_KEY.$profileId, $profile);

                $this->_profile[$profileId] = $profile;
            }
        }




        if(isset($profile['profile_code']))
            $this->pcode = $profile['profile_code'];

        $this->selectedStore = $this->getConfigData($this->pcode, 'fruugoconfiguration/product_edit/fruugo_storeid');
        $this->selectedStore = !empty($this->selectedStore) ? $this->selectedStore : 0 ;

        $this->fulfillmentLagTime = $this->getConfigData($this->pcode,'fruugoconfiguration/productinfo_map/fruugo_fullfillment_lagtime');
        if (!is_numeric($this->fulfillmentLagTime) || empty($this->fulfillmentLagTime)) {
            $this->fulfillmentLagTime = '1';
        }
        $this->productIdType = $this->getConfigData($this->pcode, 'fruugoconfiguration/productinfo_map/fruugo_productid_type');


        return $this->_profile[$this->currentProfileId];


    }

    /**
     * Validate All Products
     * @param string|[] $ids
     * @return string|[]
     */
    public function validateAllProducts($ids = [], $statusCheck = true)
    {
        $validatedProducts = [];
        foreach ($ids as $id) {

            $product = $this->objectManager->create('Magento\Catalog\Model\Product')
                ->load($id)
                ->setStoreId($this->selectedStore);
            if ($product->getTypeId() == 'configurable' &&
                $product->getVisibility() != 1) {
                $configurableProductObject = $product;
                $errorsForConfigurable = [];
                $sku = $product->getSku();
                $parentId = $product->getId();
                $productType = $product->getTypeInstance();
                $products = $productType->getUsedProducts($product);
                $attributes = $productType->getConfigurableAttributesAsArray($product);
                foreach ($attributes as $attribute) {
                    $variantAttr[] = $attribute['attribute_code'];
                }
                $fruugoVariantAttr = $profile = [];
                if (!empty($parentId) && $parentId!=null) // if config product
                    $profile =$this->getCurrentProfile($parentId);
                elseif (empty($profile))
                    $profile = $this->getCurrentProfile($id); //if simple product
                $key = 0;
                $fruugoVariantAttr = isset($profile['profile_attribute_mapping']['variant_attributes']) ? $profile['profile_attribute_mapping']['variant_attributes'] : [];
                $notMappedVariantAttribute = array();
                $fruugoVariantAttrCustom = array_flip(array_column($fruugoVariantAttr, 'magento_attribute_code'));


                foreach ($variantAttr as $code){
                    if(!isset($fruugoVariantAttrCustom[$code]))
                        $notMappedVariantAttribute[] = $code;
                }

                foreach ($products as $product) {
                    $productid = $this->validateProduct($product->getId(), null, $statusCheck, $parentId);
                    if (isset($productid['id'])) {
                        // load current product profile
                        //Check if all mappedAttributes are mapped
                        if (count($notMappedVariantAttribute)==0) {
                            $validatedProducts['valid'][$product->getId()]['id'] = $productid['id'];
                            $validatedProducts['valid'][$product->getId()]['status'] = $productid['status'];
                            $validatedProducts['valid'][$product->getId()]['type'] = 'configurable';
                            $validatedProducts['valid'][$product->getId()]['variantid'] = $sku;
                            $validatedProducts['valid'][$product->getId()]['parentid'] = $parentId;
                            $validatedProducts['valid'][$product->getId()]['profile'] = $profile;
                            $fruugoVariantAttrArray = [];
                            foreach ($fruugoVariantAttr as $value) {
                                $attribute = explode('/', $value['fruugo_attribute_name']);
                                $fruugoVariantAttrArray[] =  $attribute[0];
                            }
                            $validatedProducts['valid'][$product->getId()]['variantattr'] = $fruugoVariantAttrArray;
                            $validatedProducts['valid'][$product->getId()]['variantattrmapped'] = $fruugoVariantAttr;
                            $validatedProducts['valid'][$product->getId()]['isprimary'] = 'false';
                            if ($key == 0) {
                                $validatedProducts['valid'][$product->getId()]['isprimary'] = 'true';
                                $key = 1;
                            }
                        } else {
                            $productid['errors'] = [
                                'sku' => $product->getSku(),
                                'id' => $product->getId(),
                                'url' => $product->getId(),
                                'errors' => [
                                    'Variant Attributes' =>
                                        implode(",", $notMappedVariantAttribute) .
                                        ' - Some of Configurable Attributes are not mapped.'
                                ]
                            ];
                            $errorsForConfigurable[] =   $productid['errors'];
                            continue;
                        }
                    } elseif (isset($productid['errors'])) {
                        if(count($notMappedVariantAttribute) > 0) {
                            if($productid['errors']!='Not Assigned to any profile.')
                            {
                                try{
                                    if(isset($productid['errors']['errors'])){
                                    $productid['errors']['errors']['Variant Attributes'] = implode(",", $notMappedVariantAttribute) .
                                    ' - Some of Configurable Attributes are not mapped.' ;
                                    }
                                 }
                                catch(\Exception $e){
                                    // print_r($e->getMessage()); die(__FILE__);
                                }
                            }
                        }
                        $errorsForConfigurable[] = $productid['errors'];
                    }
                }
                if (count($errorsForConfigurable) > 0) {
                    $validatedProducts['errors'][$configurableProductObject->getSku()]= $errorsForConfigurable;
                    $configurableProductObject->setData('fruugo_validation_errors',$this->json->jsonEncode($errorsForConfigurable));
                    $configurableProductObject->setFruugoProductValidation('Invalid');
                    $configurableProductObject->getResource()->saveAttribute($configurableProductObject,'fruugo_validation_errors')->saveAttribute($configurableProductObject,'fruugo_product_validation');
                } else {
                    $configurableProductObject->setData('fruugo_validation_errors',' ');
                    $configurableProductObject->setFruugoProductValidation('Valid');
                    $configurableProductObject->getResource()->saveAttribute($configurableProductObject,'fruugo_product_validation')->saveAttribute($configurableProductObject,'fruugo_validation_errors');
                }
            } elseif ($product->getTypeId() == 'simple') {
                $productid = $this->validateProduct($product->getId(), $product, $statusCheck);

                if (isset($productid['id'])) {
                    $profile = $this->getCurrentProfile($product->getId());
                    $validatedProducts['valid'][$product->getId()] = [
                        'id' => $productid['id'],
                        'status' => $productid['status'],
                        'type' => 'simple',
                        'variantid' => null,
                        'variantattr' => null,
                        'profile' => $profile
                    ];
                }

                if (isset($productid['errors'])){
                    $validatedProducts['errors'][$product->getSku()] = $productid['errors'];
                }
            }
        }
        return $validatedProducts;
    }

    /**
     * Validate product for availability of required fruugo product attribute data
     * @param string $id
     * @param null $product
     * @return bool|string
     */
    public function validateProduct($id, $product = null, $statusCheck = true, $parentProductId = null)
    {
        //check for the current profile

        if(!empty($parentProductId) && $parentProductId!=null) // if config product
            $this->getCurrentProfile($parentProductId);
        else
            $this->getCurrentProfile($id); //if simple product
        $validatedProduct = false;
        if ($product == null) {
            $product = $this->objectManager->create('Magento\Catalog\Model\Product')
                ->load($id)
                ->setStoreId($this->selectedStore);
        }

        if(!$this->currentProfileId){
            //$this->messageManager->addErrorMessage('Product SKU '.$product->getSku(). 'is not assigned to any profile.');
            $validatedProduct['errors'] = 'Not Assigned to any profile.';
            return $validatedProduct;
        }

        if(isset($this->_profile[$this->currentProfileId]['profile_status'])
            && $this->_profile[$this->currentProfileId]['profile_status']==0){
            $this->messageManager->addErrorMessage('Product SKU '.$product->getSku().' associated to profile '.$this->_profile[$this->currentProfileId]['profile_code'].' is disabled so skip from fruugo upload.');
            $validatedProduct['errors'] = 'Profile '.$this->_profile[$this->currentProfileId]['profile_code'] .' is disabled';
            return $validatedProduct;
        }

        // ALll required simple attributes which


        $magentoAttributes = $this->getFruugoAttributes($id, ['required' => true, 'mapped' => true,
            'validation' => true], $product);
        if(isset($parentProductId) && $parentProductId) {
            $configProduct = $this->objectManager->create('Magento\Catalog\Model\Product')
                ->setStoreId($this->selectedStore)
                ->load($parentProductId);
            $fromParentAttrs = explode(',', $this->scopeConfigManager->getValue('fruugoconfiguration/product_edit/fruugo_config_attr_from_parent'));
            $fromParentAttrs = !empty($fromParentAttrs) ? $fromParentAttrs : [];
            $fruugoAttributesColumn = array_column($magentoAttributes['attributes'], 'magento_attribute_code', 'fruugo_attribute_name');
            foreach ($fromParentAttrs as $fromParentAttr) {
                $magentoAttr = isset($fruugoAttributesColumn[$fromParentAttr]) ? $fruugoAttributesColumn[$fromParentAttr] : '';
                if(!empty($magentoAttr)) {
                    $configProdValue = $configProduct->getData($magentoAttr);
                    $product->setData($magentoAttr, $configProdValue);
                }
            }
        }
        //Case 1: Category is Mapped
        if (!empty($magentoAttributes)) {
            $category = $magentoAttributes["category"];
            $magentoAttributes = $magentoAttributes["attributes"];

            $productArray = $product->toArray();
            $productArray['blank'] = '';
            $sku = '';
            if (isset($productArray['sku'])) {
                $sku = $productArray['sku'];
            }
            $attributesEmpty = [];
            foreach ($magentoAttributes as $fruugoAttribute => $magentoAttribute) {

                if ($fruugoAttribute == 'EAN' && $this->_ischeckmanufac) {
                    continue;
                } elseif ($fruugoAttribute == 'EAN' && !empty($productArray[$magentoAttribute['magento_attribute_code']])) {
                        //Code for UPC Check Digit Validation
                    $flag = $this->validateProductID($productArray[$magentoAttribute['magento_attribute_code']]);
                        if ($flag) {
                            $attributesEmpty[] = $fruugoAttribute . ' : Invalid UPC/EAN Digit';
                        }
                    }

                elseif (!isset($productArray[$magentoAttribute['magento_attribute_code']])
                    || empty($productArray[$magentoAttribute['magento_attribute_code']])
                ) {

                    if(!empty($magentoAttribute['default'])) {
                        continue;
                    }
                    if ($fruugoAttribute == "VATRate" && isset($magentoAttribute['default']) && $magentoAttribute['default'] != NULL) {
                        if($magentoAttribute['default'] == "0") {
                            continue;
                        }
                    }
                    /*if($productArray['fruugo_vat_rate'] == '0')
                    {
                         continue;
                    }*/
                    //Case 1: if the attribute value is empty, then add error message
                    if ($fruugoAttribute == "productTaxCode") {
                        //Case 1.1: if the attribute value is empty for Tax Code,
                        // then set global Tax Code for the Category from Configurations
                        $taxCode = $this->scopeConfigManager
                            ->getValue('fruugoconfiguration/productinfo_map/fruugo_taxcode_'
                                . strtolower($category));
                        if(!is_null($taxCode) && !empty($taxCode)) {
                            $product->setData('fruugo_product_taxcode', $taxCode);
                        } else {
                            $attributesEmpty["$fruugoAttribute"] = 'Required-Attribute-Empty';
                        }
                        continue;
                    }
                    if ($fruugoAttribute == "productIdentifiers/productIdentifier/productIdType") {
                        //Case 1.2: if the attribute value is empty for Product Identifier Type,
                        // then set Product Identifier Type from Configurations
                        $productIdType = $this->scopeConfigManager
                            ->getValue('fruugoconfiguration/productinfo_map/fruugo_productid_type');
                        $product->setData('fruugo_productid_type', $productIdType);
                        continue;
                    }
                    if ($fruugoAttribute == "NormalPriceWithoutVAT" || $fruugoAttribute == "NormalPriceWithVAT"
                    || $fruugoAttribute == "DiscountPriceWithoutVAT" || $fruugoAttribute == "DiscountPriceWithVAT") {
                        //Case 1.1: if the attribute value is empty for Tax Code,
                        // then set global Tax Code for the Category from Configurations
                        $profileCode = $this->_profile[$this->currentProfileId]['profile_code'];
                        $usePrice = (int) $this->scopeConfigManager
                            ->getValue("fruugoconfiguration/$profileCode/use_vat_price");
                        if( $usePrice == 0 && ( $fruugoAttribute == 'NormalPriceWithVAT' || $fruugoAttribute == 'DiscountPriceWithVAT' ) ) {
                            continue;
                        } else if($usePrice == 1 && ( $fruugoAttribute == 'NormalPriceWithoutVAT' || $fruugoAttribute == 'DiscountPriceWithoutVAT' )) {
                            continue;
                        }
                    }
                    $attributesEmpty["$fruugoAttribute"] = 'Required-Attribute-Empty';

                } elseif (isset($magentoAttribute['fruugo_attribute_type'])) {
                    //Case 2: if the attribute value is set, then max length check
                    $maxlength = explode(',', $magentoAttribute['fruugo_attribute_type']);
                    if (isset($maxlength[1]) &&
                        strlen($productArray[$magentoAttribute['magento_attribute_code']]) > $maxlength[1]
                    ) {
                        $length = strlen(htmlspecialchars($productArray[$magentoAttribute['magento_attribute_code']]));
                        $attributesEmpty["$fruugoAttribute"] = $length . ' MaxLength-' . $maxlength[1] . '-Exceded';
                    }

                }

            }
            $productImages = array();
            if(isset($parentProductId) && $parentProductId) {
                $useConfigImage = $this->scopeConfigManager->getValue('fruugoconfiguration/product_edit/fruugo_config_image');
                if($useConfigImage == "1") {
                    $productImages = $configProduct->getMediaGalleryImages();
                } else {
                    $productImages = $product->getMediaGalleryImages();
                }
            } else {
                $productImages = $product->getMediaGalleryImages();
            }
            $imgKey = 1;
            $Imagerror = false;
            foreach ($productImages as $image) {
                if(!empty($image->getUrl()) && $imgKey < 6) {
                    $Imagerror = true;

                        $imgKey++;
                }
            }
            if($Imagerror === false) {
                $attributesEmpty["Image"] = 'One image must be above 400 x 400';
            }

            //Setting Errors in product validation attribute
            if (count($attributesEmpty) > 0) {
                $attributesEmpty = [
                    "sku" => "$sku",
                    "id" => "$id",
                    "url" => "$id",
                    "errors" => $attributesEmpty
                ];

                $validatedProduct['errors'] = $attributesEmpty;
                $attributesEmpty = $this->json->jsonEncode([$attributesEmpty]);
                $product->setData('fruugo_validation_errors', $attributesEmpty);
                $product->setData('fruugo_product_validation', 'Invalid');
            } else {
                $product->setData('fruugo_product_validation', 'Valid');
                $product->setData('fruugo_validation_errors', ' ');
                $validatedProduct['id'] = $id;
                $validatedProduct['category'] = $category;
            }

            $validatedProduct['status'] = 'UNPUBLISHED';
            if ($statusCheck) {
                //Checking current Fruugo Product Status by hitting the get Item API
                $validatedProduct['status'] = $this->getItem($product->getSku(), 'publishedStatus');
                $product->setData('fruugo_product_status', $validatedProduct['status']);
            }
            $product->getResource()->saveAttribute($product,'fruugo_product_status')
                ->saveAttribute($product,'fruugo_product_validation')
                ->saveAttribute($product,'fruugo_validation_errors');
            return $validatedProduct;
        }

        //Case 2: Category Not Mapped
        $sku = $product->getSku();
        $attributesEmpty = [
            "sku" => "$sku",
            "id" => "$id",
            "url" => "$id",
            "errors" =>
                [
                    "Category Not Mapped" => "Product's Magento Category is not Mapped with any Fruugo Category"
                ]
        ];
        $validatedProduct['errors'] = $attributesEmpty;
        $attributesEmpty = $this->json->jsonEncode([$attributesEmpty]);
        $product->setData('fruugo_product_validation', $attributesEmpty);
        $product->getResource()->saveAttribute($product,'fruugo_product_validation');

        return $validatedProduct;
    }

    /**
     * Function Product Id Validate
     */
    public function validateProductID($productID) {
        if (preg_match('/[^0-9]/', $productID))
        {
            // is not numeric
            return false;
        }
        // pad with zeros to lengthen to 14 digits
        switch (strlen($productID))
        {
            case 8:
                $productID = "000000".$productID;
                break;
            case 12:
                $productID = "00".$productID;
                break;
            case 13:
                $productID = "0".$productID;
                break;
            case 14:
                break;
            default:
                // wrong number of digits
                return false;
        }
        // calculate check digit
        $a = [];
        $a[0] = (int)($productID[0]) * 3;
        $a[1] = (int)($productID[1]);
        $a[2] = (int)($productID[2]) * 3;
        $a[3] = (int)($productID[3]);
        $a[4] = (int)($productID[4]) * 3;
        $a[5] = (int)($productID[5]);
        $a[6] = (int)($productID[6]) * 3;
        $a[7] = (int)($productID[7]);
        $a[8] = (int)($productID[8]) * 3;
        $a[9] = (int)($productID[9]);
        $a[10] = (int)($productID[10]) * 3;
        $a[11] = (int)($productID[11]);
        $a[12] = (int)($productID[12]) * 3;
        $sum = $a[0] + $a[1] + $a[2] + $a[3] + $a[4] + $a[5] + $a[6] + $a[7] + $a[8] + $a[9] + $a[10] + $a[11] + $a[12];
        $check = (10 - ($sum % 10)) % 10;
        // evaluate check digit
        $last = (int)($productID[13]);
        return $check == $last;
    }

    /**
     * Prepare Additional Assets
     * @param Object $productImages
     * @return string|[]
     */
    public function prepareAdditionalAssets($productImages)
    {
        if ($productImages->getSize() > 0) {
            $additionalAssets = [
                '_attribute' => [],

            ];
            $count = 0;
            foreach ($productImages as $image) {
                $additionalAssets['_value'][$count] = [
                    'additionalAsset' => [
                        'assetUrl' =>  str_replace('https','http',$image->getUrl()),
                    ],
                ];
                $count += 1;
            }
            return $additionalAssets;
        }
        return [];
    }

    /**
     * Get Fruugo Category for product id
     * @param integer $productId
     * @return array|boolean
     */
    public function getFruugoCategoryDepre($productId, $product = null)
    {
        if ($productId != null) {
            if ($product == null) {
                $product = $this->objectManager->create('Magento\Catalog\Model\Product')
                    ->load($productId)
                    ->setStoreId($this->selectedStore);
            }
            $catId = $product->getCategoryIds();
            foreach ($catId as $k => $id) {
                if ($id == 2 || $id == 0) {
                    unset($catId[$k]);
                } else {
                    $fruugoCatId = $this->objectManager->create('\Ced\Fruugo\Model\ResourceModel\Categories\Collection')
                        ->addFieldToFilter('magento_cat_id', [['like' => '%,'.$id.',%']])->getFirstItem();
                    if(!empty($fruugoCatId->getData())) {
                        return ($fruugoCatId->getData());
                    }
                }
            }

//            return ['fruugo_required_attributes' => 'sku,productName,longDescription,'.
//                'shelfDescription,shortDescription,mainImage/mainImageUrl,'.
//                'productIdentifiers/productIdentifier/productIdType,productIdentifiers/productIdentifier/productId,'.
//                'productTaxCode,brand,price/amount,shippingWeight/value',
//                'fruugo_attributes' => 'sku,productName,longDescription,shelfDescription,shortDescription,'.
//                    'mainImage/mainImageUrl,productIdentifiers/productIdentifier/productIdType,'.
//                    'productIdentifiers/productIdentifier/productId,'.
//                    'productTaxCode,brand,price/amount,shippingWeight/value'
//            ];
        }
        return false;
    }



    /**
     * Get Fruugo Attributes
     * @param integer $productId
     * @param [] $params
     * @return array|bool
     */
    public function getFruugoAttributes($productId, $params =
    ['required' => true, 'mapped' => false, 'validation' => false], $product=null
    ) {
        // load current product profile
        $this->getCurrentProfile($productId);
        $profile = $this->_profile[$this->currentProfileId];

        $attributes = $catData =[];
        $attributes = [];
        //Case 1 when required param is true and other false
        if(isset($params['required'], $params['mapped'], $params['validation'])){
            switch ($params){
                case $params['required'] == true && $params['mapped'] == true && $params['validation'] == true:
                {
                    $attributes = [];
                    if(isset($profile['profile_attribute_mapping']['required_attributes']))
                        foreach ($profile['profile_attribute_mapping']['required_attributes'] as $value) {
                            $attributes[$value['fruugo_attribute_name']] = $value;
                        }

                    return ["attributes" => $attributes, "category" => $profile['profile_category_level_1']];
                }
                case $params['required'] == false && $params['mapped'] == true && $params['validation'] == true:
                {

                    $attributes = [];
                    if(isset($profile['profile_attribute_mapping']['required_attributes']))
                        $attributes = $profile['profile_attribute_mapping']['required_attributes'];
                    /*foreach ($profile['profile_attribute_mapping']['required_attributes'] as $value) {
                        $attributes[$value['fruugo_attribute_name']] = $value;
                    }*/
                    if(isset($profile['profile_attribute_mapping']['optional_attributes']))
                        $attributes = array_merge_recursive($attributes,$profile['profile_attribute_mapping']['optional_attributes']);
                    /*foreach ($profile['profile_attribute_mapping']['optional_attributes'] as $value) {
                        $attributes[$value['fruugo_attribute_name']] = $value;
                    }*/
                    return ["attributes" => $attributes, "category"=> [ "parent_cat_id" => $profile['profile_category_level_1']/*,  "cat_id" => $profile['profile_category_level_2']*/]];
                }
                case $params['required'] == false && $params['mapped'] == true && $params['validation'] == false:
                {

                    $attributes = [];
                    if(isset($profile['profile_attribute_mapping']['required_attributes']))
                        foreach ($profile['profile_attribute_mapping']['required_attributes'] as $value) {
                            $attributes[$value['fruugo_attribute_name']] = $value['magento_attribute_code'];
                        }
                    if(isset($profile['profile_attribute_mapping']['optional_attributes']))
                        foreach ($profile['profile_attribute_mapping']['optional_attributes'] as $value) {
                            $attributes[$value['fruugo_attribute_name']] = $value['magento_attribute_code'];
                        }
                    return ["attributes" => $attributes, "category"=> [ "parent_cat_id" => $profile['profile_category_level_1']/*,  "cat_id" => $profile['profile_category_level_2']*/]];
                }
            }
        }




        /* die("dont upload product");

         if ($productId) {
             if ($product == null) {
                 $product = $this->objectManager->create('Magento\Catalog\Model\Product')
                     ->load($productId)
                     ->setStoreId($this->selectedStore);
             }
             $catData = $this->getFruugoCategory($productId, $product);
             if ($catData) {
                 $fruugoAttributes = explode(',', $catData['fruugo_required_attributes']);
                 if (isset($params['required']) && $params['required'] == false && !empty($catData)) {
                     $fruugoAttributes = array_merge($fruugoAttributes, explode(',', $catData['fruugo_attributes']));
                 }
                 $attributes = [];
                 if ($params['mapped'] == true) {
                     $magentoAttributes = $this->objectManager
                         ->create('\Ced\Fruugo\Model\ResourceModel\Attributes\Collection')
                         ->addFieldToSelect(['fruugo_attribute_name', 'magento_attribute_code', 'fruugo_attribute_type'])
                         ->addFieldToFilter('fruugo_attribute_name', ['in' => $fruugoAttributes])
                         ->addFieldToFilter('magento_attribute_code', ['notnull' => true]);
                     if ($params['validation'] == true) { //validation true
                         foreach ($magentoAttributes as $value) {
                             $attributes[$value['fruugo_attribute_name']] = [
                                 'magento_attribute_code' => $value['magento_attribute_code'],
                                 'fruugo_attribute_type' => isset($value['fruugo_attribute_type']) ?
                                     $value['fruugo_attribute_type'] : 'string',
                             ];
                         }
                         foreach ($fruugoAttributes as $attribute) {
                             if (!isset($attributes[$attribute])) {
                                 $attributes[$attribute] = [
                                     'magento_attribute_code' => 'blank',
                                     'fruugo_attribute_type' => 'string'
                                 ];
                             }
                         }
                     } else {// when validation false
                         foreach ($magentoAttributes as $value) {
                             $attributes[$value['fruugo_attribute_name']] = $value['magento_attribute_code'];
                         }
                         foreach ($fruugoAttributes as $attribute) {
                             if (!isset($attributes[$attribute])) {
                                 $attributes[$attribute] = [
                                     'magento_attribute_code' => 'blank',
                                     'fruugo_attribute_type' => 'string'
                                 ];
                             }
                         }
                     }
                 }

                 return ["attributes" => $attributes, "category" => $catData['parent_cat_id']];
             }
         }*/
        return false;
    }

    /**
     * Response Parse and Save to db
     * $response =
     *'<ns2:FeedAcknowledgement xmlns:ns2="http://fruugo.com/">
     * <ns2:feedId>F34AC08BE61843E59739C665C5761D0C@AQMB_wA</ns2:feedId></ns2:FeedAcknowledgement>';
     * @param string $response
     * @param string $type
     * @param string $filePath
     * @return string|[]
     */
    public function responseParse($response = '', $type = null, $filePath = '', $successItem = null )
    {
        if ($type) {
            $data = '';
            $feedModel = $this->objectManager->create('\Ced\Fruugo\Model\Feeds');
            //$data = str_replace('ns2:', "", $response);
            $parser = $this->objectManager->create('\Magento\Framework\Xml\Parser');
            try {
                if($response == NULL) {
                    $itemReceived = count($this->session->getFruugoProducts());
                    $itemFailed  = 0;
                } elseif(!is_array($response)) {
                    $data = $parser->loadXML($response)->xmlToArray();
                } else {
                    $data = $response;
                    $itemReceived = count($this->session->getFruugoProducts());
                    $itemFailed  = 0;
                    foreach ($data as $error){
                        $itemFailed += count($error);
                    }
                    //$itemSuccess = $data['successItem'];
                }
                $feedModel->setData('feed_id', rand());
                $feedModel->setData('feed_status', 'Error');
                $feedModel->setData('feed_date', date( 'Y-m-d H:i:s'));
                $feedModel->setData('feed_type', $type);
                $feedModel->setData('items_received', $itemReceived);
                $feedModel->setData('items_succeeded', $successItem);
                $feedModel->setData('items_failed', $itemFailed);
                $feedModel->setData('feed_errors', $this->json->jsonEncode($data));
                /*$feedModel->setData('feed_id', $data['FeedAcknowledgement']['feedId']);
                $feedModel->setData('feed_date', date('Y-m-d H:i:s'));
                $feedModel->setData('feed_status', "UPLOADED");
                $feed = $this->getFeeds($data['FeedAcknowledgement']['feedId']);
                if (isset($feed['results']) && $feed['totalResults'] != 0) {
                    $feed = $feed['results']['0'];
                    if (!empty($feedModel->load($feed['feedId'], 'feed_id')->getData())) {
                        $feedModel->load($feed['feedId'], 'feed_id');
                    } else {
                        $feedModel->setData('feed_id', $feed['feedId']);
                    }
                    $arr = [
                        'feedStatus' => 'feed_status',
                        'itemsReceived' => 'items_received',
                        'itemsSucceeded' => 'items_succeeded',
                        'itemsFailed' => 'items_failed',
                        'itemsProcessing' => 'items_processing',
                        'feedDate' => 'feed_date',
                        'feedType' => 'feed_type',
                        'feedSource' => 'feed_source',
                    ];

                    foreach ($arr as $key => $value) {
                        if (isset($feed[$key])) {
                            if ($key == 'feedDate') {
                                $feedModel->setData('feed_date',
                                    date( 'Y-m-d H:i:s', substr($feed['feedDate'], 0, 10)));
                            } else {
                                $feedModel->setData($value, $feed[$key]);
                            }
                        }
                    }
                }*/
                $feedModel->setData('feed_file', $filePath);
                $feedModel->save();
            }
            catch (\Exception $e) {
                if ($this->debugMode) {
                    $this->fruugoLogger->
                    logger(
                        $type,
                        "responseParse",
                        $e->getMessage(),
                        "Parse  response error Exception"
                    );
                }
            }

        }
        return true;
    }

    /**
     * To Convert Escaped Characters in XML to HTML chars
     * @param string $path
     * @return bool
     */
    public function unEscapeData($path)
    {
        if ($this->fileIo->fileExists($path)) {
            $data = $this->fileIo->read($path);
            $data = htmlspecialchars_decode($data);
            //$data = preg_replace( "/\r|\n|\t/", "", $data );
            $this->fileIo->write($path, $data);
        }
        return false;
    }

    /**
     * Prepare Additional Attributes for Variations
     * @param $attributes
     * @param $product
     * @return array|bool
     */
    public function prepareAdditionalAttributes($attributes, $additionalAttributes, $product)
    {
        if (count($attributes) > 0) {
            /*if (!isset($additionalAttributes['_value'])) {
                $additionalAttributes = [
                    '_attribute' => [],
                    '_value' => []
                ];
            }*/
            foreach ($attributes as $attribute) {
                $attr = $product->getResource()->getAttribute($attribute['magento_attribute_code']);
                if ($attr && ($attr->usesSource() || $attr->getData('frontend_input')=='select')) {
                    $productAttributeValue =
                        $attr->getSource()->getOptionText($product->getData($attribute['magento_attribute_code']));
                    if ($productAttributeValue == 'No') {
                        $productAttributeValue = 'false';
                    } elseif ($productAttributeValue == 'Yes') {
                        $productAttributeValue = 'true';
                    }
                }
                $additionalAttributes[$attribute['fruugo_attribute_name']] = $productAttributeValue;
            }
        }
        return $additionalAttributes;
    }


    /**
     * Prepare Optional Attributes for Variations
     * @param $attributes
     * @param $product
     * @return array|bool
     */
    public function prepareOptionalAttributes($attributes, $product, $profileCode = null)
    {
        $optionalAttributes = [];
        if (count($attributes) > 0) {
            
            /*if (!isset($additionalAttributes['_value'])) {
                $additionalAttributes = [
                    '_attribute' => [],
                    '_value' => []
                ];
            }*/
            $usePrice = (int) $this->scopeConfigManager
                ->getValue("fruugoconfiguration/$profileCode/use_vat_price");//die('fg');
            foreach ($attributes as $attribute) {
                $attr = $product->getResource()->getAttribute($attribute['magento_attribute_code']);
                if ($attr && ($attr->usesSource() || $attr->getData('frontend_input')=='select')) {
                    $productAttributeValue =
                        $attr->getSource()->getOptionText($product->getData($attribute['magento_attribute_code']));
                    if ($productAttributeValue == 'No') {
                        $productAttributeValue = 'false';
                    } elseif ($productAttributeValue == 'Yes') {
                        $productAttributeValue = 'true';
                    }
                } else {
                    $productAttributeValue = $product->getData($attribute['magento_attribute_code']);
                }
                if($attribute['fruugo_attribute_name'] == 'RestockDate')
                {
                    $productAttributeValueArr = explode(' ', $productAttributeValue);
                    $productAttributeValue = $productAttributeValueArr[0];
                }
                if(($attribute['fruugo_attribute_name'] == 'DiscountPriceStartDate'
                    || $attribute['fruugo_attribute_name'] == 'DiscountPriceEndDate') && $productAttributeValue!= NULL) {
                    $productAttributeValueArr = explode(' ', $productAttributeValue);
                    $productAttributeValue = $productAttributeValueArr[0];
                    $optionalAttributes['Price'][$attribute['fruugo_attribute_name']] = $productAttributeValue;
                    continue;
                }

                if( ( $attribute['fruugo_attribute_name'] == 'DiscountPriceWithoutVAT'
                    && $usePrice == 1 ) || ( $attribute['fruugo_attribute_name'] == 'DiscountPriceWithVAT'
                        && $usePrice == 0 ) ) {
                    //die('dgf');
                    continue;
                }
                if( $attribute['fruugo_attribute_name'] == 'PackageWeight'){
                    $tes_weight = (float)$productAttributeValue;
                    $weightUnit = $this->scopeConfigManager->getValue(
                        'general/locale/weight_unit',\Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    );
                    if($weightUnit == 'lbs'){
                        $tes_weight = (int)($tes_weight*453.592);
                    }elseif($weightUnit == 'kgs'){
                        $tes_weight = $tes_weight*1000;
                    }
                    $productAttributeValue = (string)$tes_weight;
                }
                if( $attribute['fruugo_attribute_name'] == 'DiscountPriceWithVAT' && $productAttributeValue!= NULL) {
                    $productAttributeValue = (string)round($productAttributeValue, 2);
                    $optionalAttributes['Price'][$attribute['fruugo_attribute_name']] = $productAttributeValue;
                    continue;
                }
                if( $attribute['fruugo_attribute_name'] == 'DiscountPriceWithoutVAT' && $productAttributeValue!= NULL) {
                    $productAttributeValue = (string)round($productAttributeValue, 2);
                    $optionalAttributes['Price'][$attribute['fruugo_attribute_name']] = $productAttributeValue;
                    continue;
                }
                if($attribute['fruugo_attribute_name']=='Country'){
                   $productAttributeValue = $product->getData($attribute['magento_attribute_code']);
                    $productAttributeValue = str_replace(',', ' ', $productAttributeValue);
                    $optionalAttributes['Price'][$attribute['fruugo_attribute_name']] = $productAttributeValue;
                    continue;
                }
                if($productAttributeValue != '' || !empty($productAttributeValue) || $productAttributeValue != NULL)
                    $optionalAttributes[$attribute['fruugo_attribute_name']] = $productAttributeValue;
            }
        }
        return $optionalAttributes;
    }


    /**
     * Prepare Shipping Overrides
     * @param $product
     * @return array
     */
    public function prepareShippingOverrides($product)
    {
        $shippingOverrides = [];



//        $isEnabled = $this->scopeConfigManager->getValue
//        ('fruugoconfiguration/shipping_overides/fruugo_product_shipping');// @todo need to overwrite

        //$isEnabled = $this->getConfigData($this->pcode, 'fruugoconfiguration/shipping_overides/fruugo_product_shipping');

        //   if ($isEnabled) {
        $shippingRules = $this->getConfigData($this->pcode, 'fruugoconfiguration/productinfo_map/shipping_override_rule');

        $shippingRules = json_decode($shippingRules,true);

        $shipOverRide = [];
        foreach($shippingRules as $overRide) {
            $isShippingAllowed = 'true';
            if($overRide['enabled'] == 'false') {
                $isShippingAllowed = 'false';
            }
            if($overRide['method'] == "VALUE")
                $shipCharges = "0.00";
            else{
                $attributeCode = $overRide['magento_attribute_code'];
                $shipCharges = (float)$product->getData($attributeCode);
            }

            $shipOverRide['_attribute'] = array();
            $shipOverRide['_value'][] = array(
                'shippingOverride' => array (
                    'isShippingAllowed'=> $isShippingAllowed,
                    'shipRegion' => $overRide['region'],
                    'shipMethod' => $overRide['method'],
                    'shipPrice' => "$shipCharges"
                )
            );
        }
        
        return $shipOverRide;




        // }
    }
    /**
     * Prepare Shipping Overrides
     * @param $product
     * @return array
     */
    public function checkForConfiguration()
    {
        return true;
        $apiFlag = $this->scopeConfigManager->getValue('');
        $validationFlag = $this->scopeConfigManager->getValue('fruugoconfiguration/fruugosetting/validate_details');
        if($apiFlag && $validationFlag) {
            return true;
        }
        return false;
    }


    /**
     * Check For Licence
     * @return boolean
     */
    public function checkForLicence()
    {
        if($this->_request->getModuleName() != 'fruugo') {
            return $this;
        }
        $helper = $this->objectManager->create('Ced\Fruugo\Helper\Feed');
        $modules = $helper->getCedCommerceExtensions();
        foreach ($modules as $moduleName=>$releaseVersion)
        {

            $m = strtolower($moduleName); if(!preg_match('/ced/i',$m)){ return $this; }

            $h = $this->getStoreConfig(\Ced\Fruugo\Block\Extensions::HASH_PATH_PREFIX.$m.'_hash');

            for($i=1;$i<=(int)$this->getStoreConfig(\Ced\Fruugo\Block\Extensions::HASH_PATH_PREFIX.$m.'_level');$i++)
            {
                $h = base64_decode($h);
            }

            $h = json_decode($h,true);
            if(is_array($h) && isset($h['domain']) && isset($h['module_name']) && isset($h['license']) && strtolower($h['module_name']) == $m && $h['license'] == $this->getStoreConfig(\Ced\Fruugo\Block\Extensions::HASH_PATH_PREFIX.$m)) {
                return $this;
            }else{
                return false;
            }
        }
        return $this;
    }


    /**
     * Get Request on https://marketplace.fruugoapis.com/
     * @param string $url
     * @param string|[] $params
     * @return string
     */
    public function validateCredentials($url)
    {
        try{
            //$signature = $this->apiSignature->getSignature($url, 'GET');
            //$url = $this->apiUrl . $url;
            /* $timestamp =  $this->apiSignature->timestamp;
             $consumerId = $this->apiConsumerId;
             $this->resource->setConfig(['header' => 0]);
             if (isset($params['timestamp'],$params['signature'],$params['consumer_id'])) {
                 $timestamp = $params['timestamp'];
                 $signature = $params['signature'];
                 $consumerId = $params['consumer_id'];
                 $url = $params['url'];
                 $this->resource->setConfig(['header' => 1]);
             }
             $headers = [];
             $headers[] = "WM_SVC.NAME: Fruugo Marketplace";
             $headers[] = "WM_QOS.CORRELATION_ID: " . base64_encode(\phpseclib\Crypt\Random::string(16));
             $headers[] = "WM_SEC.TIMESTAMP: " . $timestamp;
             $headers[] = "WM_SEC.AUTH_SIGNATURE: " . $signature;
             $headers[] = "WM_CONSUMER.ID: " .   $consumerId;
             $headers[] = "Content-Type: application/json";
             $headers[] = "Accept: application/xml";
             if (isset($params['headers']) && !empty($params['headers'])) {
                 $headers[] = $params['headers'];
             }
             $headers[] = "HOST: marketplace.fruugoapis.com";

             $this->resource->setOptions([CURLOPT_HEADER => 1, CURLOPT_RETURNTRANSFER=>'true' ]);
             //for curl https use install certificate, add certificate location to php.ini
             $this->resource->setOptions([CURLOPT_HEADER => 1, CURLOPT_RETURNTRANSFER=>'true']);
             $this->resource->write("GET", $url, '1.1', $headers);
             $serverOutput = $this->resource->read();*/
            $url = $this->apiUrl . $url;
            $username = $this->apiUserName;
            $password = $this->apiUserPassword;
            $headers = array();
            $headers[] = "Content-Type: application/x-www-form-urlencoded";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
            $serverOutput = curl_exec($ch);
            //echo "<pre>";print_r($serverOutput);die('fg');
            $CURLINFO_HTTP_CODE = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            /*$header = substr($serverOutput, 0, $header_size);
            $body = substr($serverOutput, $header_size);*/
            curl_close($ch);
            //var_dump($CURLINFO_HTTP_CODE);die('hg');

            /*if (!$serverOutput) {
                return false;
            }*/
            /*$this->resource->close();*/
            if($CURLINFO_HTTP_CODE == 200) {
                return true;
            } else {
                return false;
            }
            //return $serverOutput;
            //echo "<pre>";print_r($serverOutput);die('dfg');

        } catch(\Exception $e) {
            if($this->debugMode)
                $this->fruugoLogger->logger(
                    'Get Request',
                    'Exception In Function',
                    $e->getMessage(),
                    'fruugo->Helper->Data.php : GetRequest()'
                );
            return false;
        }
    }
    public function getStoreConfig($path,$storeId=null)
    {

        $store=$this->_storeManager->getStore($storeId);
        return $this->scopeConfigManager->getValue($path, 'store', $store->getCode());
    }


    /**
     * Register domain
     */
    public function registerDomain($domain)
    {
        $data =  array('domain'=>$domain,'email'=>'','framework'=>'Magento2');
        $url = 'https://admin.apps.cedcommerce.com/magento-fruugo-info/create?'.http_build_query($data);
        /*$headers = array();
        $headers[] = "Content-Type: application/json";*/
        //echo $url;die('gh');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        /*curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);*/
        $serverOutput = curl_exec($ch);
        //var_dump($serverOutput);die('ghdf');
        /*$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($serverOutput, 0, $header_size);
        $body = substr($serverOutput, $header_size);*/
        curl_close($ch);
        return $serverOutput;
    }


    public function getTotalStock() {
        $response = $this->getRequest('stockstatus-api');
        $this->xml = $this->objectManager->create('Ced\Fruugo\Helper\Custom\Generator');
        $response = $this->xml->loadXML($response)->xmlToArray();
        $totalSkus = isset( $response['skus']['sku'] ) ? count($response['skus']['sku']) : 0;
        $prodStatus = array();
        if(isset($response['skus']))
        {
            $totalLiveSkus = count(array_filter($response['skus']['sku'], function ($sku) {
                return $sku['_value']['availability'] == 'INSTOCK';
            }));
            $prodStatus = [
                'liveSkus' => $totalLiveSkus,
                'uploadedSkus' => $totalSkus
            ];
        }

        return $prodStatus;
    }

    

}






