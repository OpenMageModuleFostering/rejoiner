<?php

/**
 * Block to check overriding for checkout by other extensions and show warning message
 *
 * @category   Rejoiner
 * @package    Rejoiner_Acr
 */
class Rejoiner_Acr_Block_Adminhtml_Notification extends Mage_Core_Block_Template
{

    public function canShow()
    {
        if (Rejoiner_Acr_Model_Notification::isNotificationViewed()) {
            return false;
        }

        if ($this->_isCoreCheckoutControllerOverridden()
            || $this->_isCoreCheckoutUrlHelperOverridden()
        ) {
            return true;
        }

        return false;
    }

    protected function _isCoreCheckoutControllerOverridden()
    {
        $frontController = new Mage_Core_Controller_Varien_Front();
        $frontController->init();

        $standard = $frontController->getRouter('standard');

        $modules = $standard->getModuleByFrontName('checkout');

        return reset($modules) != 'Mage_Checkout';
    }

    protected function _isCoreCheckoutUrlHelperOverridden()
    {
        return get_class(Mage::helper('checkout/url')) != 'Mage_Checkout_Helper_Url';
    }

}
