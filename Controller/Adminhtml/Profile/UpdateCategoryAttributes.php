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
namespace Ced\Fruugo\Controller\Adminhtml\Profile;
use Magento\Framework\View\Result\PageFactory;
 
class UpdateCategoryAttributes extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    protected $_coreRegistry;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * Vendor grid for AJAX request
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {

        $profileId = $this->getRequest()->getParam('profile_id');
        $p_id = $this->getRequest()->getParam('p_id');
        $c_id = $this->getRequest()->getParam('c_id');
        $collection = $this->_objectManager->get('Ced\Fruugo\Model\Profile')->getCollection()->addFieldToFilter('id', $profileId)
            ->addFieldToFilter('profile_category_level_1', $p_id)
            ->addFieldToFilter('profile_category_level_2', $c_id);
        if(count($collection)>0){
            $profile = $collection->getFirstItem();
            $this->_coreRegistry->register('current_profile', $profile);
        }
        $result = $this->resultPageFactory->create(true)->getLayout()->createBlock('Ced\Fruugo\Block\Adminhtml\Profile\Edit\Tab\Attribute\Requiredattribute')->setPId($p_id)->setCId($c_id)->toHtml();
        $this->getResponse()->setBody($result);
    }
       
}
