<?php

/*
 * Copyright (c) 2023.
 * Author: Michalis Michalis
 */

class InternetCode_AdvancedProductFilter_Model_Rule_Condition_Product
    extends Mage_Rule_Model_Condition_Product_Abstract
{

    //########################################

    /**
     * Validate product attribute value for condition
     *
     * @param Varien_Object $product
     * @return bool
     */
    public function validate(Varien_Object $product)
    {
        /** @var  $product Mage_Catalog_Model_Product */
        $attrCode = $this->getAttribute();

        if ($this->isFilterCustom($attrCode)) {
            $value = $this->getCustomFilterInstance($attrCode)->getValueByProductInstance($product);
            return $this->validateAttribute($value);
        }
        return parent::validate($product);
    }


    protected function isFilterCustom($filterId)
    {
        $customFilters = $this->getCustomFilters();
        return isset($customFilters[$filterId]);
    }


    protected function getCustomFilters()
    {
        return array(
            'is_in_stock' => 'Stock',
            'qty' => 'Qty',
        );
    }

    /**
     * @param $filterId
     * @param $isReadyToCache
     * @return InternetCode_AdvancedProductFilter_Model_Rule_Custom_Abstract
     */
    protected function getCustomFilterInstance($filterId, $isReadyToCache = true)
    {
        $customFilters = $this->getCustomFilters();
        if (!isset($customFilters[$filterId])) {
            return null;
        }

        if (isset($this->_customFiltersCache[$filterId])) {
            return $this->_customFiltersCache[$filterId];
        }

        /** @var InternetCode_AdvancedProductFilter_Model_Rule_Custom_Abstract $model */
        $model = Mage::getModel('advprodfilter/rule_custom_' . $customFilters[$filterId]);
        $model->setFilterOperator($this->getData('operator'))
            ->setFilterCondition($this->getData('value'));

        $isReadyToCache && $this->_customFiltersCache[$filterId] = $model;
        return $model;
    }


    /**
     * Retrieve input type
     *
     * @return string
     */
    public function getInputType()
    {
        if ($this->isFilterCustom($this->getAttribute())) {
            return $this->getCustomFilterInstance($this->getAttribute())->getInputType();
        }

        return parent::getInputType();
    }

    /**
     * Retrieve value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        if ($this->isFilterCustom($this->getAttribute())) {
            return $this->getCustomFilterInstance($this->getAttribute())->getValueElementType();
        }

        return parent::getValueElementType();
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $productAttributes = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addVisibleFilter()
            ->setOrder('frontend_label', Varien_Data_Collection::SORT_ORDER_ASC);

        $attributes = array();
        foreach ($productAttributes as $attribute) {
            /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
            $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
        }

        $this->_addSpecialAttributes($attributes);

        foreach ($this->getCustomFilters() as $filterId => $instanceName) {
            // $this->_data property is not initialized jet, so we can't cache a created custom filter as
            // it requires that data
            $customFilterInstance = $this->getCustomFilterInstance($filterId, false);

            if ($customFilterInstance instanceof InternetCode_AdvancedProductFilter_Model_Rule_Custom_Abstract) {
                $attributes[$filterId] = $customFilterInstance->getLabel();
            }
        }

        natcasesort($attributes);
        $this->setAttributeOption($attributes);

        return $this;
    }


    /**
     * Prepares values options to be used as select options or hashed array
     * Result is stored in following keys:
     *  'value_select_options' - normal select array: array(array('value' => $value, 'label' => $label), ...)
     *  'value_option' - hashed array: array($value => $label, ...),
     *
     * @return $this
     */
    protected function _prepareValueOptions()
    {
        if ($this->isFilterCustom($this->getAttribute())) {
            $selectOptions = $this->getCustomFilterInstance($this->getAttribute())->getOptions();

            // Set new values only if we really got them
            if ($selectOptions !== null) {
                // Overwrite only not already existing values
                if (!$this->getData('value_select_options')) {
                    $this->setData('value_select_options', $selectOptions);
                }

                if (!$this->getData('value_option')) {
                    $hashedOptions = array();
                    foreach ($selectOptions as $o) {
                        if (is_array($o['value'])) {
                            continue; // We cannot use array as index
                        }

                        $hashedOptions[$o['value']] = $o['label'];
                    }

                    $this->setData('value_option', $hashedOptions);
                }
            }
            return $this;
        }else {
            return parent::_prepareValueOptions();
        }
    }


    /**
     * Collect validated attributes
     *
     * @param Mage_Catalog_Model_Resource_Product_Collection $productCollection
     * @return $this
     */
    public function collectValidatedAttributes($productCollection)
    {
        if($this->isFilterCustom($this->getAttribute())){
            return $this;
        }
        return parent::collectValidatedAttributes($productCollection);
    }

    /**
     * Retrieve value element
     *
     * @return Varien_Data_Form_Element_Abstract
     */
    public function getValueElement()
    {
        $element = parent::getValueElement();

        if ($this->isFilterCustom($this->getAttribute())
            && $this->getCustomFilterInstance($this->getAttribute())->getInputType() == 'date'
        ) {
            $element->setImage(Mage::getDesign()->getSkinUrl('images/grid-cal.gif'));
        }
        return $element;
    }

    /**
     * Retrieve Explicit Apply
     *
     * @return bool
     */
    public function getExplicitApply()
    {
        if ($this->isFilterCustom($this->getAttribute())
            && $this->getCustomFilterInstance($this->getAttribute())->getInputType() == 'date'
        ) {
            return true;
        }
        return parent::getExplicitApply();
    }

}
