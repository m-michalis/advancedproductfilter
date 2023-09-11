<?php

/*
 * Copyright (c) 2023.
 * Author: Michalis Michalis
 */

class InternetCode_AdvancedProductFilter_Model_Rule_Custom_Stock
    extends InternetCode_AdvancedProductFilter_Model_Rule_Custom_Abstract
{
    //########################################

    /**
     * @return string
     */
    public function getAttributeCode()
    {
        return 'is_in_stock';
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return Mage::helper('catalog')->__('Stock Availability');
    }

    public function getValueByProductInstance(Mage_Catalog_Model_Product $product)
    {
        return Mage::getModel('cataloginventory/stock_item')
            ->setProductId($product->getId())
            ->setStockId(Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID)
            ->loadByProduct($product)
            ->getIsInStock();
    }

    //########################################

    /**
     * @return string
     */
    public function getInputType()
    {
        return 'select';
    }

    /**
     * @return string
     */
    public function getValueElementType()
    {
        return 'select';
    }

    /**
     * @return array
     */
    public function getOptions()
    {

        return Mage::getModel('cataloginventory/source_stock')->toOptionArray();
    }
}
