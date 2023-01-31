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

namespace Ced\Fruugo\Controller\Adminhtml\Attributes;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\PageFactory;

class Mapsave extends \Magento\Backend\App\Action
{
    /**
     * Page Factory
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @throws NotFoundException
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {   parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Execute
     * @return void
     */
    public function execute()
    {
        $data = $this->getRequest()->getParam('fruugo_attribute');
        $mappData = $this->getRequest()->getParam('fruugo_attribute_mapp');
        if (isset($data)) {
            $fruugoCodeCol = $this->_objectManager->create('Ced\Fruugo\Model\Attributes')->getCollection();
            $model = $this->_objectManager->create('Ced\Fruugo\Model\Attributes');
            foreach ($fruugoCodeCol as $fruugoModel) {
                $model->load($fruugoModel->getId());
                $realKey = array_search ($model->getFruugoAttributeName(), $data);
                if ($realKey !== false) {
                    $model->setData('is_mapped', 1);
                    $model->setData('magento_attribute_code', $mappData[$realKey]);
                    $model->save();
                } else {
                    $model->setData('is_mapped', 0);
                    $model->setData('magento_attribute_code', '');
                    $model->save();
                }
            }
            $this->messageManager->addSuccessMessage(__('Simple Attributes Mapped Successfully.'));
        } else {
            $this->messageManager->addErrorMessage(__('No data Posted.'));
        }
        $this->_redirect('*/*/index');
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