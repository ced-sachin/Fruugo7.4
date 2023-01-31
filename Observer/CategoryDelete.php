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

use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ObserverInterface;

class CategoryDelete implements ObserverInterface
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
     * CategoryDelete constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param RequestInterface $request
     * @param ScopeConfig $scopeConfig
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        RequestInterface $request, ScopeConfig $scopeConfig,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
        $this->messageManager = $messageManager;
    }

    /**
     * Customer register event handler
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $magentoCategory = $observer->getEvent()->getCategory();
        $model = $this->objectManager->create('Ced\Fruugo\Model\Categories');
        $collection = $model->getCollection()->addFieldToFilter('magento_cat_id', $magentoCategory->getEntityId());
        foreach ($collection as $val) {
            $model->load($val->getId())
                ->setData('magento_cat_id', '0')
                ->save();
        }
    }
}
