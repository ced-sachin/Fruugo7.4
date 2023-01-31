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
/**
 * Class Data For Fruugo Authenticated Seller Api
 * @package Ced\Fruugo\Helper
 */
class ShipmentHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
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

    /*
     * Magento\Framework\Message\Manager
     */
    public $messageManager;
    /*
     * \Magento\Sales\Api\Data\OrderInterface
     */
    public $order;

    /**
     * Data constructor.
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Sales\Api\Data\OrderInterface $order
    ) {
        parent::__construct($context);
        $this->objectManager = $objectManager;
        $this->messageManager = $messageManager;
        $this->_logger = $this->objectManager->create('\Ced\Fruugo\Helper\FruugoLogger');
        $this->order = $order;
    }

    /**
     * Shipment
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\Framework\Event\Observer
     */
    public function execute()
    {
        $fruugoOrderCollection = $this->objectManager->create('\Ced\Fruugo\Model\FruugoOrders')->getCollection()->addFieldToFilter('status' , array('in' => array('Created','Acknowledged')))->getData();
        foreach ($fruugoOrderCollection as $fruugoOrder) {
            $magentoOrderId = $fruugoOrder['magento_order_id'];
            $order = $this->order->loadByIncrementId($magentoOrderId);
            if($order->getStatus() == 'complete' || $order->getStatus() == 'Complete' ) {
                $return = $this->shipment($order,$fruugoOrder);
                if($return) {
                    $this->objectManager->create('\Ced\Fruugo\Model\FruugoOrders')->load($fruugoOrder['id'])->setStatus('Complete')->save();
                }
            }
        }
        return true;
    }

    /**
     * Shipment
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\Framework\Event\Observer
     */
    public function shipment($order = null, $fruugoOrder = null)
    {
        $trackArray = array();
        foreach($order->getShipmentsCollection() as $shipment)
        {
            $alltrackback = $shipment->getAllTracks();
            foreach ($alltrackback as $track) {
                if($track->getTrackNumber() != '') {
                    $trackArray['track_number'] = $track->getTrackNumber();
                    $trackArray['carrier_code'] =  $track->getCarrierCode();
                    break;
                }
            }
        }

        try{
            $purchaseOrderId =$fruugoOrder['purchase_order_id'];
            if(empty($purchaseOrderId))
            {
                return false;
            }
            $order_data =  isset($fruugoOrder['order_data'])?json_decode($fruugoOrder['order_data'],true):false; //get orders() depricated no need
            if(is_bool($order_data)) {
                return false;
            }
            $order_data = isset($order_data['order'])?$order_data['order'] : $order_data;
            $methodCode = (string)$order_data['shippingInfo']['methodCode'];
            $methodCode = empty($methodCode)?'Standard':$methodCode;
            $order_data = $this->parserArray($order_data);
            $offset ='.0000000-00:00';
            $tracking = (string)$trackArray['track_number'];
            $shipToDatetime = strtotime(date('Y-m-d H:i:s'));
            $carrtime = strtotime((string)date('Y-m-d H:i:s'));
            $shipToDate = date("Y-m-d", $shipToDatetime) . 'T' . date("H:i:s", $shipToDatetime) . $offset;
            $carrierPickdate = date("Y-m-d", $carrtime) . 'T' . date("H:i:s", $carrtime) . $offset;

            $trackingUrl = '';
            $carrierArray = array(
                'ups'=>'UPS',
                'usps'=>'USPS',
                'fedex'=>'FedEx'
            );

            $shipStationcarrier = $trackArray['carrier_code'];

            $carrier = isset($carrierArray[$shipStationcarrier])?$carrierArray[$shipStationcarrier]:'other';

            $orderToComplete = NULL;
            $orderCancel = NULL;
            $mixed = NULL;
            $shipmentArray = array();
            $dataShip = array();
            $i = 0;

            foreach ($order->getAllVisibleItems() as $item) {
                $merchantSku = $item->getSku();
                $quantityToShip = $item->getQtyShipped();
                $dayReturn = '';
                $k = 0;
                $time = time() + ($k + 1);
                $shpId = implode("-", str_split($time, 3));
                $rma = '';
                $lineNumber = isset($order_data[$merchantSku])?$order_data[$merchantSku]:'no_linenumber';
                $orderToComplete[$merchantSku] = 'complete';
                $shipmentArray [$i] = array(
                    'lineNumber' => $lineNumber,
                    'shipment_item_id' => $shpId,
                    'merchant_sku' => $merchantSku,
                    'response_shipment_sku_quantity' => intval($quantityToShip),
                    'RMA_number' => $rma,
                    'days_to_return' => intval($dayReturn),
                );
                $dataShip = array();
                $zip = "10001";
                $dataShip['shipments'][] = array(
                    'purchaseOrderId' => $purchaseOrderId,
                    'shipment_tracking_number' => $tracking,
                    'response_shipment_date' => $shipToDate,
                    'ship_from_zip_code' => $zip,
                    'carrier_pick_up_date' => $carrierPickdate,
                    'carrier' => $carrier,
                    'shipment_tracking_url' => $trackingUrl,
                    'methodCode' => $methodCode,
                    'shipment_items' => $shipmentArray
                );
                $i++;
            }
            if (!empty($dataShip)) {
                $dataShip['noCallToGenerateShipment'] = '1';
                $postData = array();
                $this->objectManager->create('\Ced\Fruugo\Helper\Order')
                    ->putShipOrder($dataShip, $postData, $orderToComplete, array(),array());
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return false;
    }

    /**
     * parserArray
     * {@inheritdoc}
     */
    public function parserArray($shipmentData = array())
    {

        $shipmentData = count($shipmentData)>0?$shipmentData:false;
        if(!$shipmentData)
        {
            return false;
        }
        $customArray = array();
        if (!empty($shipmentData)) {
            $arr = array();
            foreach ($shipmentData["orderLines"]['orderLine'] as $key => $value) {
                if ( in_array($key, $arr)) {
                    continue;
                }
                $count = count($shipmentData["orderLines"]['orderLine']);
                $sku = $value['item']['sku'];
                $shipQuantity = 0;
                $cancelQuantity = 0;
                $quantity = 1;
                if ($value['orderLineStatuses']['orderLineStatus'][0]['status'] == 'Shipped') {
                    $shipQuantity = 1;
                } else {
                    $cancelQuantity = 1;
                }
                $lineNumber = $value['lineNumber'];
                for ( $i = $key+1 ; $i < $count;$i++) {
                    if ($shipmentData["orderLines"]['orderLine'][$i]['item']['sku'] == $sku ) {
                        $quantity++;
                        if (
                            $shipmentData["orderLines"]['orderLine'][$i]['orderLineStatuses']
                            ['orderLineStatus'][0]['status'] == 'Shipped') {
                            $shipQuantity++;
                        } else {
                            $cancelQuantity++;
                        }
                        $lineNumber = $lineNumber.','.$shipmentData["orderLines"]['orderLine'][$i]['lineNumber'];
                        unset($shipmentData["orderLines"]['orderLine'][$i]);
                        array_push($arr, $i);
                        array_values($shipmentData["orderLines"]['orderLine']);
                    }
                }
                $shipmentData["orderLines"]['orderLine'][$key]['lineNumber'] = $lineNumber;
                $customArray[$sku] = $lineNumber;
                $shipmentData["orderLines"]['orderLine'][$key]['orderLineQuantity']['shipQuantity'] = $shipQuantity;
                $shipmentData["orderLines"]['orderLine'][$key]['orderLineQuantity']['amount'] = $quantity;
                $shipmentData["orderLines"]['orderLine'][$key]['orderLineQuantity']['cancelQuantity'] = $cancelQuantity;
            }
            return $customArray;
        }
        return false;
    }
}


