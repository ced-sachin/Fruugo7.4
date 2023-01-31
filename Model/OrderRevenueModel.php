<?php
namespace Ced\Fruugo\Model;
use Ced\Fruugo\Api\OrderRevenue;

class OrderRevenueModel implements OrderRevenue
{
    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Ced\Fruugo\Helper\Data $data,
        \Ced\Fruugo\Helper\Order $orderHelper,
        \Magento\Framework\Json\Helper\Data $json,
        \Magento\Framework\App\RequestInterface $request
    )
    {
        $this->dataHelper = $data;
        $this->orderHelper = $orderHelper;
        $this->json = $json;
        $this->_request = $request;
    }


    /**
     * Returns order data to cedcommerce
     *
     * @api
     * @param string $currentMonth
     * @return json
     */
    public function getRevenueData(/*$dates*/) {
        /*if($currentMonth == "1") {
            $order = $this->orderHelper->getCurrentMonthOrder(true);
        } else {
            $order = $this->orderHelper->getCurrentMonthOrder(false);
        }*/
        $dates = $this->_request->getParams();
        $order = $this->orderHelper->getCurrentMonthOrder($dates);
        $returnData = $this->dataHelper->getTotalStock();
        $returnData['revenueTotal'] = $order;
        //echo "<pre>";print_r($returnData);die('dfg');
        return $this->json->jsonEncode($returnData);
    }
}