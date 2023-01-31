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

namespace Ced\Fruugo\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class ProductValidation
 */
class ProductValidation extends Column
{
    /**
     * @var UrlInterface
     */
    public $urlBuilder;


    /**
     * Json Parser
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $json;

    /**
     * Product Model
     * @var \Magento\Catalog\Model\ProductFactory
     */
    public $product;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        \Magento\Framework\Json\Helper\Data $json,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        $components = [],
        $data = []
    ) {
        $this->product = $productFactory->create();
        $this->urlBuilder = $urlBuilder;
        $this->json = $json;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');

            foreach ($dataSource['data']['items'] as &$item) {

                if (isset($item[$fieldName])) {
                    if ( $item[$fieldName] == 'Valid' || is_null($item[$fieldName])) {
                        $item[$fieldName . '_html'] = "<div class='grid-severity-notice'><span>valid</span></div>";
                        $item[$fieldName . '_title'] = __('Fruugo Product Details');
                        $item[$fieldName . '_productid'] = $item['entity_id'];
                    } else if($item[$fieldName] == 'Not-Validated'){
                        $item[$fieldName . '_html'] = '<div class="grid-severity-notice"><span>
                        not validated</span></div>';
                        $item[$fieldName . '_title'] = __('Fruugo Product Details');
                        $item[$fieldName . '_productid'] = $item['entity_id'];
                    }
                    else {
                        $item[$fieldName . '_html'] = '<button style="width:100%"class="grid-severity-critical">invalid</button>';
                        $item[$fieldName . '_title'] = __('Fruugo Product Details');
                        $item[$fieldName . '_productid'] = $item['entity_id'];
                        $errors = "";
                        try{
                            if(empty($item['fruugo_validation_errors'])) {
                                $fruugo_validation_errors = $this->product
                                    ->load($item['entity_id'])->getData('fruugo_validation_errors');
                                $item['fruugo_validation_errors'] = $fruugo_validation_errors;
                            }
                            if (!empty($item['fruugo_validation_errors'])) {
                                $errorsArray = $this->json->jsonDecode($item['fruugo_validation_errors']);
                                foreach ($errorsArray as &$error) {
                                    if(isset($error['id']))
                                        $error['url'] = $this->urlBuilder->getUrl
                                        ('catalog/product/edit', ['id' => $error['id']]);
                                }
                                $errors = $this->json->jsonEncode($errorsArray);
                            }
                            $item[$fieldName . '_productvalidation'] = $errors;
                        } catch(\Exception $e) {
                            /*echo $item['fruugo_validation_errors'];
                            echo $e->getMessage();die('check');*/
                        }
                        /*if($item['entity_id'] == '95982') {
                            echo '<pre>';
                            print_r($item);die;
                        }*/

                    }
                } else {
                    $this->product
                        ->load($item['entity_id'])
                        ->setData('fruugo_product_validation', 'Not-Validated')
                        ->getResource()
                        ->saveAttribute($this->product,'fruugo_product_validation');
                    $item[$fieldName . '_html'] = '<div class="grid-severity-notice"><span>
                        not validated</span></div>';
                    $item[$fieldName . '_title'] = __('Fruugo Product Details');
                    $item[$fieldName . '_productid'] = $item['entity_id'];
                }

            }

        }
        return $dataSource;
    }
}
