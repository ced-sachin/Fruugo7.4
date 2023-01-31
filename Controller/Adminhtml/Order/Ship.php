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
 * @category  Ced
 * @package   Ced_Fruugo
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Fruugo\Controller\Adminhtml\Order;

class Ship extends \Magento\Backend\App\Action
{
    /**
     * ResultPageFactory
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * Ship constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->scopeConfigManager = $this->_objectManager->get ( 'Magento\Framework\App\Config\ScopeConfigInterface' );

    }

    /**
     * Ship action
     * @return void
     */
    public function execute()
    {
        /*$order = $this->_objectManager->get( 'Magento\Sales\Model\Order' )->loadByIncrementId( 133 );
        $orderComplete = true;
        foreach($order->getAllItems() as $item) {
            echo "<pre>";print_r($item->getData());
            $processedOrder = (int) $item->getQtyShipped() + (int) $item->getQtyRefunded() + (int) $item->getQtyCanceled();
            if( $processedOrder < (int) $item->getQtyOrdered() ) {
                $orderComplete = false;
            }
            echo $processedOrder;
        }
        var_dump($orderComplete);
        die('fg');*/
        /*if(!$this->_objectManager->create('\Ced\Fruugo\Helper\Data')->checkForConfiguration()) {
            return $this->getResponse()->setBody( 'Fruugo API not Enabled or Invalid. Please check Fruugo Configuration' );
        }*/
        $offsetEnd = $this->getStandardOffsetUTC();
        if (empty($offsetEnd) || trim($offsetEnd) == '') {
            $offset = '.0000000-00:00';
        } else {
            $offset = '.0000000' . trim($offsetEnd);
        }
        // collect ship data

        /*$shipToDatetime = strtotime($this->getRequest()->getPost('ship_todate'));
        $exptime = strtotime($this->getRequest()->getPost('exp_deliver'));
        $carrtime = strtotime($this->getRequest()->getPost('carre_pickdate'));
        // get time values
        $shipToDate = date("Y-m-d", $shipToDatetime) . 'T' . date("H:i:s", $shipToDatetime) . $offset;
        $expDelivery = date("Y-m-d", $exptime) . 'T' . date("H:i:s", $exptime) . $offset;
        $carrierPickdate = date("Y-m-d", $carrtime) . 'T' . date("H:i:s", $carrtime) . $offset;*/

        $fruugo_order_row = $this->getRequest()->getPost('order_table_row');
        $postData = $this->getRequest()->getPost();
        $trackingUrl = $postData['tracking_url'];
        $tracking = $postData['tracking'];
        $id = $postData['key1'];
        $orderid = $postData['order'];
        /*$carrier = $postData['carrier'];
        $methodCode = $postData['methodCode'];*/
        $orderId = $postData['orderid'];
        $itemsData = $postData['items'];
        $msgToCustomer = $postData['msgtocustomer'];
        $msgToFruugo = $postData['msgtofruugo'];
        $data = json_decode($itemsData);
        if (count($itemsData) == 0) {
            $this->getResponse()->setBody("You have no item in your Order.");
            return;
        }

        $orderToComplete = NULL;
        $orderCancel = NULL;
        $mixed = NULL;
        $cancelArray = [];
        $shipmentArray = [];
        $dataShip = [];
        foreach ($data as $itemsData) {
            $lineNumber = $itemsData[5];
            $merchantSku = $itemsData[0];
            $quantityOrdered = $itemsData[1];
            $quantityToShip = $itemsData[3];
            $quantityToCancel = $itemsData[2];
            $fruugoProdId = $itemsData[6];
            $fruugoProdSku = $itemsData[7];
            $k = 0;
            $time = time() + ($k + 1);
            $shpId = implode("-", str_split($time, 3));
            //flag for 3 cases complete , cancel and mixed.
            if ($quantityOrdered == $quantityToShip && ($quantityToCancel == 0 || empty($quantityToCancel))) {
                $orderToComplete[$merchantSku] = 'complete';
                // case 1 complete_order
                $shipmentArray [] = [
                    'lineNumber' => $lineNumber,
                    'shipment_item_id' => "$shpId",
                    'merchant_sku' => $merchantSku,
                    'response_shipment_sku_quantity' => intval($quantityToShip),
                    'fruugo_prodID' => $fruugoProdId,
                    'fruugo_skuID' => $fruugoProdSku
                ];
                $uniqueRandomNumber = $id.mt_rand(10, 10000);
                $dataShip = [];
                $zip = trim($this->scopeConfigManager->getValue('fruugoconfiguration/return_location/zip_code'));

                $dataShip['shipments'][] = [
                    'purchaseOrderId' => $orderId,
                    'alt_shipment_id' => $uniqueRandomNumber,
                    'shipment_tracking_number' => "$tracking",
                    'msg_to_customer' => "$msgToCustomer",
                    'msg_to_fruugo' => "$msgToFruugo",
                    /*'response_shipment_date' => $shipToDate,
                    'response_shipment_method' => '',
                    'expected_delivery_date' => $expDelivery,*/
                    'ship_from_zip_code' => "$zip",
                    /*'carrier_pick_up_date' => $carrierPickdate,
                    'carrier' => $carrier,*/
                    'shipment_tracking_url' => $trackingUrl,
                    /*'methodCode' => $methodCode,*/
                    'shipment_items' => $shipmentArray
                ];
                continue;

            } elseif ($quantityOrdered == $quantityToCancel ) {
                // case3 cancel order
                $orderCancel[$merchantSku] = 'cancel';
                $cancelArray [] = [
                    'lineNumber' => $lineNumber,
                    'shipment_item_id' => "$shpId",
                    'merchant_sku' => $merchantSku,
                    'response_shipment_cancel_qty' => ( int ) $quantityToCancel,
                    'fruugo_prodID' => $fruugoProdId,
                    'fruugo_skuID' => $fruugoProdSku
                ];
                $uniqueRandomNumber = $id.mt_rand(10, 10000);
                $dataShip = [];
                $zip = trim($this->scopeConfigManager->getValue('fruugoconfiguration/return_location/zip_code'));
                $dataShip['shipments'][] = [
                    'purchaseOrderId' => $orderId,
                    'alt_shipment_id' => $uniqueRandomNumber,
                    'shipment_tracking_number' => "$tracking",
                    'msg_to_customer' => "$msgToCustomer",
                    'msg_to_fruugo' => "$msgToFruugo",
                    /*'response_shipment_date' => $shipToDate,
                    'response_shipment_method' => '',
                    'expected_delivery_date' => $expDelivery,*/
                    'ship_from_zip_code' => "$zip",
                    /*'carrier_pick_up_date' => $carrierPickdate,
                    'carrier' => $carrier,*/
                    'shipment_tracking_url' => $trackingUrl,
                    /*'methodCode' => $methodCode,*/
                    'shipment_items' => $shipmentArray,
                    'cancel_items' => $cancelArray
                ];
                continue;
            } elseif (/*$quantityOrdered == ($quantityToShip + $quantityToCancel) &&*/ $quantityToShip > 0 || $quantityToCancel > 0 ) {
                // case 2 mixed/complete case shipment() (this case is for multiple shipment)
                $mixed[$merchantSku] = 'mixed';
                //$lineNumbers = explode(',', $lineNumber);
                //$count = count($lineNumbers);
                //$lineNumber = array_chunk($lineNumbers, intval($quantityToShip));
                if($quantityToShip > 0)
                    $shipmentArray [] = [
                        'lineNumber' => /*implode(',', $lineNumber[0])*/$lineNumber,
                        'shipment_item_id' => "$shpId",
                        'merchant_sku' => $merchantSku,
                        'response_shipment_sku_quantity' => intval($quantityToShip),
                        'response_shipment_cancel_qty' => /*intval($quantityToCancel)*/0,
                        'fruugo_prodID' => $fruugoProdId,
                        'fruugo_skuID' => $fruugoProdSku
                    ];
                if($quantityToCancel > 0)
                $cancelArray [] = [
                    'lineNumber' => /*implode(',', $lineNumber[1])*/$lineNumber,
                    'shipment_item_id' => "$shpId",
                    'merchant_sku' => $merchantSku,
                    'response_shipment_sku_quantity' => /*intval($quantityToShip)*/0,
                    'response_shipment_cancel_qty' => intval($quantityToCancel),
                    'fruugo_prodID' => $fruugoProdId,
                    'fruugo_skuID' => $fruugoProdSku
                ];
                $uniqueRandomNumber = $id.mt_rand(10, 10000);
                $dataShip = [];
                $zip = trim($this->scopeConfigManager->getValue('fruugoconfiguration/return_location/zip_code'));
                $dataShip['shipments'][] = [
                    'purchaseOrderId' => $orderId,
                    'alt_shipment_id' => $uniqueRandomNumber,
                    'shipment_tracking_number' => "$tracking",
                    'msg_to_customer' => "$msgToCustomer",
                    'msg_to_fruugo' => "$msgToFruugo",
                    /*'response_shipment_date' => $shipToDate,
                    'response_shipment_method' => '',
                    'expected_delivery_date' => $expDelivery,*/
                    'ship_from_zip_code' => "$zip",
                    /*'carrier_pick_up_date' => $carrierPickdate,
                    'carrier' => $carrier,*/
                    'shipment_tracking_url' => $trackingUrl,
                    /*'methodCode' => $methodCode,*/
                    'shipment_items' => $shipmentArray,
                    'cancel_items' => $cancelArray
                ];
                continue;
            }


            // create function in Order Helper to use in automatic shipping also
        }
        /*foreach ($dataShip['shipments'] as $values){
            if(isset($values['cancel_items']) && !isset($values['shipment_items'])){
                $msg = $this->_objectManager->get('Ced\Fruugo\Helper\Order')->rejectOrder();
                return $msg;
            }
        }*/
        //echo "<pre>";print_r($dataShip);die('ff');
        if ($dataShip) {
            $msg = $this->_objectManager->get('Ced\Fruugo\Helper\Order')
                ->putShipOrder($dataShip, $postData, $orderToComplete, $orderCancel, $mixed);
        } else {
            $msg = "You have no information to Ship on Fruugo.com";
        }
        return $this->getResponse()->setBody( $msg );
    }

    /**
     * Get Standard Off Set UTC
     * @return string | boolean
     */
    public function getStandardOffsetUTC()
    {
        $timezone = date_default_timezone_get();
        if ($timezone == 'UTC') {
            return '';
        } else {
            $timezone =$this->_objectManager->create('Magento\Framework\Stdlib\DateTime\TimezoneInterface');
            $timezone = $timezone->getConfigTimezone();
            $transitions = array_slice($timezone->getTransitions(), -3, null, true);
            foreach (array_reverse($transitions, true) as $transition) {
                if ($transition['isdst'] == 1) {
                    continue;
                }
                return sprintf('UTC %+03d:%02u', $transition['offset'] / 3600,
                    abs($transition['offset']) % 3600 / 60);
            }
            return false;
        }
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