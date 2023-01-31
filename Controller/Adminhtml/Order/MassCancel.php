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

class MassCancel extends \Magento\Backend\App\Action
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
        $dataHelper = $this->_objectManager->get('\Ced\Fruugo\Helper\Data');
        $collection = $this->filter->getCollection($this->_objectManager->create('\Ced\Fruugo\Model\FruugoOrders')
            ->getCollection());
        $purchaseOrderids = $collection->getData();

        if (count($purchaseOrderids) == 0) {
            $this->messageManager->addErrorMessage('No Orders To Cancel.');
            $this->_redirect('fruugo/order/listorder');
            return;
        } else{
            $counter = 0;
            foreach ($purchaseOrderids as $purchaseOrderid) {
                $orderData = json_decode($purchaseOrderid['order_data'],true);
                $poId = $purchaseOrderid['purchase_order_id'];
                $magentoOrderId = $purchaseOrderid['magento_order_id'];
                if(is_null($orderData) && empty($orderData['orderLines']['orderLine']) ) {
                    continue;
                }
                $counter++;
                foreach ($orderData['orderLines']['orderLine'] as $product) {
                    $lineNumber = $product['lineNumber'];
                    $dataHelper->rejectOrder($poId,$lineNumber);
                }
                $this->orderManagement->cancel($this->order->loadByIncrementId($magentoOrderId)->getEntityId());
                $this->_objectManager->create('\Ced\Fruugo\Model\FruugoOrders')->load($purchaseOrderid['id'])->setStatus('Cancelled')->save();
            }
            if ($counter) {
                $this->messageManager->addSuccessMessage($counter . ' Orders Cancellation Successfull');
                $this->_redirect('fruugo/order/listorder');
                return;
            } else {
                $this->messageManager->addErrorMessage('Orders Cancelellation Failed.');
                $this->_redirect('fruugo/order/listorder');
                return;
            }
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
