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

namespace Ced\Fruugo\Controller\Adminhtml\System\Config\Validate;

class Validate extends \Magento\Backend\App\Action
{
    /**
     * JsonFactory
     * @var JsonFactory
     */
    public $resultJsonFactory;

    /**
     * DataHelper
     * @var \Ced\Fruugo\Helper\Data
     */
    public $dataHelper;

    /**
     * Validate Constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Ced\Fruugo\Helper\Data $data
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Ced\Fruugo\Helper\Data $data
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->dataHelper = $data;
    }

    /**
     * Check Fruugo Api Credentials are Valid
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $url = $this->getRequest()->getParam('url');
        $url = $url.'v3/orders?createdStartDate='.$this->getRequest()->getParam('createdStartDate');
        $privateKey = $this->getRequest()->getParam('secretKey');
        $channelId= $this->getRequest()->getParam('channelid');
        $consumerId = $this->getRequest()->getParam('consumerid');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $signatureHelper = $objectManager->create('Ced\Fruugo\Helper\Signature');
        $signature = $signatureHelper->getSignature($url, 'GET', null, $consumerId, $privateKey);
        $timestamp = $signatureHelper->timestamp;

        $params= [
            'consumer_id'=> $this->getRequest()->getParam('consumerid'),
            'url' =>  $url,
            'signature' => $signature,
            'timestamp' => $timestamp,
            'headers' => 'WM_CONSUMER.CHANNEL.TYPE: '. $channelId,
        ];
        $response = $this->dataHelper->getRequest($url, $params);
        if ($this->dataHelper->debugMode) {
            $this->dataHelper->fruugoLogger
                ->debug("Fruugo->Controller->Config->Validate->Validate.php->execute() :  \n Request :".
                    " \n URL: ". $url.
                    "\n Body : \n". var_export($params, true) .
                    "\n Response : \n " . var_export($response, true)
                );
        }
        if (preg_match('/(HTTP\/1\.1\ 200\ OK)|(CONTENT\_NOT\_FOUND\.GMP\_ORDER\_API)/', $response)) {
            $valid = 1;
            $message= "successfully validated details.";
        } elseif (preg_match('/WM\_CONSUMER\.CHANNEL\.TYPE\ set\ null\ or\ invalid/', $response)) {
            $valid = 0;
            $message= "validation error : Consumer Channel Id is Invalid";
        } else {
            $valid = 0;
            $message= "validation error : Please Check the above details";
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData([
            'valid' => $valid,
            'message' => $message,
            'response' => $response
        ]);
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
