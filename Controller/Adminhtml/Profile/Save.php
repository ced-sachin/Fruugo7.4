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
use Braintree\Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;


class Save extends \Magento\Backend\App\Action
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    protected $_coreRegistry;
    Protected $_configFactory;
    protected $_configStructure;
    protected  $_cache;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Config\Model\Config\Structure\Element\Group $group,
        \Magento\Config\Model\Config\Structure $configStructure,
        \Magento\Config\Model\Config\Factory $configFactory,
        \Ced\Fruugo\Helper\Cache $cache,
        \Magento\Config\Model\ResourceModel\Config $configResource,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
        PageFactory $resultPageFactory
    ) {

        parent::__construct($context);
        $this->scopeConfig = $this->_objectManager->create('\Magento\Framework\App\Config');
        $this->resultPageFactory = $resultPageFactory;
        $this->_configStructure = $configStructure;
        $this->_coreRegistry     = $coreRegistry;
        $this->_configFactory = $configFactory;
        $this->_configResource = $configResource;
        $this->_cache = $cache;
        $this->_cacheTypeList = $cacheTypeList;
        $this->_cacheFrontendPool = $cacheFrontendPool;
    }
    /**
     *
     * @param string $idFieldName
     * @return mixed
     */
    protected function _initProfile($idFieldName = 'id')
    {
        $profileId = $this->getRequest()->getParam($idFieldName);

        $profile = $this->_objectManager->create('Ced\Fruugo\Model\Profile');

        if ($profileId) {
            $profile->load($profileId);
        }

        $this->getRequest()->setParam('is_fruugo',1);
        $this->_coreRegistry->register('current_profile', $profile);
        return $this->_coreRegistry->registry('current_profile');
    }


    public function execute()
    {
       /* $data = $this->getRequest()->getParams();
        print_r($data['in_profile_products']);die("datafind");*/

        $data=$this->_objectManager->create('Magento\Config\Model\Config\Structure\Element\Group')->getData();
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_context = $this->_objectManager->get('Magento\Framework\App\Helper\Context');
        $redirectBack   = $this->getRequest()->getParam('back', false);
        $tab   			= $this->getRequest()->getParam('tab', false);
        $pcode        = $this->getRequest()->getParam('pcode', false);
        $profileId        = $this->getRequest()->getParam('id', false);
        $profileData  = $this->getRequest()->getPostValue();
//        $inventory_setting=$this->getRequest()->getParam('inventory_setting');
//        $inventory_threshold_value=$this->getRequest()->getParam('inventory_threshold_value');
//        $fixed_threshold_value=$this->getRequest()->getParam('fixed_threshold_value');
        $profileData=json_decode(json_encode($profileData),1);

        $profileProducts = $this->getRequest()->getParam('in_profile_products', null);
        if(strlen($profileProducts) > 0 ) {
            $profileProducts  = explode(',' ,$this->getRequest()->getParam('in_profile_products', null)
            );
        }else{
            $profileProducts = [];
        }

        try {
            $profile = $this->_initProfile('id');

            if (!$profile->getId() && $profileId) {
                $this->messageManager->addError(__('This Profile no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
            if (isset($profileData['profile_code'])) {
                $pcode = $profileData['profile_code'];
                $profileCollection = $this->_objectManager->get('Ced\Fruugo\Model\Profile') -> getCollection()->
                addFieldToFilter('profile_code', $profileData['profile_code']);
//               var_dump($profileCollection->getData());die();
                if (count($profileCollection) > 0) {
                    $this->messageManager->addError(__('This Profile Already Exist Please Change Profile Code'));
                    $this->_redirect('*/*/new');
                    return;
                }
            }

            $profile->addData($profileData);
            $requriedAttributes = array();
            if (isset($profileData['required_attributes'])){
                $temAttribute = $this->unique_multidim_array($profileData['required_attributes'], 'fruugo_attribute_name');
                $requriedAttributes['required_attributes'] = array_filter(array_map(function($n) { if($n['required']) return $n; }, $temAttribute));
                $requriedAttributes['optional_attributes'] = array_filter(array_map(function($n) { if(!$n['required']) return $n; }, $temAttribute));
            }

            if (isset($profileData['variant_attributes']))
                $requriedAttributes['variant_attributes'] = $this->unique_multidim_array($profileData['variant_attributes'], 'fruugo_attribute_name');

            if (isset($profileData['recommended_attribute']))
                $requriedAttributes['recommended_attribute'] = $this->unique_multidim_array($profileData['recommended_attribute'], 'fruugo_attribute_name');

            $profile->setProfileAttributeMapping(json_encode($requriedAttributes));
           // $profile->setInventorySetting($profileData['inventory_setting']);
            //$profile->setInventoryThresholdValue($profileData['inventory_threshold_value']);
            //$profile->setfixed_threshold_value($profileData['fixed_threshold_value']);
            $profile->save();
            $profileArray = $profile->getData();
            $profileArray['profile_attribute_mapping'] = json_decode( $profileArray['profile_attribute_mapping'],true);
            //cache values
            $this->_cache->setValue(\Ced\Fruugo\Helper\Cache::PROFILE_CACHE_KEY.$profile->getId(), $profileArray);

            /*$configData =
                [
                    'section' => 'fruugoconfiguration',
                    'website' => 0,
                    'store' => 0,
                    'groups' => $profileData['groups']
                ];
            $configModel = $this->_configFactory->create(['data' => $configData]);
            $configModel->save();*/
            //$configResourceModel = $objectManager->create('\Magento\Config\Model\ResourceModel\Config');
            
            if(isset($profileData['profile_use_price'])) {
                $this->_configResource->saveConfig("fruugoconfiguration/$pcode/use_vat_price", $profileData['profile_use_price'],'default',0);
                $types = array('config','layout','block_html','collections','reflection','db_ddl','eav','config_integration','config_integration_api','full_page','translate','config_webservice');
                foreach ($types as $type) {
                    $this->_cacheTypeList->cleanType($type);
                }
                foreach ($this->_cacheFrontendPool as $cacheFrontend) {
                    $cacheFrontend->getBackend()->clean();
                }
            }

            $oldProfileProducts = $this->_objectManager->create("Ced\Fruugo\Model\Profileproducts")
                ->getProfileProducts($profile->getId());


            $deleteProds = array_diff($oldProfileProducts, $profileProducts);
            $addProds = array_diff($profileProducts, $oldProfileProducts);


            foreach ($deleteProds as $oUid) {
                $this->_deleteProductFromProfile($oUid);
                $this->_cache->removeValue(\Ced\Fruugo\Helper\Cache::PROFILE_PRODUCT_CACHE_KEY.$oUid);
            }

            foreach ($addProds as $nRuid) {
                if($this->_addProductToProfile($nRuid, $profile->getId()))
                    $this->_cache->setValue(\Ced\Fruugo\Helper\Cache::PROFILE_PRODUCT_CACHE_KEY.$nRuid, $profile->getId());
            }

            if ($redirectBack && $redirectBack=='edit') {
                $this->messageManager->addSuccess(__('
		   		You Saved The Fruugo Profile And Its Products.
		   			'));
                $this->_redirect('*/*/edit', array(
                    'back' => 'edit',
                    'tab' => $tab,
                    'id' => $profile->getId(),
                    'active_tab' => null,
                    'pcode' => $pcode,
                    'section' => 'fruugoconfiguration',
                ));
            }else if ($redirectBack && $redirectBack=='upload') {
                $this->messageManager->addSuccess(__('
		   		You Saved The Fruugo Profile And Its Products. Upload Product Now.
		   			'));
                $this->_redirect('fruugo/products/index', array(
                    'profile_id' => $profile->getId(),
                    'pcode' => $profile->getProfileCode()
                ));
            } else {
                $this->messageManager->addSuccess(__('
		   		You Saved The Fruugo Profile And Its Products.
		   		'));
                $this->_redirect('*/*/');
            }
        }catch (\Exception $e){
            $this->messageManager->addError(__('
		   		Unable to Save Profile Please Try Again.
		   			'. $e->getMessage()));
            $this->_redirect('*/*/edit', array(
                'back' => 'edit',
                'tab' => $tab,
                'active_tab' => null,
                'pcode' => $pcode,
                'section' => 'fruugoconfiguration',
            ));
        }

        return;
    }

    protected function _addProductToProfile($productId, $profileId)
    {

        $profileproduct = $this->_objectManager->create("Ced\Fruugo\Model\Profileproducts");

        $product = $this->_objectManager->create("Magento\Catalog\Model\Product")->load($productId);

        //remove children from old profile
        //add children to current profile
        if($product->getTypeId() == 'configurable'){
            //removed from already assigned profile
            $this->_deleteProductFromProfile($productId);

            $childIds = $product->getTypeInstance()->getUsedProductIds($product);
            if(isset($childIds))
                foreach ($childIds as $id){
                    $this->_addProductToProfile($id, $profileId);
                }
        }

        //skip product if parent already exist in other profile
        if($product->getTypeId() == 'simple'){

            $checkForChild = $this->_objectManager->create('Magento\ConfigurableProduct\Model\Product\Type\Configurable')->getParentIdsByChild($product->getId());

            if(!empty($checkForChild) && count($checkForChild) > 0) {
                $profileToProducts = $profileproduct->loadByField('product_id',$checkForChild[0])->getData();
                if(!empty($profileToProducts) && $profileToProducts['profile_id'] != $profileId) {
                    /* array('errors' => 'The Parent Product of the SKU is already assigned to Pofile . Please unassign it to continue.');*/
                    $this->messageManager->addError('The Parent Product (ID - '.$checkForChild[0].' ) of the SKU - '.$product->getSku().' is already assigned to Pofile ID - '.$profileToProducts['profile_id'].'. Please unassign it to continue.');
                    return false;
                }
            }
        }



        if( $profileproduct->profileProductExists($productId, $profileId) === true ) {
            return false;
        } else {
            $profileproduct->deleteFromProfile($productId);
            $profileproduct->setProductId($productId);
            $profileproduct->setProfileId($profileId);
            $profileproduct->save();
            return true;
        }
    }
    protected function _deleteProductFromProfile($productId)
    {
        try {
            $this->_objectManager->create("Ced\Fruugo\Model\Profileproducts")
                ->deleteFromProfile($productId);
        } catch (\Exception $e) {
            throw $e;
            return false;
        }
        return true;
    }
    /* Identify unique fruugo attributes
   */
    function unique_multidim_array($array, $key) {
        $temp_array = array();
        $i = 0;
        $key_array = array();

        foreach($array as $val) {
            if($val['delete']==1)
                continue;

            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }
}
