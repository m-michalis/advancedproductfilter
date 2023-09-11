<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class InternetCode_AdvancedProductFilter_Model_Rule_Condition_Combine
    extends Mage_Rule_Model_Condition_Combine
{

    public function setConditions($conditions)
    {
        $key = $this->getPrefix() ? $this->getPrefix() : 'conditions';
        return $this->setData($key, $conditions);
    }

    public function setStoreId($storeId)
    {
        $this->setData('store_id', $storeId);
        foreach ($this->getConditions() as $condition) {
            $condition->setStoreId($storeId);
        }

        return $this;
    }

    // ---------------------------------------

    public function getConditions()
    {
        $key = $this->getPrefix() ? $this->getPrefix() : 'conditions';
        return $this->getData($key);
    }


    /**
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $conditions = [
            [
                'label' => Mage::helper('advprodfilter')->__('Conditions Combination'),
                'value' => $this->getConditionCombine()
            ],
            [
                'label' => Mage::helper('advprodfilter')->__('Product Attribute'),
                'value' => $this->getProductOptions()
            ]
        ];

        return array_merge_recursive(parent::getNewChildSelectOptions(), $conditions);
    }

    protected function getConditionCombine()
    {
        return $this->getType();
    }


    protected function getProductOptions()
    {
        $attributes = Mage::getModel('advprodfilter/rule_condition_product')->getAttributeOption();
        return !empty($attributes) ?
            $this->getOptions('advprodfilter/rule_condition_product', $attributes)
            : [];
    }

    protected function getOptions($value, array $optionsAttribute, array $params = [])
    {
        $options = [];
        $suffix = (count($params)) ? '|' . implode('|', $params) . '|' : '|';
        foreach ($optionsAttribute as $code => $label) {
            $options[] = array(
                'value' => $value . $suffix . $code,
                'label' => $label
            );
        }

        return $options;
    }

    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($productCollection);
        }
        return $this;
    }

    public function getConditionModels()
    {
        return self::$_conditionModels;
    }
}
