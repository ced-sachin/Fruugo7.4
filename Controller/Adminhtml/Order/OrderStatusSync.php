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

class OrderStatusSync extends \Magento\Backend\App\Action
{
    /**
     * ResultPageFactory
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;
    public $messageManager;
    /**
     * Authorization level of a basic admin session
     * @var Constant
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_Fruugo::fruugo_products_index';

    public $filter;

    /**
     * MassCancel constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Ui\Component\MassAction\Filter $filter
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->filter = $filter;
    }


/**
* @param \Magento\Framework\Message\ManagerInterface $messageManager
* @return void
*/

    public function _construct(
        \Magento\Framework\Message\ManagerInterface $messageManager

    )
    {
        $this->messageManager = $messageManager;

    }

    /**
     * Execute
     * @return  void
     */
    public function execute()
    {
       /* if(!$this->_objectManager->create('\Ced\Fruugo\Helper\Data')->checkForConfiguration()) {
            $this->messageManager->addErrorMessage('API not Enabled or Details are Invalid. Please Check Fruugo configuration');
            $this->_redirect('fruugo/orders/listorder');
            return;
        }*/
        $dataHelper = $this->_objectManager->get('Ced\Fruugo\Helper\Data');
        $collection = $this->filter->getCollection($this->_objectManager->create('Ced\Fruugo\Model\FruugoOrders')
            ->getCollection());
        

        $purchaseOrderids = $collection->getAllIds();

          
            foreach ($purchaseOrderids as $id) {
               $fruugoorder = $this->_objectManager->create('Ced\Fruugo\Model\FruugoOrders')->load($id);
              
                $response[] = $dataHelper->getOrder($fruugoorder->getPurchaseOrderId());
            }
        


        $ordercollection = $this->_objectManager->create('Ced\Fruugo\Model\FruugoOrders')->getCollection();

        foreach ($ordercollection as $order) {
            foreach ($response as $Orderres) {
                if (isset($Orderres['elements']['order'])) {
                    foreach ($Orderres['elements']['order'] as $res) {
                        if ($order->getPurchaseOrderId() == $res['purchaseOrderId']) {  
                            
                            //get fruugo status
                            $status = $res['orderLines']['orderLine'][0]['orderLineStatuses']['orderLineStatus'][0]['status'];

                            //already shipped case
                            if (($order->getStatus()=='Acknowledged' && $status=='Shipped') || $order->getStatus()=='Already Shipped') {
                                $fruugostatus = 'Already Shipped';

                                //save fruugo status
                                $this->_objectManager->create('Ced\Fruugo\Model\FruugoOrders')->load($order->getPurchaseOrderId(),'purchase_order_id')->setStatus($fruugostatus)->save();
                            } else { 
                                $fruugostatus = $status=='Shipped' ? 'Complete' : $status;

                                //save fruugo status
                                $this->_objectManager->create('Ced\Fruugo\Model\FruugoOrders')->load($order->getPurchaseOrderId(),'purchase_order_id')->setStatus($fruugostatus)->save();
                            }
                        }
                    }
                } 
            }
        } 


      

        if (count($purchaseOrderids) == 0) {
            $this->messageManager->addErrorMessage('No Order selected to Sync.');
            $this->_redirect('fruugo/orders/listorder');
            return;
        }

     $this->messageManager->addSuccessMessage(count($purchaseOrderids).' Orders synced successfully from fruugo.com.');
     $this->_redirect('*/*/listorder');

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
