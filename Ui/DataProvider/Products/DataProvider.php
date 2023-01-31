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

namespace Ced\Fruugo\Ui\DataProvider\Products;

use Magento\Backend\App\Action\Context;
/**
 * Class DataProvider for Fruugo Products
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Product Collection
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public $collection;

    /**
     * Add Field Strategies
     * @var \Magento\Ui\DataProvider\AddFieldToCollectionInterface[]
     */
    public $addFieldStrategies;

    /**
     * Add Filter Strategies
     * @var \Magento\Ui\DataProvider\AddFilterToCollectionInterface[]
     */
    public $addFilterStrategies;

    /**
     * Filter Builder
     * @var \Magento\Framework\Api\FilterBuilder
     */
    public $filterBuilder;

    /**
     * Object Manager
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * Request Params
     */
    public $params;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param array $addFieldStrategies
     * @param array $addFilterStrategies
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        Context $context,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        $addFieldStrategies = [],
        $addFilterStrategies = [],
        $meta = [],
        $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->objectManager = $context->getObjectManager();//$objectManager;
        $this->filterBuilder = $filterBuilder;
        $this->addFieldStrategies = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
        $this->collection = $collectionFactory->create();

       /* $result = $this->objectManager->create('Ced\Fruugo\Model\Categories')
            ->getCollection()
            ->addFieldToSelect('magento_cat_id')
            ->addFieldToFilter('magento_cat_id', ['neq' => null]);
        $fruugoCat = [];
        $temp = [];
        $count = -1;
        foreach ($result as $val) {
            $temp = explode(',', $val['magento_cat_id']);
            foreach ($temp as $key => $value) {
                $fruugoCat[++$count] = $value;
            }
        }
        $fruugoCat = array_values(array_filter($fruugoCat, function($value) { return $value !== ''; }));*/

        $_collection = $collectionFactory->create();

        $profileId = $this->objectManager->get('Magento\Framework\App\RequestInterface')->getParam('profile_id');


        $cond = null;
        if($profileId) {
                $bookmar_coll = $this->objectManager->create('Magento\Ui\Model\Bookmark')->getCollection()
                    ->addFieldToFilter('namespace',['eq' => 'fruugo_products_index']);
                foreach($bookmar_coll as $bookmark){
                    $bookmark_model = $this->objectManager->create('Magento\Ui\Model\Bookmark')
                        ->load($bookmark->getBookmarkId());
                    if($bookmark_model->getIdentifier() == 'current'){
                        $config_value = $bookmark_model->getConfig();
                        $config_value['current']['filters']['applied']['profile_id'] = $profileId;
                        $bookmark_model->setConfig(json_encode($config_value));
                        $bookmark_model->save();
                    }
            }
            $cond = "profile_id =".$profileId;
        }


        $_collection->joinField(
            'profile_id',
            'fruugo_profile_products',
            'profile_id',
            'product_id = entity_id',
            $cond
        );




        $this->collection  = $_collection;

       // $data = $objectManager->create('Magento\Framework\Api\Filter');
       // $data->setField('profile_id')->setValue(13);

        //$this->addFilter($data);
        /*$_collection->joinField(
            'category_id',
            'catalog_category_product',
            'category_id',
            'product_id = entity_id',
            null
        );

        $_collection->addAttributeToFilter('category_id', ['in' => $fruugoCat]);
        $this->addField('fruugo_product_validation');*/


        $ids = array_unique($_collection->getAllIds());


        $this->addField('fruugo_product_status');
        $this->addField('fruugo_product_validation');

        $this->addFilter($this->filterBuilder->setField('entity_id')->setConditionType('in')
            ->setValue($ids)
            ->create());
        $this->addFilter($this->filterBuilder->setField('type_id')->setConditionType('in')
            ->setValue(['simple', 'configurable'])
            ->create());
        $this->addFilter($this->filterBuilder->setField('visibility')->setConditionType('nin')
            ->setValue([1])
            ->create());
        $this->params = $context->getRequest()->getParams();
       /* echo '<pre>';
        print_r($params);die;*/

    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }
        $collection = $this->getCollection();
        $items = $collection->toArray();


       /* echo '<pre>';
        print_r($items);die;*/
//         echo '<pre>';

        return [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => array_values($items),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if (isset($this->addFilterStrategies[$filter->getField()])) {
            $this->addFilterStrategies[$filter->getField()]
                ->addFilter(
                    $this->getCollection(),
                    $filter->getField(),
                    [$filter->getConditionType() => $filter->getValue()]
                );
        } else {
            parent::addFilter($filter);
        }
    }

    /**
     * Add field to select
     *
     * @param string|array $field
     * @param string|null $alias
     * @return void
     */
    public function addField($field, $alias = null)
    {
        if (isset($this->addFieldStrategies[$field])) {
            $this->addFieldStrategies[$field]->addField($this->getCollection(), $field, $alias);
        } else {
            parent::addField($field, $alias);
        }
    }
}

