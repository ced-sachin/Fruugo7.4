<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Fruugo
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Fruugo\Observer;

use Magento\Framework\Event\ObserverInterface;

class Shipment implements ObserverInterface
{
    /**
     * Request
     * @var  \Magento\Framework\App\RequestInterface
     */
    public $request;

    /**
     * Object Manager
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * Registry
     * @var \Magento\Framework\Registry
     */
    public $registry;

    /**
     * Fruugo Logger
     * @var \Ced\Fruugo\Helper\FruugoLogger
     */
    public $fruugoLogger;

    /**
     * ProductSaveBefore constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\RequestInterface $request

    ) {
        $this->request = $request;
        $this->registry  = $registry;
        $this->objectManager = $objectManager;
        $this->_logger =  $this->objectManager->create('\Ced\Fruugo\Helper\FruugoLogger');
    }

    public function _construct(
        \Magento\Framework\Message\ManagerInterface $messageManager

    )
    {
        $this->messageManager = $messageManager;

    }

    /**
     * Product SKU Change event handler
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\Framework\Event\Observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        //return $observer; // disable the observer
        $shipment = $observer->getEvent()->getShipment();
        $trackArray = array();
        foreach ($shipment->getAllTracks() as  $track) {
            $trackArray = $track->getData();break;
        }
        if(!isset($trackArray['order_id']) || empty($trackArray['order_id'])) {
            return;
        }
        try{
            $this->_logger->debug('Shipment Array '.var_export($trackArray,true));
            $order = $this->objectManager->get('Magento\Sales\Model\Order')->load($trackArray['order_id']);
            $incrementId = $order->getIncrementId();
            $this->_logger->debug('Increment Id'.var_export($incrementId,true));

            //$trackArray['carrier_code'];
            //$trackArray['track_number'];
//echo $incrementId;
//die('dafas');

            $fruugoOrder = $this->objectManager->get('Ced\Fruugo\Model\FruugoOrders')
                ->load($incrementId,'magento_order_id');
            $purchaseOrderId =$fruugoOrder->getPurchaseOrderId();
            $this->_logger->debug('Increment Id'.var_export($purchaseOrderId,true));
            if(empty($purchaseOrderId))
            {
                return $observer;
            }

            if($fruugoOrder->getPurchaseOrderId())
            {

                $order_data =  isset($fruugoOrder['order_data'])?json_decode($fruugoOrder['order_data'],true):false; //get orders() depricated no need
                $this->_logger->debug('Order Data '.var_export($order_data,true));

                // after ack api change
                $order_data = isset($order_data['order'])?$order_data['order'] : $order_data;
                //after ack api end

                $methodCode = (string)$order_data['shippingInfo']['methodCode'];
                $methodCode = empty($methodCode)?'Standard':$methodCode;


                $order_data = $this->parserArray($order_data);
                $this->_logger->debug('Order Data Parser Array '.var_export($order_data,true));

                // magento 1 code reuse start

                $offset ='.0000000-00:00';
                // collect ship data
                //custom code start

                $totaltime = (string)date('m/d/Y H:i:s');
                //$date = DateTime::createFromFormat('m/d/Y H:i:s', $totaltime);

                $Date = $this->objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
                $shipTodatetime = $Date->gmtTimestamp();


                //$shipTodatetime = $date->getTimestamp();
                /*$Carr_pickdate = $shipTodatetime;
                $Exp_delivery = strtotime('+5 day', $shipTodatetime);

                $Ship_todate = date("Y-m-d", $shipTodatetime) . 'T' . date("H:i:s", $shipTodatetime) . $offset;
                $Exp_delivery = date("Y-m-d",$Exp_delivery ) . 'T' . date("H:i:s", $Exp_delivery) . $offset;
                $Carr_pickdate = date("Y-m-d", $Carr_pickdate) . 'T' . date("H:i:s", $Carr_pickdate) . $offset;*/

                // $id = $xml_data->OrderNumber;

                if(isset($trackArray['track_number']))
                    $tracking = (string)$trackArray['track_number'];



                //custom code end
                $shipToDatetime = strtotime(date('Y-m-d H:i:s'));
                /* $exptime = strtotime($this->getRequest()->getPost('exp_deliver'));*/
                $carrtime = strtotime((string)date('Y-m-d H:i:s'));
                // get time values
                $shipToDate = date("Y-m-d", $shipToDatetime) . 'T' . date("H:i:s", $shipToDatetime) . $offset;
                //$expDelivery = date("Y-m-d", $exptime) . 'T' . date("H:i:s", $exptime) . $offset;
                $carrierPickdate = date("Y-m-d", $carrtime) . 'T' . date("H:i:s", $carrtime) . $offset;


                /* $fruugo_order_row = $this->getRequest()->getPost('order_table_row');*/

                $trackingUrl = '';
                $tracking = $tracking;

                $carrierArray =
                    array(
                        'ups'=>'UPS',
                        'usps'=>'USPS',
                        'fedex'=>'FedEx'
                    );

                $shipStationcarrier = $trackArray['carrier_code'];

                $carrier = isset($carrierArray[$shipStationcarrier])?$carrierArray[$shipStationcarrier]:'other';


                $orderToComplete = NULL;
                $orderCancel = NULL;
                $mixed = NULL;
                $cancelArray = array();
                $shipmentArray = array();
                $dataShip = array();
                $i = 0;


                foreach ($order->getAllVisibleItems() as $item) {
                    $lineNumber = '';
                    $merchantSku = $item->getSku();
                    $quantityOrdered = $item->getQtyOrdered();
                    $quantityToShip = $item->getQtyShipped();

                    $quantityToCancel = 0;
                    $dayReturn = '';
                    $k = 0;
                    $time = time() + ($k + 1);
                    $shpId = implode("-", str_split($time, 3));
                    $rma = '';

                    //flag for 3 cases complete , cancel and mixed.

                    $lineNumber = isset($order_data[$merchantSku])?$order_data[$merchantSku]:'no_linenumber';



                    $orderToComplete[$merchantSku] = 'complete';
                    // case 1 complete_order
                    $shipmentArray [$i] = array(
                        'lineNumber' => $lineNumber,
                        'shipment_item_id' => $shpId,
                        'merchant_sku' => $merchantSku,
                        'response_shipment_sku_quantity' => intval($quantityToShip),
                        'RMA_number' => $rma,
                        'days_to_return' => intval($dayReturn),
                    );

                    $dataShip = array();
                    $zip = "10001";/*trim($this->scopeConfigManager->getValue('fruugoconfiguration/return_location/zip_code'));*/

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


                $this->_logger->debug('Data Ship'.var_export($dataShip,true));
                if ($dataShip) {


                    $dataShip['noCallToGenerateShipment'] = '1';
                    $postData =array();
                    $msg = $this->objectManager->get('Ced/Fruugo/Helper/Order')
                        ->putShipOrder($dataShip, $postData, $orderToComplete, array(),array());
                    $this->_logger->debug('Data Ship After Message'.var_export($msg,true));
                    $this->messageManager->addSuccess($msg);
                } else {
                    $msg = "You have no information to Ship on Fruugo.com";
                    $this->messageManager->addSuccess($msg);
                }

                //magento 1 code reuse end
            }else{

                return $observer;
            }
        } catch (\Exception $e) {
            return $observer;
        }




       

       
        return $observer;
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
