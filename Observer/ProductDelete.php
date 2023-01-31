<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Fruugo
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Fruugo\Observer;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Event\ObserverInterface;

class ProductDelete implements ObserverInterface
{

    /**
     * Obj Manager
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * Message Manager
     * @var \Magento\Framework\Message\ManagerInterface
     */
    public $messageManager;

    /**
     * Logger
     * @var \Magento\Framework\Logger\Monolog
     */
    public $logger;

    /**
     * ProductDelete constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Logger\Monolog $logger
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Logger\Monolog $logger,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collectionFactory
    ) {
        $this->objectManager = $objectManager;
        $this->messageManager = $messageManager;
        $this->collectionFactory =  $collectionFactory;
        $this->request = $request;
        $this->registry  = $registry;
        $this->logger = $logger;
    }

    /**
     * Catalog product save after event handler, Retires Product on Fruugo on Delete
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
          $product = $observer->getEvent()->getData('product');
        $checkSku = $this->objectManager->get('Ced\Fruugo\Helper\Data')->getItem($product->getSku());
        if ($checkSku) {
            $requestSent =  $this->objectManager->get(
                'Ced\Fruugo\Helper\Data')->deleteRequest('v2/items/'. $product->getSku());

            $response = json_decode($requestSent, true);
            $response = empty($response) ? false : $response;
            if (isset($response['message'])) {

                $this->messageManager
                    ->addSuccessMessage('Retire Request for '. $product->getSku() .' has been sent to Fruugo' .
                        $requestSent['message']);
            } elseif (isset($response['error'][0]['description'])) {
                $this->messageManager->addErrorMessage($response['error'][0]['description']);
            } else {
                $this->logger->addDebug('ProductDelete Observer: ' . $requestSent);
            }
        }
    }
}
