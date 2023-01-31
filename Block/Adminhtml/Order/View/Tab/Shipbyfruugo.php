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

namespace Ced\Fruugo\Block\Adminhtml\Order\View\Tab;


class Shipbyfruugo extends \Magento\Sales\Block\Adminhtml\Order\AbstractOrder implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Order Ship By fruugo tab
     */

    /**
     * Ship By fruugo form template
     *
     * @var string
     */
    public $_template = 'order/shipbyfruugo/custom_tab.phtml';


    /**
     * Get ObjectManager instance
     *
     * @return \Magento\Framework\App\ObjectManager
     */
    public function getObjectManager()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance ();
        return $objectManager;
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * GetHelper
     * @param string $helper
     * @return string
     */
    public function getHelper($helper)
    {
        $helper = $this->getObjectManager()->get( "Ced\Fruugo\Helper".$helper );
        return $helper;
    }

    /**
     * Retrieve FruugoOrders model instance
     *
     * @return \Ced\Fruugo\Model\FruugoOrders
     */
    public function getModel()
    {
        $incrementId = $this->getOrder()->getIncrementId ();
        $resultdata = $this->getObjectManager()->get( 'Ced\Fruugo\Model\FruugoOrders' )
            ->getCollection ()->addFieldToFilter ( 'magento_order_id', $incrementId );

        return $resultdata;
    }


    /**
     * Retrieve FruugoOrders model instance
     *
     * @return \Ced\Fruugo\Model\FruugoOrders
     */

    public function setOrderResult($resultdata)
    {
        return $this->_coreRegistry->register('current_fruugo_order', $resultdata);
    }

    /**
     * Retrieve source model instance
     *
     * @return \Magento\Sales\Model\Order
     */

    public function getSource()
    {
        return $this->getOrder();
    }

    /**
     * Retrieve order totals block settings
     *
     * @return string[]
     */

    public function getOrderTotalData()
    {
        return [
            'can_display_total_due' => true,
            'can_display_total_paid' => true,
            'can_display_total_refunded' => true
        ];
    }

    /**
     * Get order info data
     *
     * @return string[]
     */

    public function getOrderInfoData()
    {
        return ['no_use_order_link' => true];
    }

    /**
     * Get tracking html
     *
     * @return string
     */

    public function getTrackingHtml()
    {
        return $this->getChildHtml('order_tracking');
    }

    /**
     * Get items html
     *
     * @return string
     */

    public function getItemsHtml()
    {
        return $this->getChildHtml('order_items');
    }

    /**
     * Retrieve gift options container block html
     *
     * @return string
     */

    public function getGiftOptionsHtml()
    {
        return $this->getChildHtml('gift_options');
    }

    /**
     * Get payment html
     *
     * @return string
     */

    public function getPaymentHtml()
    {
        return $this->getChildHtml('order_payment');
    }

    /**
     * View URL getter
     *
     * @param int $orderId
     * @return string
     */

    public function getViewUrl($orderId)
    {
        return $this->getUrl('sales/*/*', ['order_id' => $orderId]);
    }

    /**
     * ######################## TAB settings #################################
     */

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Ship By Fruugo');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Ship By Fruugo');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        $data = $this->getModel();
        if (count($data) > 0) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        $data = $this->getModel();
        if (count($data) > 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * parserArray
     * {@inheritdoc}
     */
    public function parserArray($shipmentData = [])
    {
        if (!empty($shipmentData)) {
            $arr = [];
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
                $shipmentData["orderLines"]['orderLine'][$key]['orderLineQuantity']['shipQuantity'] = $shipQuantity;
                $shipmentData["orderLines"]['orderLine'][$key]['orderLineQuantity']['amount'] = $quantity;
                $shipmentData["orderLines"]['orderLine'][$key]['orderLineQuantity']['cancelQuantity'] = $cancelQuantity;
            }
            return $shipmentData;
        }
        return false;
    }



    /**
     * {@inheritdoc}
     */
    public function getCancelledOrder($purchaseOrderId)
    {
        $cancelledData = $this->getObjectManager()->get( 'Ced\Fruugo\Model\FailedFruugoOrders' )->getCollection ()
            ->addFieldToSelect('reason')
            ->addFieldToFilter ( 'purchase_order_id', $purchaseOrderId )
            ->getData();
        if (count($cancelledData) > 0) {
            return $cancelledData;
            //return false;
        } else {
            return false;
        }
    }
}
