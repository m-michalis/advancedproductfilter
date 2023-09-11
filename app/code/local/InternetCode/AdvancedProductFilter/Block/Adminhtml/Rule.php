<?php
/*
 * Copyright (c) 2023.
 * Author: Michalis Michalis
 */

class InternetCode_AdvancedProductFilter_Block_Adminhtml_Rule
    extends Mage_Adminhtml_Block_Widget_Form
{

    public function __construct()
    {
        parent::__construct();
    }

    protected function _prepareForm()
    {
        $model = Mage::getModel('advprodfilter/rule');
        $storeId = $model->getStoreId();
        $prefix = $model->getPrefix();

        $form = new Varien_Data_Form();
        $form->setHtmlId($prefix);
        $form->setUseContainer(true);
        $form->setData('id', 'advanced_product_filter_form');
        $form->setData('onsubmit', $this->getData('grid_js_object_name') . '.doFilter(event);');

        /** @var Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset $fieldSetRenderer */
        $fieldSetRenderer = $this->getLayout()->getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('promo/fieldset.phtml')
            ->setNewChildUrl(
                $this->getUrl(
                    '*/advprodfilter/newActionHtml',
                    [
                        'prefix' => $prefix,
                        'store' => $storeId,
                    ]
                )
            );
        $fieldset = $form->addFieldset($prefix, [
            'legend' => 'Advanced Product Filter'
        ])->setRenderer($fieldSetRenderer);


        $rulesField = $fieldset->addField(
            $prefix . '_field', 'text', [
                'name' => 'conditions' . $prefix,
                'label' => Mage::helper('advprodfilter')->__('Conditions'),
                'title' => Mage::helper('advprodfilter')->__('Conditions'),
                'required' => true,
            ]
        );
        $rulesField->setRule($model);

        /** @var Mage_Rule_Block_Conditions $rulesRenderer */
        $rulesRenderer = $this->getLayout()->getBlockSingleton('rule/conditions');
        $rulesField->setRenderer($rulesRenderer);

        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('head')
            ->setCanLoadExtJs(true)
            ->addJs('mage/adminhtml/rules.js');

        return parent::_prepareLayout();
    }
}
