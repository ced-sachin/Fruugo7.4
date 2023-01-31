<?php
namespace Ced\Fruugo\Controller\Adminhtml\Profile;

use Ced\Fruugo\Model\Data;

class MassEnable extends \Magento\Backend\App\Action
{


    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $profileIds = $this->getRequest()->getParam('selected');
        $excluded = $this->getRequest()->getParam('excluded', false);
        if (!is_array($profileIds) && !$excluded) {
            $this->messageManager->addError(__('Please select Profile(s).'));
        } else if($excluded == "false"){
            $profileIds  = $this->_objectManager->create('Ced\Fruugo\Model\Profile')->getCollection()->getAllIds();
        }


        if (!empty($profileIds)) {
            try {
                foreach ($profileIds as $profileId) {
                    $profile = $this->_objectManager->create('Ced\Fruugo\Model\Profile')->load($profileId);
                    $profile->setProfileStatus(1);
                    $profile->save();
                }
                $this->messageManager->addSuccessMessage(__('Total of %1 record(s) have been enabled.', count($profileIds)));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }
}