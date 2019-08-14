<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pavel
 * Date: 5/16/13
 * Time: 12:32 PM
 * To change this template use File | Settings | File Templates.
 */
class Rejoiner_Acr_AddtocartController extends Mage_Core_Controller_Front_Action
{

    function indexAction()
    {
        Mage::getSingleton('checkout/cart')->truncate();
        $params = $this->getRequest()->getParams();
        $cart   = Mage::helper('checkout/cart')->getCart();
        foreach ($params as $product) {
            if ($product && is_array($product)) {
                $prodModel = Mage::getModel('catalog/product')->load((int)$product['product']);
                try {
                    $cart->addProduct($prodModel, $product);
                } catch (Exception $e) {
                    Mage::log($e->getMessage(), null, 'rejoiner.log');
                }
            }
        }
        $cart->save();
        Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
        $this->getResponse()->setRedirect(Mage::getUrl('checkout/cart/'));
    }

}