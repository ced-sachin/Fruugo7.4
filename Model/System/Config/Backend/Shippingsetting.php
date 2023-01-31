<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ced\Fruugo\Model\System\Config\Backend;

/**
 * Backend for json array data
 */
class Shippingsetting extends \Magento\Framework\App\Config\Value
{

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Process data after load
     *
     * @return void
     */
    protected function _afterLoad()
    {
        $value = $this->getValue();
        $arr   = @json_decode($value,true);
        if(!is_array($arr)) return '';

        // some cleanup
        foreach ($arr as $k => $val) {
            if(!is_array($val)) {
                unset($arr[$k]);
                continue;
            }
        }

        $this->setValue($arr);
    }

    /**
     * Prepare data before save
     *
     * @return void
     */
    public function beforeSave()
    {
        $values = $this->getValue();

        $values = $this->unique_multidim_array($values, 'region', 'method');

        $value = json_encode($values,true);
        $this->setValue($value);
    }

    function unique_multidim_array($array, $key1, $key2) {
        $temp_array = [];
        $i = 0;
        $key_array = [];
        foreach($array as $key => $val) {
            if(!isset($val[$key1]))
                continue;

            if($val['method']=='VALUE')
                unset($val['magento_attribute_code']);


            if(!in_array($val[$key1].$val[$key2], $key_array)){
                $key_array[$i] = $val[$key1].$val[$key2];
                $temp_array[$key] = $val;
            }
            $i++;
        }
        return $temp_array;
    }
}
