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
namespace Ced\Fruugo\Helper;

class Order extends \Magento\Framework\App\Helper\AbstractHelper
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

    /**
     * Store Manager
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * Fruugo Orders Model
     * @var \Ced\Fruugo\Model\ResourceModel\FruugoOrders\CollectionFactory
     */
    public $fruugoOrder;

    /**
     * Customer Repository
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    public $customerRepository;

    /**
     * Product Repository
     * @var \Magento\Catalog\Model\ProductRepository
     */
    public $productRepository;

    /**
     * Message Manager
     * @var \Magento\Framework\Message\ManagerInterface
     */
    public $messageManager;

    /**
     * Catalog Product Model
     * @var \Magento\Catalog\Model\ProductFactory
     */
    public $product;

    /**
     * Customer Factory
     * @var \Magento\Customer\Model\CustomerFactory
     */
    public $customerFactory;

    /**
     * Fruugo Data Helper
     * @var \Ced\Fruugo\Helper\Data
     */
    public $datahelper;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Quote\Model\QuoteFactory $quote,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Sales\Model\Service\OrderService $orderService,
        \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoaderFactory $creditmemoLoaderFactory,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
        \Magento\Catalog\Model\ProductFactory $product,
        \Ced\Fruugo\Model\ResourceModel\FruugoOrders\CollectionFactory $fruugoOrder
    ) {
        $this->creditmemoLoaderFactory = $creditmemoLoaderFactory;
        $this->orderService = $orderService;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->cartManagementInterface = $cartManagementInterface;
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;
        $this->quote = $quote;
        $this->quoteManagement = $quoteManagement;
        $this->product = $product;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->customerFactory = $customerFactory;
        $this->fruugoOrder = $fruugoOrder;
        parent::__construct ( $context );
        $this->scopeConfigManager = $this->objectManager->get ( 'Magento\Framework\App\Config\ScopeConfigInterface' );
        $this->configValueManager = $this->objectManager->get ( 'Magento\Framework\App\Config\ValueInterface' );
        $this->messageManager = $this->objectManager->get ( 'Magento\Framework\Message\ManagerInterface' );
        $this->datahelper = $this->objectManager->get ( 'Ced\Fruugo\Helper\Data' );
    }

    /**
     * Fetch Latest Orders in ready state from Fruugo
     *
     * @return null
     */
    public function fetchLatestFruugoOrders()
    {
        if (! $this->scopeConfigManager->getValue ( 'fruugoconfiguration/fruugosetting/enable' )) {
            $this->_logger->debug('Error : Fruugo Not Enabled in Config');
            return false;
        }

        $date = $this->scopeConfigManager->getValue ( 'fruugoconfiguration/fruugosetting/orders_fetch_startdate' );

        $websiteId = $this->storeManager->getStore ()->getWebsiteId ();
        $store = $this->storeManager->getStore ();
        $this->storeManager->setCurrentStore($store);
//        $response = $this->datahelper->getOrders (['createdStartDate' => $date, 'status' =>'Created']);
        $response['elements']['order'][] = json_decode('{"purchaseOrderId":"3377712938561","customerOrderId":"2891779609246","customerEmailId":"contactingmark@gmail.com","orderDate":1484762137000,"shippingInfo":{"phone":"9542344282","estimatedDeliveryDate":1485414000000,"estimatedShipDate":1484809200000,"methodCode":"Standard","postalAddress":{"name":"Mark  Ismach","address1":"6002 Nw 73rd Ct.","address2":null,"city":"Parkland","state":"FL","postalCode":"33067","country":"USA","addressType":"RESIDENTIAL"}},"orderLines":{"orderLine":[{"lineNumber":"1","item":{"productName":"Refill Bag - Hair Building Fiber - 25 Gram Thickener","sku":"24-MB01"},"charges":{"charge":[{"chargeType":"PRODUCT","chargeName":"ItemPrice","chargeAmount":{"currency":"USD","amount":6.46},"tax":null},{"chargeType":"SHIPPING","chargeName":"Shipping","chargeAmount":{"currency":"USD","amount":4.67},"tax":null}]},"orderLineQuantity":{"unitOfMeasurement":"EACH","amount":"1"},"statusDate":1484762164000,"orderLineStatuses":{"orderLineStatus":[{"status":"Created","statusQuantity":{"unitOfMeasurement":"EACH","amount":"1"},"cancellationReason":null,"trackingInfo":null}]},"refund":null}]}}',true);
        if(isset($response['errors'])){
            $aknDate = date('Y-m-d'); // current date
            $date = strtotime($date.' -2 days'); // current date - 1mnth
            $date = date('Y-m-d', $date);
            $response = $this->datahelper->getOrders (['createdStartDate' => $date , 'status' =>'Acknowledged','limit'=>20]);
        }
        /*echo '<pre>';
        print_r($response);die;*/
        $count = 0;
        if (isset($response ['elements']['order'])) {
            foreach ( $response ['elements']['order'] as $order ) {
                $orderObject =  $order ;
                $email = isset($order['customerEmailId']) ? $order ['customerEmailId'] : 'customer@fruugo.com';
                $customer = $this->customerFactory->create()->setWebsiteId( $websiteId)->loadByEmail($email);

                if (count ( $order ) > 0 && $this->validateString ( $order ['purchaseOrderId'] )) {
                    $purchaseOrderid = $order ['purchaseOrderId'];
                    $resultdata = $this->fruugoOrder->create()
                        ->addFieldToFilter ( 'purchase_order_id', $purchaseOrderid );
                    if (! $this->validateString ( $resultdata->getData () )) {

                        $ncustomer = $this->_assignCustomer ( $order, $customer, $store, $email );

                        if (! $ncustomer) {
                            return false;
                        } else {
                            $return = $this->generateQuote ( $store, $ncustomer, $order, $orderObject );
                            if($return) {
                                $count++;
                            }

                        }
                    }
                }
            }

            if($count>0)
            {
                $model = $this->objectManager->create('\Magento\AdminNotification\Model\Inbox');
                $date = date("Y-m-d H:i:s");
                $model->setData('severity', 4);
                $model->setData('date_added', $date);
                $model->setData('title', "Incoming Fruugo Order");
                $model->setData('description', "Congratulation !! You have received ".$count." new orders for Fruugo");
                $model->setData('url', "#");
                $model->setData('is_read', 0);
                $model->setData('is_remove', 0);
                $model->save();
                $this->messageManager->addSuccessMessage($count.' Order Fetched Successfully');
            }

        }
    }

    /**
     * Validate string for null , empty and isset
     * @param string $string
     * @return boolean
     */
    public function validateString($string)
    {
        $stringValidation = (isset ( $string ) && ! empty ( $string )) ? true : false;
        return $stringValidation;
    }

    /**
     * Create Fruugo customer on Magento
     * @param array $order
     * @param array $customer
     * @param null $store
     * @param string $email
     * @return bool|\Magento\Customer\Api\Data\CustomerInterface
     */
    public function _assignCustomer($order, $customer, $store=null, $email) {
        if (! $this->validateString ( $customer->getId () )) {
            try {
                $cname = $order ['shippingInfo']['postalAddress'] ['name'];
                $customerName = explode ( ' ', $cname );
                $firstname = $customerName [0];
                unset($customerName[0]);
                $customerName = array_values($customerName) ;
                $lastname = implode(' ', $customerName);
                if (! isset ( $customerName [1] ) || $customerName [1] == '') {
                    $customerName [1] = $customerName [0];
                }
                $websiteId = $this->storeManager->getStore ()->getWebsiteId ();
                $customer = $this->customerFactory->create ();
                $customer->setWebsiteId ( $websiteId );
                $customer->setEmail ( $email );
                $customer->setFirstname ( $firstname );
                $customer->setLastname ( $lastname );
                $customer->setPassword ( "password" );
                $customer->save ();
                return $customer;
            } catch ( \Exception $e ) {
                $orderObject = json_decode ( $order );
                $message = $e->getMessage ();
                $fruugoOrderError = $this->objectManager->create( 'Ced\Fruugo\Model\FailedFruugoOrders' );
                $fruugoOrderError->setPurchaseOrderId ( $order ['purchase_order_Id'] );
                $fruugoOrderError->setReferenceNumber ( $order ['customer_order_id'] );
                $fruugoOrderError->setReason ( $message );
                $fruugoOrderError->setOrderData($orderObject);
                $fruugoOrderError->save ();
                return false;
            }
        } else {
            $nCustomer = $this->customerRepository->getById ( $customer->getId () );
            return $nCustomer;
        }
    }

    /**
     * Generate order in Magento
     *
     * @param integer $store
     * @param Object $ncustomer
     * @param array $order
     * @param Object $orderObject
     */
    public function generateQuote($store, $ncustomer, $order, $orderObject)
    {
        try{
            $autoReject = false;
            $itemsArray = $this->parserArray($order['orderLines']['orderLine']);
            $order['orderLines']['orderLine'] = $itemsArray;
            $baseprice = '';
            $shippingcost = '';
            $tax = '';
            $quote = $this->quote->create ();
            $quote->setStore($store);
            $quote->setCurrency();
            $customer = $this->customerRepository->getById($ncustomer->getId());
            $quote->assignCustomer ( $customer );
            $shippingcost = 0;
            $subTotal = 0;
            $taxArray = [];
            $taxTotal = 0;



            foreach ( $itemsArray as $item ) {
                $tax = 0;
                $message = '';
                $sku = $item['item']['sku'];
                $lineNumber = $item['lineNumber'];
                $quantity = $item['orderLineQuantity']['amount'];
                $productObj = $this->objectManager->get ('Magento\Catalog\Model\Product');

                $product = $productObj->loadByAttribute('sku', $item['item']['sku']);

                //custom code for Product
               /* if(!$product) {
                    $_product = $this->objectManager->create('Magento\Catalog\Model\Product');
                    $_product->setName($sku);
                    $_product->setTypeId('simple');
                    $_product->setAttributeSetId(4);
                    $_product->setSku($sku);
                    $_product->setWebsiteIds(array(1));
                    $_product->setVisibility(4);
                    $_product->setPrice(10.00);
                    $_product->setStockData(array(
                            'use_config_manage_stock' => 0, //'Use config settings' checkbox
                            'manage_stock' => 1, //manage stock
                            'min_sale_qty' => 1, //Minimum Qty Allowed in Shopping Cart
                            'max_sale_qty' => 999, //Maximum Qty Allowed in Shopping Cart
                            'is_in_stock' => 1, //Stock Availability
                            'qty' => 999 //qty
                        )
                    );

                    $_product->save();
                    $productObj = $this->objectManager->get ('Magento\Catalog\Model\Product');

                    $product = $productObj->loadByAttribute('sku', $item['item']['sku']);
                }*/



                if ($product) {
                    /*$product->setStockData(array(
                        'use_config_manage_stock' => 0, //'Use config settings' checkbox
                        'manage_stock' => 1, //manage stock
                        'min_sale_qty' => 1, //Minimum Qty Allowed in Shopping Cart
                        'max_sale_qty' => 999, //Maximum Qty Allowed in Shopping Cart
                        'is_in_stock' => 1, //Stock Availability
                        'qty' => 999 //qty
                    ))->save();*/
                    //$product = $this->product->create()->load($product->getEntityId ());
                    if ($product->getStatus () == '1') {
                        $stockRegistry = $this->objectManager->get( 'Magento\CatalogInventory\Api\StockRegistryInterface' );
                        /* Get stock item */
                        $stock = $stockRegistry->getStockItem($product->getId (), $product->getStore ()->getWebsiteId ());
                        $cancelItemQuantity = '';
                        if (!empty($item['orderLineStatuses']['orderLineStatus'][0]['statusQuantity']['amount'])) {
                            $cancelItemQuantity = 0;
                        }
                        $stockstatus = ($stock->getQty () > 0) ? ($stock->getIsInStock () == '1' ?
                            ($stock->getQty () >= $item ['orderLineQuantity']['amount'] ?
                                true :  ' Qunatity ordered i.e. '.$item ['orderLineQuantity']['amount'].' is not available in your store') : ' Is set to Out of Stock') : '  has 0 Quantity';
                        if ((is_bool($stockstatus) && $stockstatus) || true)  {
                            $productArray [] = [
                                'id' => $product->getEntityId (),
                                'qty' => $item ['orderLineQuantity']['amount']
                            ];
                            $price = $item ['charges'] ['charge'][0]['chargeAmount']['amount'];
                            $qty = $item ['orderLineQuantity']['amount'];

                            if(isset($item ['charges']['charge'][1])){
                                $shippingcost += ($item ['charges']['charge'][1]['chargeAmount']['amount'] * $qty) ;
                                $tax = $tax + ($item ['charges'] ['charge'][0]['tax']['taxAmount']['amount'] * $qty) +
                                    ($item ['charges']['charge'][1]['tax']['taxAmount']['amount'] * $qty);
                            } else{
                                $tax = $tax + ($item ['charges'] ['charge'][0]['tax']['taxAmount']['amount'] * $qty);
                            }
                            $taxTotal += $tax;
                            $rowTotal = $price * $qty;
                            $subTotal +=$rowTotal;

                            $product->setPrice($price)
                                ->setBasePrice($price)
                                ->setOriginalCustomPrice($price)
                                ->setRowTotal($rowTotal)
                                ->setBaseRowTotal($rowTotal);
                            // add items in quote
                            $quote->addProduct ( $product, intval ( $qty ) );
                            //quote->setDiscountAmount ( '0' );
                        } else {
                            // need to check again
                            $autoReject = true;
                            $this->rejectOrder($item, $order , $lineNumber , $quantity , $stockstatus);
                        }
                    }
                } else {
                    $autoReject = true;


                    $this->rejectOrder($item , $order , $lineNumber , $quantity , ' Is not Available In your System or is Disabled');
                }
                $taxArray[$sku] = $tax;
            }


            if(isset($productArray))
                if (count ( $productArray ) > 0 && count ( $itemsArray ) == count ( $productArray ) && ! $autoReject) {
                    $orderData = [
                        'currency_id' => 'USD',
                        'email' => 'test@cedcommerce.com', // buyer email id
                        'shipping_address' => [
                            'firstname' => $customer->getFirstName (),
                            'lastname' => $customer->getLastName (),
                            'street' => $order ['shippingInfo'] ['postalAddress'] ['address1'],
                            'city' => $order ['shippingInfo'] ['postalAddress'] ['city'],
                            'country_id' => 'US',
                            'region' => $order ['shippingInfo'] ['postalAddress'] ['state'],
                            'postcode' => $order ['shippingInfo'] ['postalAddress'] ['postalCode'],
                            'telephone' => $order ['shippingInfo'] ['phone'],
                            'fax' => '',
                            'save_in_address_book' => 1
                        ]
                    ];
                    $quote->getBillingAddress ()->addData ( $orderData ['shipping_address'] );
                    $shippingAddress = $quote->getShippingAddress ()->addData ( $orderData ['shipping_address'] );

                    // Collect Rates and Set Shipping & Payment Method


                    $shippingAddress->setCollectShippingRates ( true )->collectShippingRates ()->setShippingMethod ( 'shipfruugocom_shipfruugocom' );
                    $quote->setPaymentMethod ( 'payfruugocom' );
                    $quote->setInventoryProcessed ( false );
                    $quote->save ();
                    //Now Save quote and quote is ready

                    // Set Sales Order Payment
                    $quote->getPayment ()->importData ( [
                        'method' => 'payfruugocom'
                    ] );
                    // Collect Totals & Save Quote
                    $quote->collectTotals ()->save ();

                    foreach($quote->getAllItems() as $item){
                        $item->setDiscountAmount(0);
                        $item->setBaseDiscountAmount(0);

                        $sku = $item->getProduct()->getSku();
                        if (isset($taxArray[$sku])) {
                            $item->setTaxAmount($taxArray[$sku]);
                            $item->setBaseTaxAmount($taxArray[$sku]);
                        }
                        $item->setOriginalCustomPrice($item->getPrice())
                            ->setOriginalPrice($item->getPrice())
                            ->save();
                    }

                    $quote->collectTotals ()->save ();

                    $reserveIncrementId = $quote->getReservedOrderId();
                    $order = $this->quoteManagement->submit($quote);

                    $order->setEmailSent(0);
                    $increment_id = $order->getRealOrderId();
                    $quote = $this->cartRepositoryInterface->get ( $quote->getId () );
                    echo '<pre>';
                    var_dump($increment_id);die;
                    $orderAfterQuote = $this->cartManagementInterface->submit ( $quote );

                    $orderId =  $orderAfterQuote->getIncrementId();
                    $orderAfterQuote->setShippingAmount($shippingcost);
                    $orderAfterQuote->setTaxAmount($taxTotal);
                    $orderAfterQuote->setBaseTaxAmount($taxTotal);
                    $orderAfterQuote->setSubTotal($subTotal);
                    $orderAfterQuote->setGrandTotal($subTotal + $shippingcost+$taxTotal)  ;
                    $orderAfterQuote->setIncrementId($order['purchaseOrderId']);
                    $orderAfterQuote->save();
                    foreach($orderAfterQuote->getAllItems() as $item){
                        $item->setOriginalPrice($item->getPrice())
                            ->setBaseOriginalPrice($item->getPrice())
                            ->save();
                    }

                    // after save order
                    $deliver_by = date ( 'Y-m-d H:i:s', substr( $order ['shippingInfo'] ['estimatedDeliveryDate'],0,10 ) );
                    $order_place = date ( 'Y-m-d H:i:s', substr ( $order ['orderDate'],0,10 ) );
                    $orderData = [
                        'purchase_order_id' => $order ['purchaseOrderId'],
                        'deliver_by' => $deliver_by,
                        'order_place_date' => $order_place,
                        'magento_order_id' => $order['purchaseOrderId'],
                        'status' => $order['orderLines']['orderLine'][0]['orderLineStatuses']['orderLineStatus'][0]['status'],
                        'order_data' => json_encode( $order,true ),
                        'merchant_order_id' => $order ['customerOrderId']];
                    $model = $this->objectManager->create ( 'Ced\Fruugo\Model\FruugoOrders' )->addData ( $orderData );
                    $model->save ();
                    $this->generateInvoice ( $orderAfterQuote );
                    $this->autoOrderacknowledge ( $order ['purchaseOrderId'], $model );
                    return true;
                    // after save order end
                }
        } catch (\Exception $e) {
            $this->rejectOrder(NULL , $order , NULL , NULL , $e->getMessage());
            return false;
        }

    }
    /*
     * @Auto Order Acknowledgement Process
     */
    public function autoOrderacknowledge($Incrementid, $ordermodel=null)
    {
        $serialize_data = json_decode($ordermodel->getOrderData (),true);
        if (empty ( $serialize_data ) || count ( $serialize_data ) == 0) {
            $result = $this->datahelper->getOrder($Incrementid);
            $ord_result = $result ;
            if (empty ( $result ) || count ( $result ) == 0) {
                return 0;
            } else if($result['elements']['order'][0]['orderLines']['orderLine'][0]['orderLineStatuses']['orderLineStatus'][0]['status'] == 'Acknowledged') {
                return 0;
            } else
            {
                $wobj = $this->objectManager->create ( 'Ced\Fruugo\Model\FruugoOrders' )->load($Incrementid,'purchase_order_id');
                $wobj->setOrderData ( json_encode( $ord_result,true ) );
                $wobj->save ();
                $serialize_data = $ord_result;
            }
        }
        $order_id = $ordermodel->getPurchaseOrderId ();

        // Api call to Acknowledge Order
        $response = $this->datahelper->acknowledgeOrder ($order_id);


        if (empty ( $response ) && $response == null ) {
            return 0;
        } else {

            // Setting acknowleged status here

            if (count ( $ordermodel ) > 0) {
                $ordermodel->setStatus ( 'Acknowledged' );
                $ordermodel->save ();

            }
        }
        return 0;
    }

    /*
    * @Auto Order Rejection Request
    */
    public function rejectOrder($item , $result , $lineNumber , $quantity , $message) {
        $orderData = json_encode($result);
        if(is_array($item)) {
            $message = "Product " . $item['item']['sku'] . $message;
        }
        $fruugoOrderError = $this->objectManager->create ( 'Ced\Fruugo\Model\OrderImportError' ); // for error
        $fruugoOrderError->load($result ['purchaseOrderId'],'purchase_order_id');
        if(empty($fruugoOrderError->getData())) {
            $fruugoOrderError->setPurchaseOrderId ( $result ['purchaseOrderId'] );
            $fruugoOrderError->setReferenceNumber ( $result ['customerOrderId'] );
            $fruugoOrderError->setReason ( $message );
            $fruugoOrderError->setOrderData($orderData);
            $fruugoOrderError->save ();
        }
//       $data = $this->datahelper->rejectOrder (  $result ['purchaseOrderId'] , $lineNumber , $quantity);
    }

    /*
     * @Invoice generation Process
     */
    public function generateInvoice($order) {
        $invoice = $this->objectManager->create (
            'Magento\Sales\Model\Service\InvoiceService' )->prepareInvoice (
            $order );
        $invoice->register();
        $invoice->save();
        $transactionSave = $this->objectManager->create (
            'Magento\Framework\DB\Transaction' )->addObject (
            $invoice )->addObject ( $invoice->getOrder () );
        $transactionSave->save ();
        $order->addStatusHistoryComment ( __ ( 'Notified customer about invoice #%1.'
            , $invoice->getId () ) )->setIsCustomerNotified ( true )->save ();
        $order->setStatus ( 'processing' )->save ();
    }

    /*
     * @Shipment generation Process
     */
    public function generateShipment($order,$cancelleditems) {
        $shipment = $this->_prepareShipment ( $order ,$cancelleditems);
        if ($shipment) {
            $shipment->register ();
            $shipment->getOrder ()->setIsInProcess ( true );
            try {
                $transactionSave = $this->objectManager->create (
                    'Magento\Framework\DB\Transaction' )->addObject (
                    $shipment )->addObject ( $shipment->getOrder () );
                $transactionSave->save ();
                $order->setStatus ( 'complete' )->save ();
            } catch ( \Exception $e ) {
                $this->messageManager->addErrorMessage ( 'Error in saving shipping:'
                    . $e->getMessage() );
            }
        }
    }


    public function _prepareShipment($order, $cancelleditems)
    {
        foreach($order->getAllItems() as $orderItems)
        {

            $qty_ordered = $orderItems->getQtyOrdered();
            $cancelleditems[$orderItems->getId()] = (int) ($qty_ordered - $cancelleditems[$orderItems->getId()]);
        }

        $shipment = $this->objectManager->get ( 'Magento\Sales\Model\Order\ShipmentFactory' )->create ( $order, isset ( $cancelleditems ) ? $cancelleditems : [ ], [ ] );

        if (! $shipment->getTotalQty ()) {

            return false;
        }

        return $shipment;
    }

    public function generateCreditMemo($order,$cancelleditems)
    {

        foreach($order->getAllItems() as $orderItems)
        {
            $items_id = $orderItems->getId();
            $order_id = $orderItems->getOrderId();
        }
        $creditmemoLoader = $this->creditmemoLoaderFactory->create();
        $creditmemoLoader->setOrderId($order_id);
        foreach ($cancelleditems as $item_id=> $cancel_qty)
        {
            $creditmemo[$item_id] =['qty' => $cancel_qty];
        }

        $items = ['items' => $creditmemo,
            'do_offline' => '1',
            'comment_text' => 'Fruugo Cancelled Orders',
            'adjustment_positive' => '0',
            'adjustment_negative' => '0'];
        $creditmemoLoader->setCreditmemo($items);
        $creditmemo = $creditmemoLoader->load();

        $creditmemoManagement = $this->objectManager->create(
            'Magento\Sales\Api\CreditmemoManagementInterface'
        );

        if($creditmemo){

            $creditmemo->setOfflineRequested(true);
            $creditmemoManagement->refund($creditmemo, true);

        }
    }

    /**
     * @param string $details_after_saved
     * @return bool
     */

    public function generateCreditMemoForRefund($details_after_saved ='')
    {
        if (!empty($details_after_saved)) {
            $sku_details="";
            $sku_details=$details_after_saved['sku_details'];
            $item_details= [];
            $merchant_order_id="";
            $merchant_order_id=$details_after_saved['refund_order_id'];
            $shipping_amount=0;
            $adjustment_positive=0;
            foreach ($sku_details as $detail) {
                if ($this->checkifTrue($detail)) {
                    $item_details[]=['sku'=>$detail['merchant_sku'],'refund_qty'=>$detail['refund_quantity']];
                    $return_shipping_cost=0;
                    $return_shipping_tax=0;
                    $return_tax=0;
                    $return_shipping_cost=(float)trim(isset($detail['return_shipping_cost'])?$detail['return_shipping_cost']:0);
                    $return_tax=(float)trim(isset($detail['return_tax'])?$detail['return_tax']:0);
                    $return_shipping_tax=(float)trim(isset($detail['return_shipping_tax'])?$detail['return_shipping_tax']:0);
                    $shipping_amount = $shipping_amount + $return_shipping_cost +
                        $return_shipping_tax;
                    $adjustment_positive=$adjustment_positive+$return_tax;
                }
            }
            $collection="";
            $collection=$this->objectManager->create(
                'Ced\Fruugo\Model\FruugoOrders')->getCollection()->addFieldToSelect('magento_order_id')->addFieldToFilter(
                'purchase_order_id', $merchant_order_id);

            if ($collection->getSize()>0) {
                foreach ($collection as $coll) {
                    $magento_order_id=$coll->getData('magento_order_id');
                    break;
                }
            }

            if ($magento_order_id !='') {
                try {
                    $order ="";
                    $order = $this->objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($magento_order_id);
                    $data=[];
                    $shipping_amount=1; // enable credit memo
                    $data['shipping_amount']=0;
                    $data['adjustment_positive']=0;

                    ($shipping_amount>0) ? $data['shipping_amount']=$shipping_amount : false;
                    ($adjustment_positive>0) ? $data['adjustment_positive']=$adjustment_positive : false ;

                    foreach ($item_details as $key => $value) {
                        $orderItem="";
                        $orderItem = $order->getItemsCollection()->getItemByColumnValue('sku', $value['sku']);
                        $data['qtys'][$orderItem->getId()]=$value['refund_qty'] ;
                    }

                    if (!array_key_exists("qtys",$data)) {
                        $this->messageManager
                            ->addErrorMessage("Problem in Credit Memo Data Preparation.Can't generate Credit Memo.");
                        return false;
                    }

                    ($data['shipping_amount']==0) ? $this->messageManager
                        ->addErrorMessage("Amount is 0 .So Credit Memo Cannot be generated.") :
                        $this->generateCreditMemo($merchant_order_id, $data) ;

                } catch(\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage().".Can't generate Credit Memo.");
                    return false;
                }
            } else {
                $this->messageManager->addErrorMessage("Order not found.Can't generate Credit Memo.");
                return false;
            }
        } else {
            $this->messageManager->addErrorMessage("Can't generate Credit Memo.");
            return false;
        }
    }

    /**
     * @param $detail
     * @return bool
     */
    public function checkifTrue($detail)
    {
        if ($detail['refund_quantity']>0
            && $detail['return_quantity']>=$detail['refund_quantity']
            && $detail['refund_quantity']<=$detail['available_to_refund_qty']) {
            return true;
        } else {
            return false;
        }
    }

    public function getOrderFlag($order_to_complete,$order_cancel,$mixed)
    {
        $order_to_complete = isset($order_to_complete)?$order_to_complete:[];
        $order_cancel = isset($order_cancel)?$order_cancel:[];
        $mixed = isset($mixed)?$mixed:[];


        $Order_flag_array=array_merge($order_to_complete,$order_cancel,$mixed);
        $itemcount = sizeof($Order_flag_array);
        $complete = 0;
        $cancel = 0;
        $mix = 0;
        foreach ($Order_flag_array as $key => $value) {
            if($value == 'complete')
            {
                $complete++;
            }elseif($value == 'cancel')
            {
                $cancel++;
            }else
            {
                $mix++;
            }
        }

        return [
            'item_count' => $itemcount,
            'complete'=>$complete,
            'cancel'=> $cancel,
            'mix' => $mix
        ];
    }
    /*
      * @Ship by fruugo save process
      */
    public function putShipOrder($data_ship = NULL, $postData, $order_to_complete = [], $order_cancel = [], $mixed = []) {
        //For Auto ShipStation Only
        if(isset($data_ship['noCallToGenerateShipment'])) {

            $restorefile = fopen("var/log/shipments1.log",  'a+');
            fwrite($restorefile, "IN PUTSHIP");

            $fruugomodel = $this->objectManager->create('Ced\Fruugo\Model\FruugoOrders')->load ( $data_ship['shipments'][0]['purchaseOrderId'],'purchase_order_id' );

            fwrite($restorefile, $fruugomodel->getPurchaseOrderId());

            $data = $this->datahelper->shipOrder(  $data_ship  );
            fwrite($restorefile, "IN PUTSHIP".var_export( $data,true));

            if(isset($data['ns4:errors'])) {
                if (strpos($data['ns4:errors']['ns4:error']['ns4:description'],'SHIPPED status')!=false ) {
                    $fruugomodel->setStatus ( 'Already Shipped' ); //Already shipped
                    $fruugomodel->save ();
                    return true;
                }
            }
            fwrite($restorefile, var_export( $data,true));
            $responsedata['shippedData'] = $data;
            $responsedata['cancelData'] = '';
            $fruugomodel->setStatus('Complete')->setShipmentData(json_encode($responsedata,true))->save();

            return true;
        }


        $flag=$this->getOrderFlag($order_to_complete,$order_cancel,$mixed);
        if($flag['item_count'] == $flag['complete'])
        {
            $order_to_complete = true;
            $order_cancel = false;
            $mixed = false;
        }elseif($flag['item_count'] == $flag['cancel'])
        {
            $order_to_complete = false;
            $order_cancel = true;
            $mixed = false;
        }else{
            $order_to_complete = false;
            $order_cancel = false;
            $mixed = true;
        }
        $id = $postData ['key1'];
        $order_id = $postData ['orderid'];
        $fruugo_order_row = $postData ['order_table_row'];
        $items_data = $postData ['items'];
        $items_data = json_decode ( $items_data );
        /* Do not touch*/
        $quantity_to_cancel = $items_data [0] [2];
        /* Do not touch end */
        $order = $this->objectManager->get ( 'Magento\Sales\Model\Order' )->loadByIncrementId ( $id );

        // for order items ids and cancel qty relation
        foreach($order->getAllItems() as $orderItem)
        {
            foreach($items_data as $val)
            {
                if($orderItem->getSku() == $val[0])
                    $cancelleditems[$orderItem->getId()] = $val[2];
            }

        }

        $cancelData = $this->datahelper->rejectOrders($order_id,$data_ship);

        // Api call to complete shipment on fruugo
        $data = $this->datahelper->shipOrder(  $data_ship  );
        $responsedata['shippedData'] = $data;
        $responsedata['cancelData'] = $cancelData;
        if(empty($data)) {
            $this->messageManager->addSuccessMessage ('Fruugo API is down , please try to generate Shipment after sometime.');
            return;
        }
        $fruugomodel = $this->objectManager->get ( 'Ced\Fruugo\Model\FruugoOrders' )->load ( $fruugo_order_row );
        $fruugo_reference_id = $fruugomodel->getId ();
        if (($responsedata) && ($fruugo_reference_id)) {
            try {
                // as cancel quantity is not available in $dataship array for complete case
                if ($quantity_to_cancel != 0) {
                    $data_ship ['shipments'] [0] ['shipment_items'] [0] ['response_shipment_cancel_qty'] = $quantity_to_cancel;
                }

                $this->saveFruugoShipData ( $fruugomodel, $data_ship, $order_to_complete, $order_cancel,$mixed , $order, $cancelleditems,$responsedata);
                return  "Success" ;
            } catch ( \Exception $e ) {
                return $e->getMessage ();
            }
        } else {
            $err =  'Error while generating shipment on fruugo.com';
            return $err;
        }
    }
    /**
     * @Ship by fruugo save process
     */
    public function saveFruugoShipData($fruugomodel, $data_ship, $order_to_complete=null, $order_cancel=null, $mixed=null,$order, $cancelleditems , $responsedata) {

        // change 1 sept optimize it merging similar case removed whole complete case due to repetition of code

        // mixed_complete_case // And need one more case for multiple shipment (data of mixed will used)

        // all case in one

        if(!$order_cancel || $mixed){
            $fruugomodel->setStatus ( 'Complete' );
        }else{
            $fruugomodel->setStatus ( 'Cancelled' );
        }
        $fruugomodel->setShipmentData ( json_encode( $responsedata,true ) );
        $fruugomodel->save ();

        if(!$order_cancel)
            if (! $order->canShip()) {
                $this->messageManager->addErrorMessage(__("You can\'t create an shipment.")
                );
            }else{
                $this->generateShipment ( $order , $cancelleditems);
            }

        if(!$order_to_complete || $order_cancel)
            if (!$order->canCreditmemo()) {
                $this->messageManager->addErrorMessage(__("We can\'t create credit memo for the order."));
                return false;
            }else{
                $this->generateCreditMemo($order,$cancelleditems);
            }
        $this->messageManager->addSuccessMessage ( 'Your Fruugo Order ' . $order->getId() . ' has been Completed.' );

    }

    public function parserArray($array){
        $arr = [];
        foreach ($array as $key => $value){
            if(in_array($key,$arr))
                continue;
            $count = count($array);
            $sku = $value['item']['sku'];
            $quantity = 1;
            $lineNumber = $value['lineNumber'];
            for ( $i = $key+1 ; $i < $count;$i++){
                if(isset($array[$i]) && ($array[$i]['item']['sku'] == $sku)){
                    $quantity++;
                    $lineNumber = $lineNumber.','.$array[$i]['lineNumber'];
                    unset($array[$i]);
                    array_push($arr,$i);
                    array_values($array);
                }
            }
            $array[$key]['lineNumber'] = $lineNumber;
            $array[$key]['orderLineQuantity']['amount'] = $quantity;
        }
        return $array;
    }
}