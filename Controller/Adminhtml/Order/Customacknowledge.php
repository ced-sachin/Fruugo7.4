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

class Customacknowledge extends \Magento\Backend\App\Action
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
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Ced\Fruugo\Helper\Data $dataHelper,
        \Ced\Fruugo\Model\FruugoOrders $fruugoOrders,
        \Magento\Sales\Model\Order $order,
        \Magento\Framework\Json\Helper\Data $json
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->scopeConfigManager = $this->_objectManager->get ( 'Magento\Framework\App\Config\ScopeConfigInterface' );
        $this->dataHelper = $dataHelper;
        $this->fruugoOrder = $fruugoOrders;
        $this->salesOrder = $order;
        $this->json = $json;

    }

    /**
     * Ship action
     * @return void
     */
    public function execute()
    {
        $fruugo_order_row = $this->getRequest()->getPost('order_table_row');
        $id = $this->getRequest()->getPost('key1');
        $orderid = $this->getRequest()->getPost('order');
        $order_id = $this->getRequest()->getPost('orderid');
        $msg_to_cst = $this->getRequest()->getPost('msg_to_cst');
        $msg_to_fruugo = $this->getRequest()->getPost('msg_to_fruugo');
        $items_data = $this->getRequest()->getPost('items');
        $items_data = json_decode($items_data);
        $item_confirmed = '';
        $data_ack = [];
        if (count($items_data) == 0) {
            $message = "You have no any item in your Order.";
            $this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($message));
            return;
        }
        foreach ($items_data as $item) {
            $item_confirmed .= '&'.$item[1].','.$item[2].','.$item[4];
            $ack_arr[] = array(
                'merchant_sku' => $item[0],
                'fruugo_product_id' => $item[1],
                'fruugo_sku_id' => $item[2],
                'ack_qty' => $item[4],
            );
        }
        if($msg_to_cst!='' && $item_confirmed!='')
        {
            $item_confirmed.='&messageToCustomer='.$msg_to_cst;
        }
        if($msg_to_fruugo!='' && $item_confirmed!='')
        {
            $item_confirmed.='&messageToFruugo='.$msg_to_fruugo;
        }
        /*$confirmed_items_arr[] = array('orderId' => $order_id,
            'item' => $item_confirmed
        );*/
        $confirmed_items_arr = "orderId= $order_id".$item_confirmed;
        //echo "<pre>";print_r($confirmed_items_arr);die('f');
        if(count($confirmed_items_arr)>0) {
            $responseSimple = $this->dataHelper->postRequest('orders/confirm', $confirmed_items_arr);
            //$responseSimple = Mage::helper('fruugo')->CPostRequest('orders/confirm', $confirmed_items_arr);
        }
        try {
            $fruugomodel = $this->fruugoOrder->load( $fruugo_order_row );
            //$fruugomodel = Mage::getModel('fruugo/fruugoorder')->load($fruugo_order_row);
            $ack_dbdata = $fruugomodel->getAcknowledgeData();
            $data_ack['acknowledge'][] = array(
                'acknowledged_items' => $ack_arr
            );
            if (isset($ack_dbdata)) {
                $temp_arr = json_decode($ack_dbdata,true);
                $temp_arr["acknowledge"][] = $data_ack["acknowledge"][0];
            } else {
                $temp_arr = $data_ack;
            }
            $fruugomodel->setData('acknowledge_data', json_encode($temp_arr,true));
            $fruugomodel->save();
            $order = $this->salesOrder->load($orderid);

            foreach ($order->getAllVisibleItems() as $item) {
                $ship_sku = $item->getSku();
                foreach ($ack_arr as $ackItem) {
                    if ($ackItem['merchant_sku'] == $ship_sku) {
                        $ackQty[$item->getId()] = $ackItem['ack_qty'];
                    }
                }
            }
            //echo "<pre>";print_r($ackQty);die('f');
            $this->generateInvoice($order, $ackQty);
            $message = "Success";
            //$this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody($this->json->jsonEncode($message));
            return;
            //return "Success";
        } catch (Exception $e) {
            $this->getResponse()->setBody($e->getMessage());
            return;
            //Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
    }
    public function generateInvoice($order, $itemQty) {
        $invoice = $this->_objectManager->create (
            'Magento\Sales\Model\Service\InvoiceService' )->prepareInvoice (
            $order, $itemQty );
        $invoice->register();
        $invoice->save();
        $transactionSave = $this->_objectManager->create (
            'Magento\Framework\DB\Transaction' )->addObject (
            $invoice )->addObject ( $invoice->getOrder () );
        $transactionSave->save ();
        $order/*->addStatusHistoryComment ( __ ( 'Notified customer about invoice #%1.'
            , $invoice->getId () ) )*/->setIsCustomerNotified ( false )->save ();
        $order->setStatus ( 'processing' )->save ();
    }
}