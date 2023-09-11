<?php
/*
 * Copyright (c) 2023.
 * Author: Michalis Michalis
 */

class InternetCode_AdvancedProductFilter_Model_Rule
{

    /**
     * @var InternetCode_AdvancedProductFilter_Model_Rule_Condition_Combine
     */
    private $_conditions;
    /**
     * @var Varien_Data_Form
     */
    private $_form;
    /**
     * @var array
     */
    private $_productIds;
    /**
     * @var array
     */
    private $_collectedAttributes;

    public function getForm()
    {
        if (!$this->_form) {
            $this->_form = new Varien_Data_Form();
        }

        return $this->_form;
    }

    /**
     * Create rule instance form post array
     *
     * @param array $post
     *
     */
    public function loadFromPost(array $post)
    {
        if (!isset($post['rule'][$this->getPrefix()])) {
            return $this;
        }
        $conditions = $this->_convertFlatToRecursive($post['rule'][$this->getPrefix()]);

        $this->_conditions = $this->getConditionInstance();

        if (empty($conditions)) {
            return $this;
        }
        $this->_conditions->loadArray($conditions[$this->getPrefix()][1], $this->getPrefix());
        return $this;
    }

    public function getPrefix()
    {
        return 'advanced_product_filter';
    }

    /**
     * Set specified data to current rule.
     * Set conditions and actions recursively.
     * Convert dates into Zend_Date.
     *
     * @param array $data
     *
     * @return array
     */
    protected function _convertFlatToRecursive(array $data)
    {
        $arr = array();
        foreach ($data as $id => $value) {
            $path = explode('--', $id);
            $node =& $arr;
            for ($i = 0, $l = sizeof($path); $i < $l; $i++) {
                if (!isset($node[$this->getPrefix()][$path[$i]])) {
                    $node[$this->getPrefix()][$path[$i]] = array();
                }

                $node =& $node[$this->getPrefix()][$path[$i]];
            }

            foreach ($value as $k => $v) {
                $node[$k] = $v;
            }
        }

        return $arr;
    }

    protected function getConditionInstance()
    {
        return Mage::getModel($this->getConditionClassName())
            ->setRule($this)
            ->setPrefix($this->getPrefix())
            ->setValue(true)
            ->setId(1)
            ->setData($this->getPrefix(), array());
    }

    private function getConditionClassName()
    {
        return 'advprodfilter/rule_condition_combine';
    }

    /**
     * Add filters to magento product collection
     *
     * @param Varien_Data_Collection_Db
     */
    public function setAttributesFilterToCollection(Varien_Data_Collection_Db $collection)
    {
        $conditions = $this->getConditions()->getData($this->getPrefix());
        if (empty($conditions)) {
            return;
        }

        $this->_productIds = array();
        $this->getConditions()->collectValidatedAttributes($collection);

        $idFieldName = $collection->getIdFieldName();
        if (empty($idFieldName)) {
            $idFieldName = Mage::getModel('catalog/product')->getIdFieldName();
        }

        Mage::getSingleton('core/resource_iterator')->walk(
            $collection->getSelect(),
            array(array($this, 'callbackValidateProduct')),
            array(
                'attributes' => $this->getCollectedAttributes(),
                'product' => Mage::getModel('catalog/product'),
                'store_id' => $collection->getStoreId(),
                'id_field_name' => $idFieldName
            )
        );

        $collection->addFieldToFilter($idFieldName, array('in' => $this->_productIds));
    }

    /**
     * Get condition instance
     *
     * @return InternetCode_AdvancedProductFilter_Model_Rule_Condition_Combine
     *
     */
    public function getConditions()
    {
        $prefix = $this->getPrefix();

        if ($this->_conditions !== null) {
            return $this->_conditions->setJsFormObject($prefix)->setStoreId($this->getStoreId());
        }

        $this->_conditions = $this->getConditionInstance();

        return $this->_conditions->setJsFormObject($prefix)->setStoreId($this->getStoreId());
    }

    public function getStoreId()
    {
        //todo
        return 0;
    }

    /**
     * @return array
     */
    public function getCollectedAttributes()
    {
        return $this->_collectedAttributes;
    }

    public function callbackValidateProduct($args)
    {
        $product = clone $args['product'];
        $args['row']['store_id'] = $args['store_id'];
        $product->setData($args['row']);

        if ($this->getConditions()->validate($product)) {
            $this->_productIds[] = $product->getData($args['id_field_name']);
        }
    }

    /**
     * Validate magento product with rule
     *
     * @param Varien_Object $object
     *
     * @return bool
     */
    public function validate(Varien_Object $object)
    {
        return $this->getConditions()->validate($object);
    }

    /**
     * @param array $attributes
     * @return $this
     */
    public function setCollectedAttributes(array $attributes)
    {
        $this->_collectedAttributes = $attributes;
        return $this;
    }
}
