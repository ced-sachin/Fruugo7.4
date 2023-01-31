<?php

namespace Ced\Fruugo\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action {

    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Ced\Fruugo\Helper\Data $data,
        \Ced\Fruugo\Helper\Order $orderHelper,
        \Magento\Framework\Json\Helper\Data $json
    )
    {
        $this->dataHelper = $data;
        $this->orderHelper = $orderHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->json = $json;
        parent::__construct($context);
    }

    /**
     * Execute Index Action
     *
     * @return json
     */
    public function execute()
    {
        $dataHelper = $this->_objectManager->create('\Ced\Fruugo\Helper\Data');
        $storeManager = $this->_objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $dataHelper->registerDomain($storeManager->getStore()->getBaseUrl());
        die('gh');
        $returnData = [];
        $currentMonth = $this->getRequest()->getParam('currentMonth');
        if($currentMonth == "1") {
            $order = $this->orderHelper->getCurrentMonthOrder(true);
        } else {
            $order = $this->orderHelper->getCurrentMonthOrder(false);
        }
        $returnData = $this->dataHelper->getTotalStock();
        $returnData['revenueTotal'] = $order;
        //echo "<pre>";print_r($returnData);die('dfg');
        return $this->json->jsonEncode($returnData);
    }
}
