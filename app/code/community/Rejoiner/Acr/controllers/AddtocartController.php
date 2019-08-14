<?php

class Rejoiner_Acr_AddtocartController extends Mage_Core_Controller_Front_Action
{

    function indexAction()
    {
        Mage::getSingleton('checkout/cart')->truncate();
        $params = $this->getRequest()->getParams();
        $cart   = Mage::helper('checkout/cart')->getCart();
        foreach ($params as $key => $product) {
            if ($product && is_array($product)) {
                $prodModel = Mage::getModel('catalog/product')->load((int)$product['product']);
                if (!$prodModel->getId()) {
                    continue;
                }
                try {
                    $cart->addProduct($prodModel, $product);
                    unset($params[$key]);
                } catch (Exception $e) {
                    Mage::log($e->getMessage(), null, 'rejoiner.log');
                }
            }
        }
        if ($params['coupon_code']) {
            $cart->getQuote()->setCouponCode($params['coupon_code'])->collectTotals()->save();;
        }
        $cart->save();
        Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
        $this->getResponse()->setRedirect(Mage::getUrl('checkout/cart/', array('_query' => $params)));
    }

}