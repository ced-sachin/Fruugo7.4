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

namespace Ced\Fruugo\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ObserverInterface;

class CategorySave implements ObserverInterface
{
    /**
     * Object Manager
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * Message Manager
     * @var \Magento\Framework\Message\ManagerInterface
     */
    public $messageManager;

    /**
     * Logger
     * @var $fileIo
     */
    public $logger;

    /**
     * Json
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $json;

    /**
     * CategorySave constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param RequestInterface $request
     * @param ScopeConfig $scopeConfig
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Json\Helper\Data $json
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        RequestInterface $request, ScopeConfig $scopeConfig,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Json\Helper\Data $json,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->json = $json;
        $this->logger = $logger;
    }

    /**
     * Category Mapping event handler
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $request = $observer->getEvent()->getRequest();
        $data = $request->getPost();
        //$this->logger->addDebug( $this->json->jsonEncode($data));
        $currentCatId = $data['entity_id']; //print_r($data);die;
        if ($currentCatId == '' || $currentCatId == '0' || $currentCatId == '1' || $currentCatId == '2') {
            $this->messageManager->addErrorMessage(__('Select Magento Sub Category , Fruugo does not allows mapping with root category.'));
        } else {
            $category = $this->objectManager->create('\Ced\Fruugo\Model\Categories');

            // Removes if magento category is already mapped other fruugo category
            $this->checkMagentoCatIdExist($currentCatId);

            //Remove Product Cache
            $cache = $this->objectManager->create('\Magento\Framework\App\Cache');
            $cache->remove('fruugo_validation_array');
            if (($data['fruugo_cat_level_0'] == '0')) {
                // Case 1: category mapping reset on select value == 0
                $this->messageManager->addSuccessMessage(__('Fruugo Mapping Reset Successfully'));
                return true;
            }

            elseif (isset($data['fruugo_cat_level_1'])) {
                //Case 2: Map a new category
                $category = $category->load($data['fruugo_cat_level_1'], 'path');
                if ($category->getMagentoCatId() == '') {
                    $category->setData( 'magento_cat_id', ','.$currentCatId.',' )->save();
                    $this->messageManager->addSuccessMessage(__('Fruugo Category Mapping details saved'));
                } else {
                    $magentoCatId = $category->getMagentoCatId();
                    if(!(preg_match('/(\,'.$currentCatId.'\,)/', $magentoCatId))) {
                        $category->setData( 'magento_cat_id', $magentoCatId.$currentCatId.',' )->save();
                        $this->messageManager->addSuccessMessage(__('Fruugo Category Mapping details saved'));
                    }

                }

            }
        }
    }

    /**
     * @param $currentCatId
     * @return bool
     */
    public function checkMagentoCatIdExist($currentCatId)
    {
        $mageCatExists = $this->objectManager->create('Ced\Fruugo\Model\Categories')->getCollection()->addFieldToFilter(['magento_cat_id'], [[ 'like' => "%,".$currentCatId.",%" ]])->getData();
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
                $model = $this->objectManager->create('Ced\Fruugo\Model\Categories');
                $model->load($id);
                $model->setdata('magento_cat_id', $mageCatId)->save();
            }
        }

    }

}