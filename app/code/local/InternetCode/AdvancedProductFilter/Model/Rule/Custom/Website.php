<?php

/*
 * Copyright (c) 2023.
 * Author: Michalis Michalis
 */

class InternetCode_AdvancedProductFilter_Model_Rule_Custom_Website
    extends InternetCode_AdvancedProductFilter_Model_Rule_Custom_Abstract
{
    //########################################

    /**
     * @return string
     */
    public function getAttributeCode()
    {
        return 'website';
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return Mage::helper('catalog')->__('Website');
    }

    public function getValueByProductInstance(Mage_Catalog_Model_Product $product)
    {
        return $product->getWebsiteIds();
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
        return Mage::getModel('core/website')->getCollection()->toOptionArray();
    }
}
