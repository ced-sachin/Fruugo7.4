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

use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Boolean;

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

    /*
     * FruugoOrders Resouce Connection
     * @var ZendConnection
     */
    public $connection;
    public $registry;

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
        \Ced\Fruugo\Model\ResourceModel\FruugoOrders\CollectionFactory $fruugoOrder,
        \Magento\Framework\Json\Helper\Data $json,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Customer\Model\CustomerRegistry $customerRegistry,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->productHelper = $productHelper;
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
        $this->customerRegistry = $customerRegistry;
        $this->json = $json;
        $this->_orderFactory = $orderCollectionFactory;
        $this->fruugoLogger =  $this->objectManager->create('\Ced\Fruugo\Helper\FruugoLogger');
        parent::__construct ( $context );
        $this->scopeConfigManager = $this->objectManager->get ( 'Magento\Framework\App\Config\ScopeConfigInterface' );
        $this->configValueManager = $this->objectManager->get ( 'Magento\Framework\App\Config\ValueInterface' );
        $this->messageManager = $this->objectManager->get ( 'Magento\Framework\Message\ManagerInterface' );
        $this->datahelper = $this->objectManager->get ( 'Ced\Fruugo\Helper\Data' );
        $this->connection = $this->objectManager->create ( 'Ced\Fruugo\Model\ResourceModel\FruugoOrders' )->getConnection();
        $this->currencyFactory = $currencyFactory;
        $this->_coreRegistry = $registry;
    }

    /**
     * Fetch Latest Orders in ready state from Fruugo
     *
     * @return null
     */
    public function fetchLatestFruugoOrders()
    {
        $date = $this->scopeConfigManager->getValue ( 'fruugoconfiguration/fruugosetting/orders_fetch_startdate' );
        $storeId = $this->scopeConfigManager->getValue ( 'fruugoconfiguration/product_edit/fruugo_storeid' );
        $websiteId = $this->storeManager->getStore ()->getWebsiteId ();
        $store = $this->storeManager->getStore ($storeId);
        $this->storeManager->setCurrentStore($store);
        $response = $this->datahelper->getOrders();
        if(isset($response ['o:orders']['_value']['o:order']) && !isset($response ['o:orders']['_value']['o:order'][0])) {
            $singleOrder[0] = $response ['o:orders']['_value']['o:order'];
            unset($response ['o:orders']['_value']['o:order']);
            $response ['o:orders']['_value']['o:order'] = $singleOrder;
        }

        $successPOS = [];
        $errorsPOS = [];

        if (isset($response ['o:orders']['_value']['o:order'])) {
            foreach ( $response ['o:orders']['_value']['o:order'] as $order ) {
                $orderObject =  $order ;
                $firstname = $order['o:shippingAddress']['o:firstName'];
                $lastname = $order['o:shippingAddress']['o:lastName'];
                //uncomment if want to create every email
                /*$customEmailID = $firstname.'_'.$lastname.'@fruugo.com';
                 $email = isset($order['customerEmailId']) ? $order ['customerEmailId'] : $customEmailID;
                 $email = preg_replace("/[^a-zA-Z0-9\@._]/", '', $email);*/
                 //uncomment if want to create every email
                $customEmailID = 'customer@fruugo.com';
                $email = isset($order['o:shippingAddress']['o:emailAddress']) ? $order['o:shippingAddress']['o:emailAddress'] : $customEmailID;
                $email = rand().$email;
                $customer = $this->customerFactory->create()->setWebsiteId( $websiteId)->loadByEmail($email);

                if (count ( $order ) > 0 && $order['o:orderStatus'] == 'PENDING') {
                    $purchaseOrderid = $order['o:orderId'];
                    $resultdata = $this->fruugoOrder->create()
                        ->addFieldToFilter ( 'purchase_order_id', $purchaseOrderid );
                    if (count ( $resultdata->getData () ) <= 0) {
                        $ncustomer = $this->_assignCustomer ( $order, $customer, $store, $email );
                        if (is_bool($ncustomer) && ! $ncustomer->getId()) {
                            continue;
                        } else {
                            $return = $this->generateQuote ( $store, $ncustomer, $order, $orderObject );
                            if($return) {
                                $successPOS[] = $purchaseOrderid;
                            } else {
                                $errorsPOS[] = $purchaseOrderid;
                            }

                        }
                    }
                }
            }

            if(count($successPOS ) > 0)
            {
                $model = $this->objectManager->create('\Magento\AdminNotification\Model\Inbox');
                $date = date("Y-m-d H:i:s");
                $model->setData('severity', 4);
                $model->setData('date_added', $date);
                $model->setData('title', "Incoming Fruugo Order");
                $model->setData('description', "Congratulation !! You have received ".count($successPOS )." new orders from Fruugo");
                $model->setData('url', "#");
                $model->setData('is_read', 0);
                $model->setData('is_remove', 0);
                $model->save();
                $this->messageManager->addSuccessMessage('The following Purchase Order Id\'s fetched Successfully - '.implode(',' , $successPOS));
            }
            if(count($errorsPOS ) > 0) {
                $url = $this->objectManager->create('Magento\Framework\UrlInterface')->getUrl('fruugo/order/failedorders');
                $message = 'The following Purchase Order Id\'s failed to fetch - ';
                $message.= implode("," , $errorsPOS) ;
                $message.=  ' . Please check <a href="'.$url.'">Fruugo Failed Orders</a>';
                $this->messageManager
                    ->addError($message);

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
        if (!  ( $customer->getId () )) {
            try {
                //$cname = $order ['o:shippingAddress']['postalAddress'] ['name'];
                //$customerName = explode ( ' ', $cname );
                $firstname = $order['o:shippingAddress']['o:firstName'];
                //unset($customerName[0]);
                //$customerName = array_values($customerName) ;
                $lastname = $order['o:shippingAddress']['o:lastName'];
                if (! isset ( $lastname ) || $lastname == '') {
                    $lastname = $firstname;
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
                $this->rejectOrder(NULL , $order , NULL , NULL ,$e->getMessage ());
                return false;
            }
        } else {
            $firstname = $order['o:shippingAddress']['o:firstName'];
            $lastname = $order['o:shippingAddress']['o:lastName'];
            $customer->setFirstname($firstname);
            $customer->setLastname($lastname);
            $customer->save();
            return $customer;
        }
    }

    /**
     * Generate order in Magento     *
     * @param integer $store
     * @param Object $ncustomer
     * @param array $order
     * @param Object $orderObject
     * @return Boolean
     */
    public function generateQuote($store, $ncustomer, $order, $orderObject)
    {

        try{
//            $this->connection->beginTransaction();
            $autoReject = false;
            if(!isset($order['o:orderLines']['o:orderLine'][0])) {
                $singleOrder[0] = $order['o:orderLines']['o:orderLine'];
                unset($order['o:orderLines']['o:orderLine']);
                $order['o:orderLines']['o:orderLine'] = $singleOrder;
            }
            $quote = $this->quote->create ();
            $quote->setStore($store);

            $fruugoCurrencyCode = $this->scopeConfigManager->getValue ( 'fruugoconfiguration/productinfo_map/fruugo_currency' );
            $baseCurrency = $this->storeManager->getStore()->getBaseCurrency()->getCode();

            $quote->setCurrency();
            $this->customerRegistry->remove($ncustomer->getId());
            $customer = $this->customerRepository->getById($ncustomer->getId());
            $quote->assignCustomer ( $customer );
            if($order['o:shippingCostVAT']!=null)
            {
                $shippingcost =  $order ['o:shippingCostInclVAT'] - $order['o:shippingCostVAT'];
                $shippingTax = $order['o:shippingCostVAT'];
            }
            
            else
            {
                $shippingcost =  $order ['o:shippingCostInclVAT'];
                $shippingTax = 0;
            }
            
            $subTotal = 0;
            $taxArray = [];
            $taxTotal = 0;
            $orderPrefix = $this->scopeConfigManager->getValue ( 'fruugoconfiguration/productinfo_map/order_prefix' );
            $autoCnfrm = $this->scopeConfigManager->getValue('fruugoconfiguration/fruugo_order_setting/auto_confirm_order');
            $autoRejectFruugoOrder = $this->scopeConfigManager->getValue('fruugoconfiguration/fruugo_order_setting/auto_reject_order');
            $prodDisabled = $this->scopeConfigManager->getValue('fruugoconfiguration/fruugo_order_setting/create_order_for_disabled');
            $prodOutOfStock = $this->scopeConfigManager->getValue('fruugoconfiguration/fruugo_order_setting/create_order_for_outofstock');

            foreach ( $order['o:orderLines']['o:orderLine'] as $item ) {
                $tax = 0;
                $sku = $item['o:skuId'];
                $lineNumber = '';
                $quantity = $item['o:totalNumberOfItems']/*['amount']*/;
                $productObj = $this->objectManager->get ('Magento\Catalog\Model\Product');
                $product = $productObj->loadByAttribute('sku', $sku);
                $stockRegistry = $this->objectManager->get( 'Magento\CatalogInventory\Api\StockRegistryInterface' );
                /* Get stock item */
                $stock = $stockRegistry->getStockItem($product->getId (), $product->getStore ()->getWebsiteId ());
                $productStatus = $product->getStatus();
                $productStatusChange = 0;
                if($prodDisabled=='1' && $productStatus == '2'){
                    $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
                    $product->save();
                    $productStatusChange = 1;
                }
                if ($product) {                    
                    if ($product->getStatus () == '1' || ( $prodDisabled == "1") ) {
                        $stockRegistry = $this->objectManager->get( 'Magento\CatalogInventory\Api\StockRegistryInterface' );
                        /* Get stock item */
                        $stock = $stockRegistry->getStockItem($product->getId (), $product->getStore ()->getWebsiteId ());
                        $cancelItemQuantity = '';
                        if (!empty($item['o:totalNumberOfItems'])) {
                            $cancelItemQuantity = 0;
                        }
                        $stockstatus = ($stock->getQty () > 0) ? ($stock->getIsInStock () == '1' ?
                            ($stock->getQty () >= $item ['o:totalNumberOfItems'] ?
                                true :  ' Qunatity ordered i.e. '.$item ['o:totalNumberOfItems'].' is not available in your store') : ' Is set to Out of Stock') : '  has 0 Quantity';
                        if ((is_bool($stockstatus) && $stockstatus)|| ( $prodOutOfStock == "1" ))  {
                            $productArray [] = [
                                'id' => $product->getEntityId (),
                                'qty' => $item ['o:totalNumberOfItems']
                            ];
                            if(isset($item ['o:itemPriceInclVat'])) {
                                $price = $item ['o:itemPriceInclVat'] - $item ['o:itemVat'];
                            }
                            /*$price = $this->priceHelper->currencyConvert($price, $baseCurrency, $fruugoCurrencyCode);*/
                            $currencyRate = $this->currencyFactory->create()->load($baseCurrency)->getAnyRate($fruugoCurrencyCode);
                            $price = $price / $currencyRate; 
                            
                            $qty = $item ['o:totalNumberOfItems'];
                            $tax = $tax + ($item ['o:itemVat'] * $qty);

                            /*if(isset($order ['o:shippingCostInclVAT'])){
                                
                                $tax = $tax + ($item ['o:itemVat'] * $qty); //+
                                    ($item ['charges']['charge'][1]['tax']['taxAmount']['amount'] * $qty);
                            } else{
                                $tax = $tax + ($item ['o:itemVat'] * $qty);
                            }*/
                            $taxTotal += $tax;
                            $rowTotal = $price * $qty;
                            $subTotal +=$rowTotal;

                            $product->setPrice($price)
                                ->setBasePrice($price)
                                ->setOriginalCustomPrice($price)
                                ->setRowTotal($rowTotal)
                                ->setBaseRowTotal($rowTotal)
                                ->setTaxClassId(null);
                            if ( ( $product->getStatus () == '2' && ( $prodDisabled == "1") ) || ( !is_bool($stockstatus) && $prodOutOfStock == "1" ) ) {
                                $quote->setIsSuperMode(true);
                                $this->productHelper->setSkipSaleableCheck(true);
                            }
                            //$this->productHelper->setSkipSaleableCheck(true);
                            $quote->addProduct ( $product, intval ( $qty ) );
                            $ack_arr[] = array(
                                'merchant_sku' => $product->getSku(),
                                'fruugo_product_id' => $item['o:fruugoProductId'],
                                'fruugo_sku_id' => $item['o:fruugoSkuId'],
                                'ack_qty' => $item['o:totalNumberOfItems'],
                            );
                            $item_confirmed = $item['o:fruugoProductId'].','.$item['o:fruugoSkuId'].','.$item['o:totalNumberOfItems'];
                            $confirmed_items_arr[] = $item_confirmed;
                        } else {
                            $autoReject = true;
                            $item_rejected = $item['o:fruugoProductId'].','.$item['o:fruugoSkuId'].','.$item['o:totalNumberOfItems'];
                            $reject_items_arr[] = $item_rejected;
                            $this->rejectOrder($item, $order , $lineNumber , $quantity , $stockstatus);
                        }
                    }
                    else {
                        $autoReject = true;
                        $item_rejected = $item['o:fruugoProductId'].','.$item['o:fruugoSkuId'].','.$item['o:totalNumberOfItems'];
                        $reject_items_arr[] = $item_rejected;
                        $this->rejectOrder($item , $order , $lineNumber , $quantity , ' SKU is Disabled in your System.');
                    }
                } else {
                    $autoReject = true;
                    $item_rejected = $item['o:fruugoProductId'].','.$item['o:fruugoSkuId'].','.$item['o:totalNumberOfItems'];
                    $reject_items_arr[] = $item_rejected;
                    $this->rejectOrder($item , $order , $lineNumber , $quantity , ' Is not Available In your System.');
                }
                $taxArray[$sku] = $tax;
                if($productStatusChange==1){                        
                    $product->setData('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED)->save();
                }
            }

            if($autoReject && $autoRejectFruugoOrder == "1") {
                $reject_items_arr = implode('&item=', $reject_items_arr);
                $reject_items_arr = 'orderId='.$order ['o:orderId'].'&item='.$reject_items_arr.'&cancellationReason=out_of_stock';
                $cancelOrder = $this->datahelper->cancelOrder ($reject_items_arr );
            }


            if(isset($productArray))
                if (count ( $productArray ) > 0 /*&& count ( $order['o:orderLines']['o:orderLine'] ) == count ( $productArray ) && ! $autoReject*/) {
                    /*$state = "";
                    $region_id = $order ['o:shippingAddress']['o:countryCode'];
                    if(isset($order ['o:shippingAddress']['o:province']))
                    {
                        $state = $order ['o:shippingAddress']['o:province'];
                        $countryFactory = $this->objectManager->get('Magento\Directory\Model\CountryFactory');
                         $stateArray = $countryFactory->create()->setId($order ['o:shippingAddress']['o:countryCode'])->getLoadedRegionCollection()->toOptionArray();

                         if(is_array($stateArray) && count($stateArray)>0)
                         {
                            foreach ($stateArray as $key => $value) {
                                if($value['title'] == $state)
                                    $region_id = $value['value'];
                            }
                            
                         }
                         if($region_id == $order ['o:shippingAddress']['o:countryCode'] && count($stateArray)>0)
                         $region_id = $stateArray[1]['value'];
                       
                    }
                    else
                    {
                        $state = $order ['o:shippingAddress']['o:countryCode'];
                        $countryFactory = $this->objectManager->get('Magento\Directory\Model\CountryFactory');
                         $stateArray = $countryFactory->create()->setId($state)->getLoadedRegionCollection()->toOptionArray();
                         if(is_array($stateArray) && count($stateArray)>0)
                         {
                            $region_id = $stateArray[1]['value'];
                         }
                         
                         

                    }*/
                    $countryFactory = $this->objectManager->get('Magento\Directory\Model\CountryFactory');
                    $region_id = '';
                    $region = '';
                    if(isset($order ['o:shippingAddress']['o:province']))
                    {
                        $region = $order ['o:shippingAddress']['o:province'];

                         
                         $stateArray = $countryFactory->create()->setId($order ['o:shippingAddress']['o:countryCode'])->getLoadedRegionCollection()->toOptionArray();

                         if(is_array($stateArray) && count($stateArray)>0)
                         {
                            foreach ($stateArray as $key => $value) {
                                if($value['title'] == $region)
                                    $region_id = $value['value'];
                            }
                            
                         }
                       
                    }
                    else
                    {
                        $region_id = '';
                        $region = '';
                    }

                    $phone_number = '';
                       if(isset($order ['o:shippingAddress']['o:phoneNumber']))
                       {
                            $phone_number = $order ['o:shippingAddress']['o:phoneNumber'];
                       }
                       $fruugo_tax_id = 'false';
                       if($order['o:fruugoTax'] == 'false')
                       {
                            $fruugo_tax_id = 'false';
                       }
                       else
                       {
                        $fruugo_tax_id = $order['o:fruugoTaxID'];
                       }
                    $orderData = [
                        'currency_id' => 'USD',
                        'email' => 'test@cedcommerce.com', // buyer email id
                        'shipping_address' => [
                            'firstname' => $customer->getFirstName (),
                            'lastname' => $customer->getLastName (),
                            'street' => $order ['o:shippingAddress']['o:streetAddress'],
                            'city' => $order ['o:shippingAddress']['o:city'],
                            'country_id' => $order ['o:shippingAddress']['o:countryCode'],
                            'region' => $region,
                            'region_id' => $region_id,
                            'postcode' => $order ['o:shippingAddress']['o:postalCode'],
                            'telephone' => $phone_number,
                            'fax' => $fruugo_tax_id,
                            'save_in_address_book' => 1
                        ]
                    ];
                    $quote->getBillingAddress ()->addData ( $orderData ['shipping_address'] );
                    $shippingAddress = $quote->getShippingAddress ()->addData ( $orderData ['shipping_address'] );
                    $shippingService = $order['o:shippingMethod'];
                    $this->registerShippingMethod($shippingService);
                    $this->registerShippingAmount($shippingcost);
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
                   // $orderAfterQuote = $this->quoteManagement->submit($quote->getId());

                    $quote = $this->cartRepositoryInterface->get ( $quote->getId () );
                    $orderAfterQuote = $this->cartManagementInterface->submit ( $quote );
                    //var_dump($orderAfterQuote);die;
                    $orderId =  $orderPrefix . $orderAfterQuote->getIncrementId();
                    
                    $orderAfterQuote->setShippingAmount($shippingcost);
                    $orderAfterQuote->setBaseShippingAmount($shippingcost);
                    $orderAfterQuote->setShippingInclTax($shippingcost + $shippingTax);
                    $orderAfterQuote->setBaseShippingInclTax($shippingcost + $shippingTax);
                    $orderAfterQuote->setTaxAmount($taxTotal + $shippingTax);
                    $orderAfterQuote->setBaseTaxAmount($taxTotal + $shippingTax);
                    $orderAfterQuote->setSubTotal($subTotal);
                    /*$orderAfterQuote->setBaseSubTotalInclTax($subTotal);
                    $orderAfterQuote->setBaseShippingDiscountTaxCompensationAmnt('0.0000');*/
                    $orderAfterQuote->setGrandTotal($subTotal + $shippingcost + $taxTotal + $shippingTax)  ;
                    $orderAfterQuote->setIncrementId($orderId);

                    $orderAfterQuote->save();
                    foreach($orderAfterQuote->getAllItems() as $item){
                        $item->setOriginalPrice($item->getPrice())
                            ->setBaseOriginalPrice($item->getPrice())
                            ->save();
                    }
                    $deliver_by = $order['o:orderReleaseDate'];
                    $order_place = $order['o:orderDate'];
                    if($autoCnfrm == "1") {
                        $status = 'pending';
                        $data_ack['acknowledge'][] = array(
                            'acknowledged_items' => $ack_arr
                        );
                        $data_ack = json_encode($data_ack,true);
                    } else {
                        $status = 'ready';
                        $data_ack = NULL;
                    }
                    $orderData = [
                        'purchase_order_id' => $order ['o:orderId'],
                        'deliver_by' => $deliver_by,
                        'order_place_date' => $order_place,
                        'magento_order_id' => $orderId,
                        'status' => /*$order['o:orderStatus']*/$status,
                        'order_data' => json_encode ($order,true),
                        'acknowledge_data' => $data_ack,
                        'merchant_order_id' => $order ['o:customerOrderId']];
                    $model = $this->objectManager->create ( 'Ced\Fruugo\Model\FruugoOrders' )->addData ( $orderData );
                    $model->save ();

                    if(count($confirmed_items_arr) > 0 && $autoCnfrm == "1") {
                        $confirmed_items_data = implode('&item=', $confirmed_items_arr);
                        $confirmed_items_data = 'orderId='.$order ['o:orderId'].'&item='.$confirmed_items_data;
                        $this->autoOrderacknowledge ( $confirmed_items_data, $model );
                        $this->generateInvoice ( $orderAfterQuote );
                    }
//                    $this->connection->commit();
                    return true;
                    // after save order end
                }
        } catch (\Exception $e) {
//            $this->connection->rollback();
            $this->rejectOrder(NULL , $order , NULL , NULL , $e->getMessage());
            return false;
        }

    }
    /*
     * @Auto Order Acknowledgement Process
     */
    public function autoOrderacknowledge($confirmed_items, $ordermodel=null)
    {
        // Api call to Acknowledge Order
        $response = $this->datahelper->acknowledgeOrder ($confirmed_items);
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
    public function rejectOrder($item = null , $result  , $lineNumber = null , $quantity = null , $message) {
        
        $orderData = json_encode($result);
        if(is_array($item)) {
            $message = "Product " . $item['o:skuId'] . $message;
        }
        $fruugoOrderError = $this->objectManager->create ( 'Ced\Fruugo\Model\OrderImportError' ); // for error
        $fruugoOrderError->load($result ['o:orderId'],'purchase_order_id');
        if(empty($fruugoOrderError->getData())) {
            $fruugoOrderError->setPurchaseOrderId ( $result ['o:orderId'] );
            $fruugoOrderError->setReferenceNumber ( $result ['o:customerOrderId'] );
            $fruugoOrderError->setReason ( date("l jS \of F Y h:i:s A").' - '.$message );
            $fruugoOrderError->setOrderData($orderData);
            $fruugoOrderError->save ();
        } else {
            $fruugoOrderError->setReason ( date("l jS \of F Y h:i:s A").' - '.$message );
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
        $order/*->addStatusHistoryComment ( __ ( 'Notified customer about invoice #%1.'
            , $invoice->getId () ) )*/->setIsCustomerNotified ( false )->save ();
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
                //$order->setStatus ( 'complete' )->save ();
            } catch ( \Exception $e ) {
                $this->messageManager->addErrorMessage ( 'Error in saving shipping:'
                    . $e->getMessage() );
            }
        }
    }


    public function _prepareShipment($order, $cancelleditems)
    {
        /*foreach($order->getAllItems() as $orderItems)
        {

            $qty_ordered = $orderItems->getQtyOrdered();
            $cancelleditems[$orderItems->getId()] = (int) ($qty_ordered - $cancelleditems[$orderItems->getId()]);
        }*/

        //echo "<pre>";print_r($cancelleditems);die('fg');
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

        $items = [
            'items' => $creditmemo,
            'do_offline' => '1',
            'comment_text' => 'Fruugo Cancelled Orders',
            'adjustment_positive' => '0',
            'adjustment_negative' => '0'
        ];
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
            $fruugomodel = $this->objectManager->create('Ced\Fruugo\Model\FruugoOrders')->load ( $data_ship['shipments'][0]['purchaseOrderId'],'purchase_order_id' );
            $data = $this->datahelper->shipOrder(  $data_ship  );
            $ship_dbdata = $fruugomodel->getShipmentData();
            if(isset($ship_dbdata)){
                $temp_arr = json_decode($ship_dbdata,true);
                $temp_arr["shipments"][]=$data_ship["shipments"][0];
            }else{
                $temp_arr = $data_ship;
            }
            //if(isset($data['o:order']['o:orderId']))
                $fruugomodel->setStatus('Complete')->setShipmentData(json_encode($temp_arr,true))->save();

            return $data;
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
        $order = $this->objectManager->get( 'Magento\Sales\Model\Order' )->loadByIncrementId( $id );
        $cancelleditems = $shipQty = [];

        // for order items ids and cancel qty relation
        foreach($order->getAllItems() as $orderItem)
        {
            foreach($items_data as $val)
            {
                if($orderItem->getSku() == $val[0] && $val[2] > 0 ) {
                    $cancelleditems[$orderItem->getId()] = $val[2];
                }
                if($orderItem->getSku() == $val[0] && $val[3] > 0 ) {
                    $shipQty[$orderItem->getId()] = $val[3];
                }
            }

        }
        $cancelData = '';
        if(isset($data_ship['shipments'][0]['cancel_items']) && count($data_ship['shipments'][0]['cancel_items'])> 0 ) {
            $cancelData = $this->datahelper->rejectOrders($order_id,$data_ship);
        }


        // Api call to complete shipment on fruugo
        $data = '';
        if(isset($data_ship['shipments'][0]['shipment_items']) && count($data_ship['shipments'][0]['shipment_items'])> 0 ) { 
            $data = $this->datahelper->shipOrder(  $data_ship  ); 
        }
        $responsedata['shippedData'] = $data;
        $responsedata['cancelData'] = $cancelData;
        if(!$data && !$cancelData) {
            $this->messageManager->addSuccessMessage ('No response from Fruugo API , please try to generate Shipment after sometime.');
            return;
        }
        $fruugomodel = $this->objectManager->get ( 'Ced\Fruugo\Model\FruugoOrders' )->load ( $fruugo_order_row );
        $fruugo_reference_id = $fruugomodel->getId ();
        if (($responsedata) && ($fruugo_reference_id)) {
            try {
                $this->saveFruugoShipData ( $fruugomodel, $data_ship, $order_to_complete, $order_cancel,$mixed , $order, $cancelleditems, $shipQty);
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
    public function saveFruugoShipData($fruugomodel, $data_ship, $order_to_complete=null, $order_cancel=null, $mixed=null,$order, $cancelleditems , $shipQty) {

        // change 1 sept optimize it merging similar case removed whole complete case due to repetition of code

        // mixed_complete_case // And need one more case for multiple shipment (data of mixed will used)

        // all case in one

        if(!$order_cancel || $mixed){
            $fruugomodel->setStatus ( 'Complete' );
        }else{
            $fruugomodel->setStatus ( 'Cancelled' );
        }
        $ship_dbdata = $fruugomodel->getShipmentData();
        if(isset($ship_dbdata)){
            $temp_arr = json_decode($ship_dbdata,true);
            $temp_arr["shipments"][]=$data_ship["shipments"][0];
        }else{$temp_arr = $data_ship;
        }
        $fruugomodel->setShipmentData ( json_encode($temp_arr,true) );
        $fruugomodel->save ();

        if(!$order_cancel && count($shipQty) > 0) {
            if (! $order->canShip()) {
                $this->messageManager->addErrorMessage(__("You can\'t create an shipment.")
                );
            }else{
                $this->generateShipment ( $order , $shipQty);
            }
        }

        if(/*!$order_to_complete || $order_cancel && */count($cancelleditems) > 0) {
            if (!$order->canCreditmemo()) {
                $this->messageManager->addErrorMessage(__("We can\'t create credit memo for the order."));
                return false;
            }else{
                $this->generateCreditMemo($order,$cancelleditems);
            }
        }
        //$this->checkAndSaveOrderStatus($order, $fruugomodel);
        //$this->messageManager->addSuccessMessage ( 'Your Fruugo Order ' . $order->getId() . ' has been Completed.' );

    }

    public function parserArray($array){
        $arr = [];
        if(!isset($array[0])) {
            $singleOrder[0] = $array;
            unset($array);
            $array = $singleOrder;
        }
        /*if(!isset($response['content']['datas']['order']['0'])) {
            $singleOrder[0] = $response['content']['datas']['order'];
            unset($response['content']['datas']['order']);
            $response['content']['datas']['order'] = $singleOrder;
        }*/
        foreach ($array as $key => $value){
            if(in_array($key,$arr))
                continue;
            $count = count($array);
            $sku = $value['o:skuId']/*['sku']*/;
            $quantity = 1;
            $lineNumber = $value['o:totalNumberOfItems'];
            for ( $i = $key+1 ; $i < $count;$i++){
                if(isset($array[$i]) && ($array[$i]['o:skuId'] == $sku)){
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
        //die('fg');
        return $array;
    }


    public function checkAndSaveOrderStatus($order, $fruugomodel) {
        $orderComplete = true;
        $orderItems = $order->getAllItems();
        foreach($orderItems as $item) {
           // echo "<pre>";print_r($item->getData());
            $processedOrder = (int) $item->getQtyShipped() + (int) $item->getQtyRefunded() + (int) $item->getQtyCanceled();
            if( $processedOrder < (int) $item->getQtyOrdered() ) {
                $orderComplete = false;
            }
        }

        //var_dump($orderComplete);
        if($orderComplete) {
            $order->setStatus( 'complete' )->save();
            $fruugomodel->setStatus( 'Complete' )->save();
        } else {
            $fruugomodel->setStatus( 'inProgress' )->save();
        }
        //die('fg');
        return $orderComplete;
        //die('gf');
    }



    /**
     * Get Current Month Order
     */
    public function getCurrentMonthOrder($dates)
    {
        try {
            $returnsOrderData = [
                'currentMonth' => [],
                'totalOrders' => []
            ];
            /*if($currentMonthFlag) {
                $ordersData = $this->fruugoOrder->create()
                    ->addFieldToFilter('order_place_date', [ 'gteq' => date('Y-09-20 14:57:s')])
                    ->getData();
            } else {
                $ordersData = $this->fruugoOrder->create()->getData();
            }*/
            
            $startDate = date('Y-m-d 00:00:00', strtotime($dates['from']));
            $endDate = date('Y-m-d 00:00:00', strtotime($dates['to']));
            
            /*$currMonthOrdersData = $this->fruugoOrder->create()
            ->getData();
            foreach ($currMonthOrdersData as $orderData ) {
                $orderDate = date("Y-m-d H:i:s", strtotime($orderData['order_place_date']));
                $order = $this->_orderFactory->create()
                    ->addFieldToFilter(
                        'increment_id',
                        $orderData['magento_order_id']
                    )->getFirstItem();
                     if($orderDate >= $startDate && $orderDate <= $endDate) 
                     {
                        $filterData = array($orderData['purchase_order_id'] => $order['grand_total'] );
                        array_push($returnsOrderData['currentMonth'], $filterData);
                     }
                
            }*/
            $ordersData = $this->fruugoOrder->create()->getData();
            foreach ($ordersData as $orderData ) {
                $orderDate = date("Y-m-d H:i:s", strtotime($orderData['order_place_date']));
                $order = $this->_orderFactory->create()
                    ->addFieldToFilter(
                        'increment_id',
                        $orderData['magento_order_id']
                    )->getFirstItem();
                     $filterData = array($orderData['purchase_order_id'] => $order['grand_total'] );
                     if($orderDate >= $startDate && $orderDate <= $endDate) 
                     {
                       // $filterData = array($orderData['purchase_order_id'] => $order['grand_total'] );
                        array_push($returnsOrderData['currentMonth'], $filterData);
                     }
               
                array_push($returnsOrderData['totalOrders'], $filterData);
            }
            
            return $returnsOrderData;
            //echo "<pre>";print_r($returnsOrderData);die('fg');
        } catch (\Exception $e) {
            echo $e->getMessage();die('fgsdf');
        }
    }


    private function registerShippingMethod($shippingService)
    {
        $this->_coreRegistry->unregister(\Ced\Fruugo\Model\Carrier\Shipfruugocom::REGISTRY_INDEX_SHIPPING_METHOD);

        $this->_coreRegistry->register(
            \Ced\Fruugo\Model\Carrier\Shipfruugocom::REGISTRY_INDEX_SHIPPING_METHOD,
            $shippingService
        );
    }

    /**
     * Set the total shipping price in registry
     * @param float $amount
     */
    private function registerShippingAmount($shippingPrice)
    {
        $this->_coreRegistry->unregister(\Ced\Fruugo\Model\Carrier\Shipfruugocom::REGISTRY_INDEX_SHIPPING_TOTAL);

        $this->_coreRegistry->register(
            \Ced\Fruugo\Model\Carrier\Shipfruugocom::REGISTRY_INDEX_SHIPPING_TOTAL,
            $shippingPrice
        );
    }


}
