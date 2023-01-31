<?php
namespace Ced\Fruugo\Plugin;


class ConfigPlugin
{

    /**
     * DataHelper
     * @var \Ced\Fruugo\Helper\Data
     */


    public function afterSave(
        \Magento\Config\Model\Config $subject

    ) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $scopeConfigManager = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');

        $configPost = $subject->getData();
        
        if(isset($configPost['section']) && $configPost['section'] == 'fruugoconfiguration') {
           
            $this->dataHelper = $objectManager->create('\Ced\Fruugo\Helper\Data');
            $configResourceModel = $objectManager->create('\Magento\Config\Model\ResourceModel\Config');
          

            $response = $this->dataHelper->validateCredentials('stockstatus-api');
           

            $messageManager = $objectManager->create('\Magento\Framework\Message\ManagerInterface');

            $enabled = $scopeConfigManager->getValue('fruugoconfiguration/fruugosetting/enable');

            if ($enabled && $response) {
                $messageManager->addSuccess('Fruugo Credential Valid');
                $configResourceModel->saveConfig('fruugoconfiguration/fruugosetting/validate_details','1','default',0);
            } else {
                $messageManager->addError('Fruugo Credential Invalid');
                $configResourceModel->saveConfig('fruugoconfiguration/fruugosetting/validate_details','0','default',0);
            }

        }

    }
}