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


class ProductFeedErrors extends Column
{
    /**
     * @var UrlInterface
     */
    public $urlBuilder;

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
        $components = [],
        $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
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
        $fieldName = $this->getData('name');
        foreach ($dataSource['data']['items'] as $key => $item) {

            if (isset($item[$fieldName]) && !empty($item[$fieldName])) {

                $dataSource['data']['items'][$key][$fieldName . '_html'] =
                    '<button style="width:100%"class="grid-severity-critical">Errors</button>';
                $dataSource['data']['items'][$key][$fieldName . '_title'] = __('Feeds Errors');
                //$dataSource['data']['items'][$key][$fieldName . '_feedid'] = $item['id'];
                $dataSource['data']['items'][$key][$fieldName . '_feederrors'] = $item[$fieldName];
            } else {
                $dataSource['data']['items'][$key][$fieldName . '_html'] =
                    "<div class='grid-severity-notice'>Success</div>";
                $dataSource['data']['items'][$key][$fieldName . '_title'] = __('Feeds Errors');
                //$dataSource['data']['items'][$key][$fieldName . '_feedid'] = $item['id'];
            }
        }
        return $dataSource;
    }
}
