<?php

/*
 * Copyright (c) 2023.
 * Author: Michalis Michalis
 */

abstract class InternetCode_AdvancedProductFilter_Model_Rule_Custom_Abstract
{
    protected $_filterOperator = null;
    protected $_filterCondition = null;

    //########################################

    abstract public function getAttributeCode();

    abstract public function getLabel();

    abstract public function getValueByProductInstance(Mage_Catalog_Model_Product $product);

    //########################################

    /**
     * @return string
     */
    public function getInputType()
    {
        return 'string';
    }

    /**
     * @return string
     */
    public function getValueElementType()
    {
        return 'text';
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return array();
    }

    //########################################

    public function getFilterOperator()
    {
        return $this->_filterOperator;
    }

    public function setFilterOperator($filterOperator)
    {
        $this->_filterOperator = $filterOperator;
        return $this;
    }

    //----------------------------------------

    public function getFilterCondition()
    {
        return $this->_filterCondition;
    }

    public function setFilterCondition($filterCondition)
    {
        $this->_filterCondition = $filterCondition;
        return $this;
    }

    //########################################
}
