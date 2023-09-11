<?php
/*
 * Copyright (c) 2023.
 * Author: Michalis Michalis
 */

class InternetCode_AdvancedProductFilter_Block_Adminhtml_Rule_Chooser_Category
    extends Mage_Adminhtml_Block_Catalog_Category_Checkboxes_Tree
{
    //########################################

    public function getLoadTreeUrl($expanded = null)
    {
        $params = array(
            '_current' => true,
            'id' => null,
            'store' => $this->getRequest()->getParam('store', 0)
        );

        if (($expanded === null && Mage::getSingleton('admin/session')->getIsTreeWasExpanded())
            || $expanded == true) {
            $params['expand_all'] = true;
        }

        return $this->getUrl('*/*/categoriesJson', $params);
    }

    //########################################
}
