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
 * @category  Ced
 * @package   Ced_Jet
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Fruugo\Ui\Component\Listing\Columns;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class RefundDetails extends Column
{
    /**
     * @var UrlInterface
     */
    public $urlBuilder;

    /**
     * Data Helper
     * @var  \Ced\Fruugo\Helper\Data
     */
    public $dataHelper;

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
        \Ced\Fruugo\Helper\Data $dataHelper,
        $components = [],
        $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->dataHelper = $dataHelper;

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
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')]['edit'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'fruugo/refund/details',
                        ['id' => $item['id']]
                    ),
                    'label' => __('View Details'),
                    'hidden' => false,
                    'data-id' => $item['id'],
/*                    'data-refund' => json_enocde($this->dataHelper->getOrder($item['refund_purchaseOrderId'])['elements']['order'][0]['orderLines']['orderLine'])*/
                ];
            }
        }
        return $dataSource;
    }
}


