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
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Fruugo\Controller\Adminhtml\Products;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class SyncFeeds extends \Magento\Backend\App\Action
{
    /**
     * Result Page factory
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * Feeds Model
     * @var \Ced\Fruugo\Model\FeedsFactory
     */
    public $feedsFactory;

    /**
     * Logger
     * @var $logger \Psr\Log\LoggerInterface
     */
    public $logger;

    /**
     * Data Helper
     * @var $helper
     */
    public $helper;

    /**
     * SyncFeeds constructor.
     * @param Context $context
     * @param \Ced\Fruugo\Model\FeedsFactory $feedsFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Ced\Fruugo\Helper\Data $helper
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Ced\Fruugo\Model\FeedsFactory $feedsFactory,
        \Psr\Log\LoggerInterface $logger,
        \Ced\Fruugo\Helper\Data $helper,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->feedsFactory = $feedsFactory;
        $this->logger = $logger;
        $this->helper = $helper;
    }

    /**
     * Execute
     */
    public function execute()
    {
        $feedStatus = $this->fetchFeeds();
        if ($feedStatus) {
            $this->messageManager->addSuccessMessage('Feeds Synced Successfully');
        } else {
            $this->messageManager->addErrorMessage('Feeds Sync Failed');
        }
        $this->_redirect('fruugo/products/feeds');

    }

    /**
     * Feeds Sync
     * @return bool
     */
    public function fetchFeeds()
    {
        try {
            $feeds = $this->helper->getFeeds();
            if (isset($feeds['results'] )){
                foreach ( $feeds['results'] as $feed ) {
                    $feedModel = $this->feedsFactory->create();
                    $checkFeed = $feedModel->load($feed['feedId'], 'feed_id')->getData();
                    if (!empty($checkFeed)) {
                        $feedModel->load($feed['feedId'], 'feed_id');
                    } else {
                        $feedModel->setData('feed_id', $feed['feedId']);
                    }
                    $feedModel->setData('feed_status', $feed['feedStatus']);
                    $feedModel->setData('items_received', $feed['itemsReceived']);
                    $feedModel->setData('items_succeeded', $feed['itemsSucceeded']);
                    $feedModel->setData('items_failed', $feed['itemsFailed']);
                    $feedModel->setData('items_processing', $feed['itemsProcessing']);
                    $feedModel->setData('feed_date', date( 'Y-m-d H:i:s', substr($feed['feedDate'], 0, 10)));
                    if (isset($feed['feedType'])) {
                        $feedModel->setData('feed_type', $feed['feedType']);
                    }
                    if (isset($feed['feedSource'])) {
                        $feedModel->setData('feed_source', $feed['feedSource']);
                    }
                    if (isset($feed['itemsFailed']) && $feed['itemsFailed'] > 0) {
                        $errors = $this->helper->getFeeds($feed['feedId'], true);
                        if (isset($errors['elements']['itemDetails'])) {
                            $feedModel->setData('feed_errors', json_encode($errors['elements']['itemDetails']));
                        }
                    }
                    $feedModel->save();
                }

            }
            return true;
        }
        catch (\Exception $e) {
            $this->logger->debug("Fruugo Feeds Sync Failed : fetchFeeds : " . $e->getMessage());
            return false;
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