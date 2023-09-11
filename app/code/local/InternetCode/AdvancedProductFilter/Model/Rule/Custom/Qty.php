<?php
/*
 * Copyright (c) 2023.
 * Author: Michalis Michalis
 */

class InternetCode_AdvancedProductFilter_Model_Rule_Custom_Qty
    extends InternetCode_AdvancedProductFilter_Model_Rule_Custom_Abstract
{
    //########################################

    /**
     * @return string
     */
    public function getAttributeCode()
    {
        return 'qty';
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return Mage::helper('catalog')->__('Qty');
    }

    public function getValueByProductInstance(Mage_Catalog_Model_Product $product)
    {
        return Mage::getModel('cataloginventory/stock_item')
            ->setProductId($product->getId())
            ->setStockId(Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID)
            ->loadByProduct($product)
            ->getQty();
    }

    //########################################
}
