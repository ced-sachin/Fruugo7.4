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

namespace Ced\Fruugo\Model\Payment;

class Payfruugocom extends \Magento\Payment\Model\Method\AbstractMethod
{
  
  public $_code = 'payfruugocom';
  public $_canAuthorize = true;
  public $_canCancelInvoice = false;
  public $_canCapture = false;
  public $_canCapturePartial = false;
  public $_canCreateBillingAgreement = false;//
  public $_canFetchTransactionInfo = false;
  public $_canManageRecurringProfiles = false;//
  public $_canOrder = false;
  public $_canRefund = false;
  public $_canRefundInvoicePartial = false;
  public $_canReviewPayment = false;
  /* Setting for disable from front-end. */
  /* START */
  public $_canUseCheckout = false;
  public $_canUseForMultishipping = false;//
  public $_canUseInternal = false;
  public $_canVoid = false;
  public $_isGateway = false;
  public $_isInitializeNeeded = false;
  
  /* END */
  
/**
* @return boolean
*/

  public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null) {
    return true;
  }
  /**
* @return string
*/
  public function getCode(){
    return $this->_code;
  }
}