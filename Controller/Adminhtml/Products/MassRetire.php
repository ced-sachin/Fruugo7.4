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

namespace Ced\Fruugo\Controller\Adminhtml\Products;

class MassRetire extends \Magento\Backend\App\Action
{
    /**
     * PF
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_Fruugo::fruugo_products_index';

    /**
     * Filter
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    public $filter;

    /**
     * PSR
     * @var \Psr\Log\LoggerInterface
     */
    public $logger;

    public $dataHelper;

    /**
     * MassRetire constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Ced\Fruugo\Helper\Data $dataHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->filter = $filter;
        $this->logger =  $logger;
        $this->dataHelper = $dataHelper;
    }

    /**
     * Product Retire
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        if(!$this->dataHelper->checkForConfiguration()) {
            $this->messageManager->addNoticeMessage(__('Products Retire Failed . Fruugo API not enabled or Invalid. Please check Fruugo Configuration.'));
            return $this->_redirect('fruugo/products/index');
        }
        $collection = $this->filter->getCollection($this->_objectManager->create('Magento\Catalog\Model\Product')
            ->getCollection());
        $productids = $collection->getAllIds();

        if (is_string($productids)) {
            $productids = explode(",", $productids);
        }

        if (count($productids) == 0) {
            $this->messageManager->addErrorMessage('No Product selected to retire.');
            $this->_redirect('fruugo/products/index');
        }

        /*if ($dataHelper->createProductOnFruugo($productids)) {
            $this->messageManager->addSuccessMessage(count($productids) . ' Products Retired Successfully');
            $this->_redirect('fruugo/products/index');
        } else {
            $this->messageManager->addErrorMessage('Products Retired Failed.');
            $this->_redirect('fruugo/products/index');
        }*/
        $counter = 0;
        foreach ($productids as  $value) {
            $sku = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($value)->getSku();
            $checkSku = $this->_objectManager->get('Ced\Fruugo\Helper\Data')->getItem($sku);
            if ($checkSku) {
                $requestSent =  $this->_objectManager->get(
                    'Ced\Fruugo\Helper\Data')->deleteRequest('v2/items/'.$sku);
                $response = json_decode($requestSent, true);
                $response = empty($response) ? false : $response;
                if (isset($response['message'])) {
                    $counter++;

                } elseif (isset($response['error'][0]['description'])) {
                    $this->logger->debug('Delete Error for Sku: '.$sku.' with error message'.
                        $response['error'][0]['description']);
                    $this->_redirect('fruugo/products/index');
                } else {
                    $this->logger->debug('ProductDelete MassActon: ' . $requestSent);

                }
            }
        }
        if ($counter > 0) {
            $this->messageManager
                ->addSuccessMessage('Retire Request for '.$counter.' Products has been sent to Fruugo');
        } else {
            $this->messageManager->addErrorMessage('Product retire failed ');
        }
        return $this->_redirect('fruugo/products/index');

    }

    /**
     * IsALLowed
     * @return boolean
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ced_Fruugo::Fruugo');
    }
}
