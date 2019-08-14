<?php

class Rejoiner_Acr_AddbyskuController extends Mage_Core_Controller_Front_Action
{

    const XML_PATH_REJOINER_DEBUG_ENABLED   = 'checkout/rejoiner_acr/debug_enabled';

    function indexAction()
    {
        $params = $this->getRequest()->getParams();
        if(Mage::helper('checkout/cart')->getItemsCount()) {
            $quote = Mage::getSingleton('checkout/session')->getQuote();
        } else {
            $quote = Mage::helper('checkout/cart')->getCart();
        }
        $successMessage = '';
        foreach ($params as $key => $product) {
            if ($product && is_array($product)) {
                $productBySKU = Mage::getModel('catalog/product')->loadByAttribute('sku', $product['sku']);
                $productId = $productBySKU->getId();
                if ($productId) {
                    $qty = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId)->getQty();
                    try {
                        if(!$quote->hasProductId($productId) && is_numeric($product['qty']) && $qty > $product['qty']) {
                            $quote->addProduct($productBySKU, (int)$product['qty']);
                            $successMessage .= $this->__('%s was added to your shopping cart.'.'</br>', Mage::helper('core')->escapeHtml($productBySKU->getName()));
                        }
                        unset($params[$key]);
                    } catch (Exception $e) {
                        if(Mage::getStoreConfig(self::XML_PATH_REJOINER_DEBUG_ENABLED)) {
                            Mage::log($e->getMessage(), null, 'rejoiner.log');
                        }
                    }
                }
            }
        }
        if ($params['coupon_code']) {
            Mage::getSingleton('checkout/cart')->getQuote()->setCouponCode($params['coupon_code'])->collectTotals()->save();;
        }
        try {
            $quote->save();
        }  catch (Exception $e) {
            if(Mage::getStoreConfig(self::XML_PATH_REJOINER_DEBUG_ENABLED)) {
                Mage::log($e->getMessage(), null, 'rejoiner.log');
            }
        }
        Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
        if($successMessage) {
            Mage::getSingleton('core/session')->addSuccess($successMessage);
        }
        $this->getResponse()->setRedirect(Mage::getUrl('checkout/cart/'));
    }

}