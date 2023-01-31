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
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Fruugo\Block\Adminhtml\Category\Tabs;

class Form extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    public $_systemStore;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    public $_wysiwygConfig;

    public $_template = 'categories/form.phtml';

    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $collectionFactory
     * @param \Magento\Catalog\Helper\Catalog $helperCatalog
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $collectionFactory,
        \Magento\Catalog\Helper\Catalog $helperCatalog,
        \Magento\Framework\Registry $registry,
        $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_coreRegistry = $registry;
        $this->_helperCatalog = $helperCatalog;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    public function getCurrentCategory()
    {
        return $this->_coreRegistry->registry('current_category')->getEntityId();
    }

    public function getRootCategory()
    {
        return $this->_coreRegistry->registry('current_category')->getParentId();
    }

    /**
     * Get current level of fruugo category
     * @param integer $level
     * @return string
     */
    public function getLevel($level)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $collection = $objectManager->create('Ced\Fruugo\Model\Categories')
            ->getCollection()
            ->addFieldToSelect(['id', 'cat_id', 'parent_cat_id', 'name', 'path'])
            ->addFieldToFilter('level', $level);
        return $collection->getData();
    }

    /**
     * Get current mapped fruugo category
     * @param integer $level
     * @return string
     */
    public function getSavedCategoryData($level)
    {
        $magentoCatId = $this->getCurrentCategory();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $obj = $objectManager->create('Ced\Fruugo\Model\Categories')
            ->getCollection()
            ->addFieldToFilter(['magento_cat_id'], [[ 'like' => "%,".$magentoCatId.",%" ]])
            ->getFirstItem();
        if ($level == '0') {
            return $obj->getData('parent_cat_id');
        } else {
            return $obj->getData('path');
        }
    }


}