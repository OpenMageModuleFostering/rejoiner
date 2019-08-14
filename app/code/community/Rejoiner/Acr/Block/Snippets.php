<?php

/**
 * Main block for module
 *
 * @category   Rejoiner
 * @package    Rejoiner_Acr
 */
class Rejoiner_Acr_Block_Snippets extends Mage_Core_Block_Template
{

    public function getCartItems()
    {
        $items = array();
        if ($quote = $this->_getQuote()) {
            $mediaUrl = Mage::getBaseUrl('media');
            $quoteItems = $quote->getAllItems();

            $parentToChild = array();
            /** @var Mage_Sales_Model_Quote_Item $item */
            foreach ($quoteItems as $item) {
                /** @var Mage_Sales_Model_Quote_Item $parent */
                if ($parent = $item->getParentItem()) {
                    if ($parent->getProductType() == Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE) {
                        $parentToChild[$parent->getId()] = $item;
                    }
                }
            }

            foreach ($quote->getAllItems() as $item) {
                if ($item->getParentItem()) {
                    continue;
                }

                $product = $item->getProduct();
                $thumbnail = 'no_selection';
                $imageHelper = Mage::helper('catalog/image');
                // get thumbnail from configurable product
                if ($product->getData('thumbnail') && ($product->getData('thumbnail') != 'no_selection')) {
                    $thumbnail = $product->getData('thumbnail');
                    // or try finding it in the simple one
                } elseif ($item->getProductType() == Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE) {
                    /** @var Mage_Sales_Model_Quote_Item $simpleItem */
                    $simpleItem = $parentToChild[$item->getId()];
                    $simpleProduct = $simpleItem->getProduct();
                    if ($simpleProduct->getData('thumbnail') && ($simpleProduct->getData('thumbnail') != 'no_selection')) {
                        $thumbnail = $simpleProduct->getData('thumbnail');
                    }
                } elseif ($productId = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getEntityId())) {
                    if (isset($productId[0])) {
                        $configurableProduct = Mage::getModel('catalog/product')
                            ->getCollection()
                            ->addAttributeToSelect('thumbnail')
                            ->addAttributeToFilter('entity_id', $productId)
                            ->getFirstItem();
                        $thumbnail = $configurableProduct->getData('thumbnail');
                    }
                }
                
                if (!file_exists(Mage::getBaseDir('media') . '/catalog/product' . $thumbnail)) {
                    $thumbnail = 'no_selection';
                }
                // use placeholder image if nor simple nor configurable products does not have images
                if ($thumbnail == 'no_selection') {
                    $imageHelper->init($product, 'thumbnail');
                    $image = Mage::getDesign()->getSkinUrl($imageHelper->getPlaceholder());
                } elseif($imagePath = Mage::helper('rejoiner_acr')->resizeImage($thumbnail)) {
                    $image = str_replace(Mage::getBaseDir('media') . '/', $mediaUrl, $imagePath);
                } else {
                    $image = $mediaUrl . 'catalog/product' . $thumbnail;
                }

                $newItem = array();
                $newItem['name']        = addslashes($item->getName());
                $newItem['image_url']   = $image;
                $newItem['price']       = (string) $this->_convertPriceToCents($item->getBaseCalculationPrice());
                $newItem['product_id']  = (string) $item->getSku();
                $newItem['item_qty']    = (string) $item->getQty();
                $newItem['qty_price']   = (string) $this->_convertPriceToCents($item->getBaseRowTotal());
                $items[] = $newItem;
            }
        }
        return $items;
    }

    public function getCartData()
    {
        if ($quote = $this->_getQuote()) {
            return '"totalItems":"'.$this->_getQuote()->getItemsQty().'","value":"'.$this->_convertPriceToCents($this->_getQuote()->getBaseSubtotal()).'","returnUrl":"'.Mage::helper('rejoiner_acr')->getRestoreUrl().'"';
        }
        return '';
    }


    protected function _getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    protected function _convertPriceToCents($price) {
        return round($price*100);
    }

}
