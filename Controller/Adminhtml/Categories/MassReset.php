<?php

namespace Ced\Fruugo\Controller\Adminhtml\Categories;

use Magento\Backend\Model\View\Result\Redirect;

class MassReset extends \Magento\Backend\App\Action
{
    /**
     * Filter
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    public $filter;

    /**
     * Categories Collection
     * @var \Ced\Fruugo\Model\ResourceModel\Categories\CollectionFactory
     */
    public $collectionFactory;

    /**
     * MassReset constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Ced\Fruugo\Model\ResourceModel\Categories\CollectionFactory $collectionFactory
     * @param \Ced\Fruugo\Controller\Adminhtml\Categories\FilterCustom $filter
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Ced\Fruugo\Model\ResourceModel\Categories\CollectionFactory $collectionFactory,
        \Ced\Fruugo\Controller\Adminhtml\Categories\FilterCustom $filter
    ) {
        $this->filter = $filter;
        $this->collectionFactory =  $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Execute
     * @return Redirect
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $categoryReset = 0;
        foreach ($collection->getItems() as $category) {
            $id = $category->getId();
            $categoryFactory = $this->_objectManager->create('Ced\Fruugo\Model\Categories');
            $categoryFactory->load($id);
            $categoryFactory->setData('magento_cat_id', null);
            $categoryFactory->save();
            $categoryReset++;
        }
        $this->messageManager->addSuccessMessage(
            __('A total of %1 record(s) have been Reset.', $categoryReset)
        );

        return $this->_redirect('*/*/index');
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
