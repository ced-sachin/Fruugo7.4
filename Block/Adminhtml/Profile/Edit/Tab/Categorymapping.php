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
 * @package     Ced_CsGroup
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Fruugo\Block\Adminhtml\Profile\Edit\Tab;

/**
 * Rolesedit Tab Display Block.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Categorymapping extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'Ced_Fruugo::profile/categorymapping.phtml';


    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Acl\RootResource $rootResource
     * @param \Magento\Authorization\Model\ResourceModel\Rules\CollectionFactory $rulesCollectionFactory
     * @param \Magento\Authorization\Model\Acl\AclRetriever $aclRetriever
     * @param \Magento\Framework\Acl\AclResource\ProviderInterface $aclResourceProvider
     * @param \Magento\Integration\Helper\Data $integrationData
     * @param array $data
     */
    /*public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Acl\RootResource $rootResource,
        \Magento\Authorization\Model\ResourceModel\Rules\CollectionFactory $rulesCollectionFactory,
        \Magento\Authorization\Model\Acl\AclRetriever $aclRetriever,
        \Magento\Framework\Acl\AclResource\ProviderInterface $aclResourceProvider,
    	\Magento\Framework\App\Config\ScopeConfigInterface $scopeinterface,
        \Magento\Integration\Helper\Data $integrationData,
    	
        array $data = []
    ) {
        $this->_aclRetriever = $aclRetriever;
        $this->_rootResource = $rootResource;
        $this->_rulesCollectionFactory = $rulesCollectionFactory;
        $this->_aclResourceProvider = $aclResourceProvider;
        $this->_integrationData = $integrationData;
        $this->_scopeConfig = $scopeinterface;
        parent::__construct($context, $data);
    }
    */


    public function getFruugoCategoryId($field='csv_parent_id'){
        $category_id=Mage::app()->getRequest()->getParam('id');

        $value = $this->_objectManager->create('Ced\Fruugo\Model\Categories')->getCollection()->addFieldToFilter('magento_cat_id',$category_id)->getFirstItem();

        $fruugo_mapped_id=$value->getData($field);
        $fruugo_mapped_id=($fruugo_mapped_id === 0?'':$fruugo_mapped_id);

        return $fruugo_mapped_id;
    }

    public function getFilteredFruugoCollection($level){
        return $this->_objectManager->create('Ced\Fruugo\Model\Categories')->getCollection()->addFieldToFilter('level' , $level);
    }
}
