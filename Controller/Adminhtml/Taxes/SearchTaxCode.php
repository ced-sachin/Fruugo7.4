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

namespace Ced\Fruugo\Controller\Adminhtml\Taxes;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\PageFactory;

class SearchTaxCode extends \Magento\Backend\App\Action
{
    /**
     * RJF
     * @var JsonFactory
     */
    public $resultJsonFactory;

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @throws NotFoundException
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory

    )
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Execute
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $model =  $this->_objectManager->create('\Ced\Fruugo\Model\ResourceModel\FruugoTaxCodes\Collection');
        $searchString = '%'.$this->getRequest()->getParam('search').'%';
        if(!$searchString){
            return false;
        }
        $model->addFieldToFilter(['cat_desc', 'sub_cat_desc'],
            [
                ['like'=>$searchString],
                ['like'=>$searchString]
            ]
        );
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData([
            'result' => $model->getData(),
            'size' => $model->getSize(),
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