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
 * Class For Fruugo
 * @package Ced\Fruugo\Helper
 */
class Fruugo extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * Object Manager
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * Scope Manager
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfigManager;

    /**
     * Value Manager
     * @var \Magento\Framework\App\Config\ValueInterface
     */
    public $configValueManager;

    /**
     * Fruugo constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
        parent::__construct($context);
        $this->scopeConfigManager = $this->objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->configValueManager = $this->objectManager->get('Magento\Framework\App\Config\ValueInterface');
    }

    /**
     * Calculate fruugo shipment qty and cancel qty
     * @param Object $orderModelData
     * @return  array|boolean
     */
    public function getShippedCancelledQty($orderModelData)
    {
        foreach ($orderModelData as $order) {
            $shipserializedata = $order->getShipment_data();
        }
        if (isset($shipserializedata )) {
            $shipData = json_decode($shipserializedata,true);
            if (isset($shipData)? $shipData : false) {
                $shipItemsInfo = $this->shipItemInfo($shipData);
                $orderData = $this->getOrderedCancelledQty($orderModelData);
                return $shipItemsInfo;
            } else {
                $tempData = $orderModelData->getData();
                if (count($tempData) > 0) {
                    $shipData = json_decode(isset($tempData ['shipment_data']) ?
                        $tempData ['shipment_data']:false,true);
                    if (isset($shipData)?$shipData :false) {
                        $shipItemsInfo = $this->shipItemInfo($shipData);
                        return $shipItemsInfo;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    /**
     * Ship item info
     * @param array $shipData
     * @return : array
     */
    public function shipItemInfo($shipData)
    {
        $shipItemsInfo = [];
        $check = 0;
        if(isset($shipData['shipments']))
            foreach ($shipData['shipments'] as $items) {
                if(isset($items['shipment_items']) && count($items['shipment_items']) > 0)
                    foreach ($items['shipment_items']/*['orderLines']*/ as $item) {
                        /*$shipItemsInfo [$item['merchant_sku']] ['response_shipment_cancel_qty'] = 0;*/
                        $cancelQty = isset($shipItemsInfo [$item['merchant_sku']] ['response_shipment_cancel_qty']) ? $shipItemsInfo [$item['merchant_sku']] ['response_shipment_cancel_qty'] : 0;
                        $shipItemsInfo [$item['merchant_sku']] ['response_shipment_cancel_qty'] = 0 + $cancelQty;
                        $shipItemsInfo [$item['merchant_sku']] ['response_shipment_sku_quantity'] = $item['response_shipment_sku_quantity'];
                    }
                if(isset($items['cancel_items']) && count($items['cancel_items']) > 0)
                    foreach ($items['cancel_items']/*['orderLines']*/ as $item) {
                        $shipQty = isset($shipItemsInfo [$item['merchant_sku']] ['response_shipment_sku_quantity']) ? $shipItemsInfo [$item['merchant_sku']] ['response_shipment_sku_quantity'] : 0;
                        $shipItemsInfo [$item['merchant_sku']] ['response_shipment_sku_quantity'] = 0 + $shipQty;
                        $shipItemsInfo [$item['merchant_sku']] ['response_shipment_cancel_qty'] = $item['response_shipment_cancel_qty'];
                    }

            }
        return $shipItemsInfo;
    }

    /**
     * Get Order cancel qty
     * @param array $orderModelData
     * @return  array|boolean
     */
    public function getOrderedCancelledQty($orderModelData)
    {
        foreach ($orderModelData as $order) {
            $orderSerializeData = $order->getOrder_data();
        }
        if (isset($orderSerializeData)) {
            $orderData=json_decode($orderSerializeData,true);

            if (isset($orderData)) {
                $orderItemsInfo=[];
                foreach ($orderData['o:orderLines']['o:orderLine'] as $sdata) {
                    $orderItemsInfo[$sdata['o:skuId']] = 0;
                    $orderItemsInfo[$sdata['o:skuId']] = 0;
                    $orderItemsInfo[$sdata['o:skuId']] +=
                        $sdata['o:totalNumberOfItems'];
                }
                return $orderItemsInfo;
            } elseif (!$orderData) {
                $tempData = $orderModelData->getData();
                $orderData = json_decode($tempData[0]["order_data"],true);
                if (sizeof($orderData) > 0) {
                    $orderItemsInfo = [];
                    foreach ($orderData->order_items as $sdata) {
                        $orderItemsInfo[$sdata->merchant_sku]['request_sku_quantity'] = 0;
                        $orderItemsInfo[$sdata->merchant_sku]['request_cancel_qty'] = 0;
                        $orderItemsInfo[$sdata->merchant_sku]['request_sku_quantity'] +=
                            $sdata->request_order_quantity;
                    }
                    return $orderItemsInfo;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Get Refunded Qty Info
     * @param string $order
     * @param string $itemSku
     * @return array
     */
    public function getRefundedQtyInfo($order ="",$itemSku ="")
    {
        $itemSku = trim($itemSku);
        $check=[];
        $check['error']=1;
        if ($order == "") {
            $check['error_msg']="Order not found for current item.";
            return $check;
        }
        if ($itemSku=="") {
            $check['error_msg']="Item Sku not found for current item.";
            return $check;
        }
        if ($order instanceof \Magento\Sales\Model\Order) {
            $qtyOrdered=0;
            $qtyRefunded=0;

            foreach ($order->getAllItems() as $items) {
                if ($itemSku == $items->getSku()) {
                    $qtyOrdered = intval($items->getQtyOrdered());
                    $qtyRefunded = intval($items->getQtyRefunded());
                }
            }
            $availableToRefundQty = intval($qtyOrdered - $qtyRefunded);
            $check['error']=0;
            $check['qty_already_refunded'] = $qtyRefunded;
            $check['available_to_refund_qty'] = $availableToRefundQty;
            $check['qty_ordered'] = $qtyOrdered;
            return $check;
        }
        return $check;
    }

    /**
     * @return array
     */

    public function feedbackOptArray()
    {
        return [
            [
                'value' => '',
                'label' => __('Please Select an Option')
            ],
            [
                'value' => 'item damaged', 'label' =>__('item damaged')
            ],
            [
                'value' => 'not shipped in original packaging',
                'label' => __('not shipped in original packaging')
            ],
            [
                'value' => 'customer opened item',
                'label' => __('customer opened item')
            ]
        ];
    }


    /**
     * @return array
     */
    public function refundreasonOptionArr()
    {

        return [
            [
                'value' => '', 'label' => __('Please Select an Option')
            ],
            [
                'value' => 'unsatisfied_with_item', 'label' =>  __('Customer was not satisfied with the item')
            ],
            [
                'value' => 'item_did_not_match_description', 'label' =>  __('Item didn\'t match the product description')
            ],
            [
                'value' => 'damaged_item', 'label' =>  __('Item was damaged')
            ],
            [
                'value' =>'wrong_item', 'label' =>  __('Wrong item was shipped to the customer')
            ],
            [
                'value' => 'other', 'label' =>  __('Other reason')
            ]
        ];
    }
    
    /**
     * Get Fruugo Price
     * @param Object $productObject
     * @return array
     */
    public function getFruugoPrice( $productObject , $profile = null )
    {
        $helperFruugo = $this->objectManager->create('Ced\Fruugo\Helper\Data');
        if($profile === null) {
            $profile = $helperFruugo->getCurrentProfile($productObject->getId());
            $profileCode = $profile['profile_code'];
        } else {
            $profileCode = $profile['profile_code'];
        }


        $priceAttribute = "";

        $usePrice = $this->scopeConfigManager
            ->getValue("fruugoconfiguration/$profileCode/use_vat_price");
        if(isset($profile['profile_attribute_mapping']['required_attributes']) && is_array($profile['profile_attribute_mapping']['required_attributes']))
            foreach($profile['profile_attribute_mapping']['required_attributes'] as $attribute){
                if( ( $attribute['fruugo_attribute_name'] == 'NormalPriceWithVAT' && $usePrice == 1 )
                    || ( $attribute['fruugo_attribute_name'] == 'NormalPriceWithoutVAT' && $usePrice == 0 )){
                    $priceAttribute = $attribute['magento_attribute_code'];
                    break;
                }
            }
        if($priceAttribute == 'default'){
            $splprice = isset($attribute['default']) ? (float) $attribute['default'] : (float) $productObject->getFinalPrice();
            $price = isset($attribute['default']) ? (float) $attribute['default'] : (float) $productObject->getPrice();
        } elseif($priceAttribute == 'price'){
            $splprice =(float)$productObject->getFinalPrice();
            $price = (float)$productObject->getPrice();
        } elseif($priceAttribute) {
            $splprice = $price = (float)$productObject->getData($priceAttribute);
        } else {
            $splprice = $price = (float)$productObject->getData('price');
        }

        $configPrice = $this->scopeConfigManager->getValue($profileCode."/".'fruugoconfiguration/productinfo_map/fruugo_product_price');
        if(!$configPrice){
            $configPrice = $this->scopeConfigManager->getValue('fruugoconfiguration/productinfo_map/fruugo_product_price');
            $profileCode = '';
        }

        switch($configPrice) {
            case 'plus_fixed':
                $fixedPrice = trim($helperFruugo->getConfigData($profileCode,'fruugoconfiguration/productinfo_map/fruugo_fix_price'));
                $price = $this->forFixPrice($price, $fixedPrice, 'plus_fixed');
                $splprice = $this->forFixPrice($splprice, $fixedPrice, 'plus_fixed');
                break;

            case 'plus_per':
                $percentPrice = trim($helperFruugo->getConfigData($profileCode,
                    'fruugoconfiguration/productinfo_map/fruugo_percentage_price'));
                $price = $this->forPerPrice($price, $percentPrice, 'plus_per');
                $splprice = $this->forPerPrice($splprice, $percentPrice, 'plus_per');
                break;

            case 'min_fixed':
                $fixedPrice = trim($helperFruugo->getConfigData($profileCode,
                    'fruugoconfiguration/productinfo_map/fruugo_fix_price'));
                $price = $this->forFixPrice($price, $fixedPrice, 'min_fixed');
                $splprice = $this->forFixPrice($splprice, $fixedPrice, 'min_fixed');
                break;

            case 'min_per':
                $percentPrice = trim($helperFruugo->getConfigData($profileCode,
                    'fruugoconfiguration/productinfo_map/fruugo_percentage_price'));
                $price = $this->forPerPrice($price, $percentPrice, 'min_per');
                $splprice = $this->forPerPrice($splprice, $percentPrice, 'min_per');
                break;

            case 'differ':
                $customPriceAttr = trim($helperFruugo->getConfigData($profileCode,
                    'fruugoconfiguration/productinfo_map/fruugo_different_price'));
                try {
                    $cprice =(float)$productObject -> getData($customPriceAttr);
                } catch(\Exception $e) {
                    $this->_logger->debug(" Fruugo: Fruugo Helper: getFruugoPrice() : " . $e->getMessage());
                }
                $price =(isset($cprice) && $cprice != 0) ? $cprice : $price ;
                $splprice = $price;
                break;

            default:
                return [
                    'price' => (string)$price,
                    'splprice' => (string)$splprice,
                ];
        }
        return [
            'price' => (string)$price,
            'splprice' => (string)$splprice,
        ];
    }


    /**
     * ForFixPrice
     * @param null $price
     * @param null $fixedPrice
     * @param string $configPrice
     * @return float|null
     */
    public function forFixPrice($price = null, $fixedPrice = null, $configPrice)
    {
        if (is_numeric($fixedPrice) && ($fixedPrice != '')) {
            $fixedPrice =(float)$fixedPrice;
            if ($fixedPrice > 0) {
                $price= $configPrice == 'plus_fixed' ?(float)($price + $fixedPrice)
                    :(float)($price - $fixedPrice);
            }
        }
        return $price;
    }

    /**
     * ForPerPrice
     * @param null $price
     * @param null $percentPrice
     * @param string $configPrice
     * @return float|null
     */
    public function forPerPrice($price = null, $percentPrice = null, $configPrice)
    {
        if (is_numeric($percentPrice)) {
            $percentPrice =(float)$percentPrice;
            if ($percentPrice > 0) {
                $price = $configPrice == 'plus_per' ?
                    (float)($price + (($price/100)*$percentPrice))
                    :(float)($price - (($price/100)*$percentPrice));
            }
        }
        return $price;
    }


    /**
     * Get Updated Refund Quantity
     * @param string $merchantOrderId
     * @return array
     */
    public function getUpdatedRefundQty($merchantOrderId)
    {
        $refundcollection = $this->objectManager->create('Ced\Fruugo\Model\FruugoRefund')
            ->getCollection()
            ->addFieldToFilter('refund_purchaseOrderId', $merchantOrderId);
        //echo "<pre>";print_r($refundcollection->getData());die('fg');
        $refundQty = [];
        if ($refundcollection->getSize()>0) {

            foreach ($refundcollection as $coll) {
                $refundData = json_decode($coll->getData('saved_data'),true);
                foreach ($refundData['sku_details'] as $data) {
                    $refundQuantity = isset($refundQty[$data['merchant_sku']]) ? $refundQty[$data['merchant_sku']] : 0 ;
                    //echo $refundQty;die();
                    $refundQty[$data['merchant_sku']] = $refundQuantity + $data['refund_quantity'];
                }
            }
        }
        return $refundQty;
    }



    public function getackQtyData($order_model_data)
    {
        $flag = false;
        $ackData = $order_model_data->getData();
        if(count($ackData) > 0){
            $flag =  true;
            $ackData = json_decode($ackData[0]['acknowledge_data'],true);
        }
        //var_dump($ackData);
        //echo "<pre>";print_r($ackData);die('f');

        if($flag){
            $ack_items_info = array();
            //$orderData = $this->getOrdered_Cancelled_Qty($order_model_data);
            if($ackData)
            {
                foreach ($ackData["acknowledge"] as $sdata) {
                    //echo "<pre>";print_r($sdata);die('gh');
                    foreach ($sdata["acknowledged_items"] as $items) {
                        //echo "<pre>";print_r($items);die('fdg');
                        if(!isset($ack_items_info[$items['merchant_sku']]['acknowledge_qty'])){
                            $ack_items_info[$items['merchant_sku']]['acknowledge_qty'] = 0;
                        }
                        $ack_items_info[$items['merchant_sku']]['acknowledge_qty'] += $items['ack_qty'];
                        //$ack_items_info[$items['merchant_sku']]['response_shipment_sku_quantity'] += $items['response_shipment_sku_quantity'];
                    }
                }
                return $ack_items_info;
            }
            return false;


        }
    }
}

