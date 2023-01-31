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
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Fruugo\Controller\Adminhtml\Logs;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Truncate extends \Magento\Backend\App\Action
{
    /**
     * ResultPageFactory
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * Helper
     * @var PageFactory
     */
    public $helper;

    /**
     * FailedOrders constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Ced\Fruugo\Helper\Order $helper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Execute
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $dataPost =$this->getRequest()->getParams();
        //print_r($dataPost);die("data");
        if (!empty($dataPost)) {
            try {
                $model = $this->_objectManager->create('Ced\Fruugo\Model\FruugoLogs');
                $connection = $model->getCollection()->getConnection();
                $tableName = $model->getCollection()->getMainTable();
                $connection->truncateTable($tableName);
                $this->messageManager->addSuccess(
                    __('Logs Record Delete Succesfully')
                );
                return $this->_redirect('fruugo/logs/loggrid');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Logs Not Deleted, Please Check Error'));
                $this->_redirect('fruugo/logs/loggrid');
            }
        }
        else{
            $this->messageManager->addErrorMessage(__('Log Records Not Send, Please Check!!!'));
            $this->_redirect('fruugo/logs/loggrid');
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