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

namespace Ced\Fruugo\Controller\Adminhtml\Categories;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Reset extends \Magento\Backend\App\Action
{
    /**
     * ResultPageFactory
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * Collection
     * @var $collection
     */
    public $collection;

    /**
     * Categroies
     * @var $categories
     */
    public $categoriesFactory;

    /**
     * DataHelper
     * @var $dataOpenHelper
     */
    public $dataHelper;

    /**
     * Reset constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Ced\Fruugo\Model\ResourceModel\Categories\CollectionFactory $collectionFactory
     * @param \Ced\Fruugo\Model\CategoriesFactory $categoriesFactory
     * @param \Ced\Fruugo\Helper\Data $data
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Ced\Fruugo\Model\ResourceModel\Categories\CollectionFactory $collectionFactory,
        \Ced\Fruugo\Model\CategoriesFactory $categoriesFactory,
        \Ced\Fruugo\Helper\Data  $data

    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->dataHelper = $data;
        $this->collection = $collectionFactory;
        $this->categoriesFactory = $categoriesFactory;
    }

    /**
     * Execute
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try
        {
            $error = [];

            $data = $this->collection->create();
            $dataFull = $this->collection->create();

            //save db data to json file
            if (!($this->dataHelper->createFile($data->addFieldToSelect(['cat_id', 'magento_cat_id'])->getData(),
                ['name' => 'categories_mapped_backup'])
            )) {
                $error[] = ['categories_mapped_backup.json creation failed'];
            }

            //save full db data to json file
            $this->dataHelper->createFile($dataFull->getData(), ['name' => 'categories_mapped_backup_full']);

            //truncate fruugo categories table
            //$this->_collection->create()->walk('delete');

            foreach ($data as $value) {
                $value->setMagentoCatId(null);
                $value->save();
            }
            $this->messageManager->addSuccessMessage(__('Categories Reset Successfully.'));
            return $resultRedirect->setPath('*/*/');

        }
        catch (\Exception $e)
        {
            $this->messageManager->addErrorMessage(__('Categories Reset Failed.' . $e));
            return $resultRedirect->setPath('*/*/');

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