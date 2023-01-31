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

namespace Ced\Fruugo\Block\Adminhtml\System\Config;


class Validate extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Customer Id
     *
     * @var string
     */
    public $_customerId = 'fruugoconfiguration_fruugosetting_customer_id';

    /**
     * Private Key
     *
     * @var string
     */
    public $_privatekey = 'fruugoconfiguration_fruugosetting_private_key';

    /**
     * Validate Details Button Label
     *
     * @var string
     */

    public $_buttonLabel = 'Validate Details';

    /**
     * Set Customer Id Field Name
     *
     * @param string $apikey
     * @return \Ced\Fruugo\Block\Adminhtml\System\Config\Validate
     */

    public function setCustomerId($customerId)
    {
        $this->_customerId = $customerId;
        return $this;
    }

    /**
     * Get Customer Id Field Name
     *
     * @return string
     */
    public function getCustomerId()
    {
        return $this->_customerId;
    }

    /**
     * Set Private Key Field
     *
     * @param string $secretkey
     * @return \Ced\Fruugo\Block\Adminhtml\System\Config\Validate
     */
    public function setPrivateKey($privatekey)
    {
        $this->_privatekey = $privatekey;
        return $this;
    }

    /**
     * Get Private Key Field
     *
     * @return string
     */
    public function getPrivateKey()
    {
        return $this->_privatekey;
    }


    /**
     * Set Validate Details Button Label
     *
     * @param string $buttonLabel
     * @return \Ced\Fruugo\Block\Adminhtml\System\Config\Validate
     */
    public function setButtonLabel($buttonLabel)
    {
        $this->_buttonLabel = $buttonLabel;
        return $this;
    }

    /**
     * Set template to itself
     *
     * @return \Ced\Fruugo\Block\Adminhtml\System\Config\Validate
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('system/config/validate.phtml');
        }
        return $this;
    }

    /**

     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $buttonLabel = !empty($originalData['button_label']) ? $originalData['button_label'] : $this->_vatButtonLabel;
        $this->addData(
            [
                'button_label' => __($buttonLabel),
                'html_id' => $element->getHtmlId(),
                'ajax_url' => $this->_urlBuilder->getUrl('fruugo/system_config_validate/validate'),
            ]
        );

        return $this->_toHtml();
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function getPostUrl()
    {
        return $this->getUrl('fruugo/system_config_validate/validate');
    }
}
