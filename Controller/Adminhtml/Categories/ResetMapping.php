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

class ResetMapping extends \Magento\Backend\App\Action
{
    /**
     * ResultPageFactory
     * @var PageFactory
     */
    public $resultPageFactory;

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
     * Collection
     * @var $collection
     */
    public $collection;

    /**
     * ResetMapping constructor.
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
    }

    /**
     * Execute
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $categoryId = $this->getRequest()->getParam('id');
        try
        {
//            $category = $this->collection
//                ->create()->addFieldToFilter('magento_cat_id', $categoryId);
//            foreach ($category as $value) {
//                $id = $value->getId('0');
//                $categoryModel = $this->categoriesFactory->create()->load($id);
//                $categoryModel->setData('magento_cat_id', null);
//                $categoryModel->save();
//            }
            $this->checkMagentoCatIdExist($categoryId);
            $this->messageManager->addSuccessMessage(__('Category Reset Successfull.'));
            return $this->_redirect('catalog/category/edit/', ['id' => $categoryId]);

        }
        catch (\Exception $e)
        {
            $this->messageManager->addErrorMessage(__('Category Reset Failed.'));
            return $this->_redirect('catalog/category/edit/', ['id' => $categoryId]);

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

    /**
     * @param $currentCatId
     * @return bool
     */
    public function checkMagentoCatIdExist($currentCatId)
    {
        $mageCatExists = $this->_objectManager->create('Ced\Fruugo\Model\Categories')->getCollection()->addFieldToFilter(['magento_cat_id'], [[ 'like' => "%,".$currentCatId.",%" ]])->getData();
        foreach ($mageCatExists as $mageCatExist){
            if (isset($mageCatExist['magento_cat_id']) && $mageCatExist['magento_cat_id']){
                $mageCatExist['magento_cat_id'] = substr($mageCatExist['magento_cat_id'], 1, (strlen($mageCatExist['magento_cat_id']) - 2));
                $temp = array_flip(explode(',',$mageCatExist['magento_cat_id']));
                unset($temp[$currentCatId]);
                $mageCatId = null;
                if (count($temp) > 0) {
                    $mageCatId = ','.implode(',',array_flip($temp)).',';
                }
                $id = $mageCatExist["id"];
                $model = $this->_objectManager->create('Ced\Fruugo\Model\Categories');
                $model->load($id);
                $model->setdata('magento_cat_id', $mageCatId)->save();
            }
        }

    }

}