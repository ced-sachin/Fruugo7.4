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
        try{
            $purchaseOrderId = false;
            $shipment = $observer->getEvent()->getShipment();
            //$track = $observer->getEvent()->getTrack();
            //$shipment = $track->getShipment();
            $order = $shipment->getOrder();
            $trackArray = array();
            foreach ($shipment->getAllTracks() as  $track) {
                $trackArray = $track->getData();break;
            }
            $incrementId = $order->getIncrementId();
            $fruugoOrder = $this->objectManager->get('Ced\Fruugo\Model\FruugoOrders')
                ->load($incrementId,'magento_order_id');
            $purchaseOrderId =$fruugoOrder->getPurchaseOrderId();
            if(empty($purchaseOrderId))
            {
                return $observer;
            }
        }catch(\Exception $e)
        {
            return $observer;
        }

        if($purchaseOrderId)
        {
            $this->_logger->logger('Shipment-'.$fruugoOrder->getPurchaseOrderId(),'Magento Order Id', "$incrementId");
            $order_data =  isset($fruugoOrder['order_data'])?json_decode($fruugoOrder['order_data'],true):false;
            $this->_logger->logger('Shipment-'.$fruugoOrder->getPurchaseOrderId(),'Order Data', var_export($order_data,true));
            $this->_logger->logger('Shipment-'.$fruugoOrder->getPurchaseOrderId(),'Track Data', var_export($trackArray,true));
            $tracking = $trackingUrl = '';

            if(isset($trackArray['track_number']))
                $tracking = (string)$trackArray['track_number'];

            $shipStationcarrier = '';
            if(isset($trackArray['carrier_code']))
                $shipStationcarrier = $trackArray['carrier_code'];
            if(strtoupper($shipStationcarrier) == 'DHL') {
                $trackingUrl = 'https://webtrack.dhlglobalmail.com';
            }
            $trackingUrl = 'https://webtrack.dhlglobalmail.com/?trackingnumber='.$tracking;
            $orderToComplete = NULL;
            $orderCancel = NULL;
            $mixed = NULL;
            $cancelArray = array();
            $shipmentArray = array();
            $dataShip = array();
            $i = 0;
            $orderItemsData = isset($order_data['o:orderLines']['o:orderLine']) ?
                $order_data['o:orderLines']['o:orderLine'] : NULL;
            $orderItems = isset($order_data['o:orderLines']['o:orderLine']) ?
                array_column($order_data['o:orderLines']['o:orderLine'], 'o:skuId') : NULL;
            foreach ($order->getAllVisibleItems() as $item) {
                $merchantSku = $item->getSku();
                $fruugoItemIndex = array_search($merchantSku, $orderItems);
                $quantityOrdered = $item->getQtyOrdered();
                $k = 0;
                $time = time() + ($k + 1);
                $shpId = implode("-", str_split($time, 3));
                $orderToComplete[$merchantSku] = 'complete';
                // case 1 complete_order
                if(isset($orderItemsData[$fruugoItemIndex]['o:fruugoProductId'])) {
                    $shipmentArray [$i] = array(
                        'lineNumber' => $i,
                        'shipment_item_id' => $shpId,
                        'merchant_sku' => $merchantSku,
                        'response_shipment_sku_quantity' => intval($quantityOrdered),
                        'fruugo_prodID' => isset($orderItemsData[$fruugoItemIndex]['o:fruugoProductId']) ? $orderItemsData[$fruugoItemIndex]['o:fruugoProductId'] : NULL,
                        'fruugo_skuID' => isset($orderItemsData[$fruugoItemIndex]['o:fruugoSkuId']) ? $orderItemsData[$fruugoItemIndex]['o:fruugoSkuId'] : NULL,
                    );
                    $i++;
                }
                $dataShip = array();
                $zip = "10001";

                $dataShip['shipments'][] = array(
                    'purchaseOrderId' => $purchaseOrderId,
                    'shipment_tracking_number' => $tracking,
                    'ship_from_zip_code' => $zip,
                    'shipment_tracking_url' => $trackingUrl,
                    'msg_to_customer' => '',
                    'msg_to_fruugo' => '',
                    'shipment_items' => $shipmentArray
                );
            }

            if ($dataShip) {
                $this->_logger->logger('Shipment-'.$fruugoOrder->getPurchaseOrderId(),'Data Ship', var_export($dataShip,true));

                $dataShip['noCallToGenerateShipment'] = '1';
                $postData =array();
                $msg = $this->objectManager->get('\Ced\Fruugo\Helper\Order')
                    ->putShipOrder($dataShip, $postData, $orderToComplete, array(),array());
                $this->_logger->logger('Shipment-'.$fruugoOrder->getPurchaseOrderId(),'Put Ship Response', var_export($msg,true));
                //$this->messageManager->addSuccess($msg);
            } else {
                $msg = "You have no information to Ship on Fruugo.com";
                $this->messageManager->addSuccess($msg);
            }
        }else{
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
