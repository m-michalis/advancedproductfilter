<?php
/*
 * Copyright (c) 2023.
 * Author: Michalis Michalis
 */

class InternetCode_AdvancedProductFilter_Model_Observer
{
    //controller_action_layout_generate_blocks_after
    //controller_action_layout_render_before

    public function attachButton($event)
    {
        $layout = Mage::getSingleton('core/layout');

        $allowedGridBlockTypes = [
//            Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Crosssell::class,
//            Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Upsell::class,
//            Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Related::class,
//            Mage_Adminhtml_Block_Catalog_Category_Tab_Product::class,
            Mage_Adminhtml_Block_Catalog_Product_Grid::class
        ];
        foreach ($layout->getAllBlocks() as $gridBlock) {


            if(!($gridBlock instanceof Mage_Adminhtml_Block_Widget_Grid)){
                continue;
            }
            $allowed = false;
            foreach ($allowedGridBlockTypes as $blockType) {
                $className = Mage::getConfig()->getBlockClassName($blockType);
                $allowed = $allowed || $gridBlock instanceof $className;
            }

            if (!$allowed) {
                continue;
            }

            /**
             * We are going to replace search_button with a core/text_list in its place.
             * The core/text_list will contain the detached search_button and the new button.
             */
            $searchButton = $gridBlock->getChild('search_button');

            $searchButtonAliasNew = $searchButton->getBlockAlias() . '.grouped_item';
            $searchButton->setBlockAlias($searchButtonAliasNew);

            /**
             * Append blocks in a certain order (search button should be last)
             */
            $buttonListBlock = $layout->createBlock('core/text_list')
                ->append($this->getAdvancedFilterButtonBlock($layout), 'advanced_filter_button')
                ->insert($searchButton, 'advanced_filter_button', true, $searchButtonAliasNew);


            $gridBlock->setChild(
                'search_button',
                $buttonListBlock
            );

            /**
             * If grid called via ajax, then stop here. no need to re-render rules block
             * TODO: maybe here we can check for ajax tab load, ex. related products
             */
            if (Mage::app()->getRequest()->isAjax()) {
                return;
            }

            /*
             * Will apply the same logic for grid so we can append the rules block
             */

            /** @var Mage_Adminhtml_Block_Catalog_Product $gridParentBlock */
            $gridParentBlock = $gridBlock->getParentBlock();
            $gridBlockAlias = $gridBlock->getBlockAlias();
            $gridBlockAliasNew = $gridBlock->getBlockAlias() . '.grouped_item';
            $gridBlock->setBlockAlias($gridBlockAliasNew);

            $gridParentBlock->unsetChild($gridBlockAlias);

            $ruleBlock = $layout->createBlock('advprodfilter/adminhtml_rule')
                ->setData('grid_js_object_name', $gridBlock->getJsObjectName());

            /**
             * Append it so that first comes the rule block and then the grid
             */
            $newGridBlock = $layout->createBlock('core/text_list',$gridBlockAlias)
                ->append($ruleBlock, 'rule_wrapper')
                ->insert($gridBlock, 'rule_wrapper', true, $gridBlockAliasNew)
                ->insert($this->getGridJsOverrideBlock($layout, $gridBlock->getJsObjectName()), $gridBlockAliasNew, true, 'grid_js_filter_override');

            $gridParentBlock->setChild($gridBlockAlias, $newGridBlock);

        }
    }

    public function getAllParentClasses($object) {
        $class = get_class($object);  // Get the class name of the object
        $parents = [];

        while ($parent = get_parent_class($class)) {
            $parents[] = $parent;
            $class = $parent;
        }

        return $parents;
    }

    private function getAdvancedFilterButtonBlock($layout)
    {
        return $layout->createBlock('adminhtml/widget_button')
            ->setData([
                'label' => Mage::helper('adminhtml')->__('Show Advanced Filter'),
                'onclick' => 'advancedFilterToggle()',
                'class' => 'task',
                'id' => 'advanced_filter_button'
            ]);

    }

    private function getGridJsOverrideBlock($layout, $jsObjectName)
    {
        return $layout->createBlock('adminhtml/template')
            ->setTemplate('InternetCode/AdvancedProductFilter/js_after_grid.phtml')
            ->setData('js_object_name', $jsObjectName);
    }

    public function catalogProductCollectionLoadBefore($event)
    {
        /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = $event->getCollection();

        $req = Mage::app()->getRequest();


        $actionName = $req->getActionName();
        $controllerName = $req->getControllerName();

        if ($controllerName == 'catalog_product' && $actionName == 'grid') {

            /** @var $ruleModel InternetCode_AdvancedProductFilter_Model_Rule */
            $ruleModel = Mage::getModel('advprodfilter/rule');
            $ruleModel->loadFromPost($req->getPost());
            $ruleModel->setAttributesFilterToCollection($collection);
        }
        return;
    }
}
