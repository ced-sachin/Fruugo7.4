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

namespace Ced\Fruugo\Controller\Adminhtml\Order;

use Magento\Framework\Data\Argument\Interpreter\Constant;

class MassShip extends \Magento\Backend\App\Action
{
    /**
     * ResultPageFactory
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * Authorization level of a basic admin session
     * @var Constant
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_Fruugo::fruugo_products_index';

    public $filter;

    public $orderManagement;

    public $order;

    /**
     * MassCancel constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Sales\Api\Data\OrderInterface $order
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->filter = $filter;
        $this->orderManagement = $orderManagement;
        $this->order = $order;
    }

    /**
     * Execute
     * @return  void
     */
    public function execute()
    {
        $dataHelper = $this->_objectManager->get('\Ced\Fruugo\Helper\ShipmentHelper');
        var_dump($dataHelper->execute());die('111');
        $collection = $this->filter->getCollection($this->_objectManager->create('\Ced\Fruugo\Model\FruugoOrders')
            ->getCollection());
        $fruugoOrders = $collection->getData();

        if (count($fruugoOrders) == 0) {
            $this->messageManager->addErrorMessage('No Orders To Ship.');
            $this->_redirect('fruugo/order/listorder');
            return;
        } else{
            $counter = 0;
            foreach ($fruugoOrders as $fruugoOrder) {
                $poId = $fruugoOrder['purchase_order_id'];
                $magentoOrderId = $fruugoOrder['magento_order_id'];
                $order = $this->order->loadByIncrementId($magentoOrderId);
                if($order->getStatus() == 'complete' || $order->getStatus() == 'Complete' ) {
                    $return = $this->shipment($order,$fruugoOrder);
                    if($return) {
                        $counter++;
                        $this->_objectManager->create('\Ced\Fruugo\Model\FruugoOrders')->load($fruugoOrder['id'])->setStatus('Complete')->save();
                    }
                }
            }
            if ($counter) {
                $this->messageManager->addSuccessMessage($counter . ' Orders Shipment Successfull to Fruugo.com');
                $this->_redirect('fruugo/order/listorder');
                return;
            } else {
                $this->messageManager->addErrorMessage('Orders Shipment Unsuccessfull.');
                $this->_redirect('fruugo/order/listorder');
                return;
            }
        }

    }

    /**
     * Shipment
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\Framework\Event\Observer
     */
    public function shipment($order = null, $fruugoOrder = null)
    {
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
                $this->_objectManager->create('\Ced\Fruugo\Helper\Order')
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


    /**
     * IsALLowed
     * @return boolean
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ced_Fruugo::Fruugo');
    }

}
