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

namespace Ced\Fruugo\Ui\Component\Listing\Columns\Profile;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class ProductValidation
 */
class ProductCount extends Column
{
    /**
     * @var UrlInterface
     */
    public $urlBuilder;

    public $profileProduct;

    public $productModel;

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
        \Ced\Fruugo\Model\Profileproducts $profileProduct,
        \Magento\Catalog\Model\Product $productModel,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->profileProduct = $profileProduct;
        $this->productModel = $productModel;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource) {

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {

                $profileProducts = $this->profileProduct->getProfileProducts($item['id']);
                if(count($profileProducts)>0) {
                    $products = $this->productModel->getCollection();
                    $products->addFieldToFilter('entity_id', array('in', $profileProducts));
                    $products->addFieldToFilter('type_id', array('simple', 'configurable'))
                        ->addAttributeToFilter('visibility',  array('neq' => 1));
                    $value = count($products);
                }else{
                    $value = 0;
                }
                $item['product_count'] = $value;

            }
        }

        return $dataSource;
    }

}
