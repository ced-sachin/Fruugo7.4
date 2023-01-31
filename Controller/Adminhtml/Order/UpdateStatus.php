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

class UpdateStatus extends \Magento\Backend\App\Action
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
        $this->messageManager = $this->_objectManager->get ( 'Magento\Framework\Message\ManagerInterface' );
    }

    /**
     * Ship action
     * @return void
     */
    public function execute()
    {
        $orderID = $this->getRequest()->getPost('orderid');
        $fruugo_order_row = $this->getRequest()->getPost('order_table_row');
        $order = $this->_objectManager->get( 'Magento\Sales\Model\Order' )->loadByIncrementId( $orderID );
        //echo "<pre>";print_r($order->getData());die('fg');
        $fruugomodel = $this->_objectManager->get ( 'Ced\Fruugo\Model\FruugoOrders' )->load ( $fruugo_order_row );
        $this->checkAndSaveOrderStatus($order, $fruugomodel);
    }
    public function checkAndSaveOrderStatus($order, $fruugomodel) {
        $orderComplete = true;
        $orderItems = $order->getAllItems();
        foreach($orderItems as $item) {
            //echo "<pre>";print_r($item->getData());
            $processedOrder = (int) $item->getQtyShipped() + (int) $item->getQtyRefunded() + (int) $item->getQtyCanceled();
            if( $processedOrder < (int) $item->getQtyOrdered() ) {
                $orderComplete = false;
            }
        }

        //var_dump($orderComplete);
        if($orderComplete) {
            $order->setStatus( 'complete' )->save();
            $fruugomodel->setStatus( 'Complete' )->save();
            $this->messageManager->addSuccessMessage ( 'Your Fruugo Order ' . $order->getId() . ' has been Completed.' );
        } else {
            $fruugomodel->setStatus( 'inProgress' )->save();
            $this->messageManager->addSuccessMessage ( 'Your Fruugo Order ' . $order->getId() . ' is under progress.' );
        }
        //die('fg');
        return $orderComplete;
        //die('gf');
    }
}