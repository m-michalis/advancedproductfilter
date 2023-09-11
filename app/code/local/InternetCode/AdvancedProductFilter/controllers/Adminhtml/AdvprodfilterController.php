<?php


class InternetCode_AdvancedProductFilter_Adminhtml_AdvprodfilterController extends Mage_Adminhtml_Controller_Action
{

    public function newActionHtmlAction()
    {
        $id = $this->getRequest()->getParam('id');
        $prefix = $this->getRequest()->getParam('prefix');
        $storeId = $this->getRequest()->getParam('store', 0);

        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $attributeCode = !empty($typeArr[1]) ? $typeArr[1] : '';
        if (count($typeArr) == 3) {
            $attributeCode = !empty($typeArr[2]) ? $typeArr[2] : '';
        }

        $model = Mage::getModel($type)
            ->setId($id)
            ->setType($type)
            ->setRule(Mage::getModel('advprodfilter/rule'))
            ->setPrefix($prefix);

        if ($type == 'advprodfilter/rule_condition_combine') {
            $model->setData($prefix, array());
        }

        if (!empty($attributeCode)) {
            $model->setAttribute($attributeCode);
        }

        if ($model instanceof Mage_Rule_Model_Condition_Interface) {
            $model->setJsFormObject($prefix);
            $model->setStoreId($storeId);
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }

        $this->getResponse()->setBody($html);
    }

    public function getRuleConditionChooserHtmlAction()
    {
        $request = $this->getRequest();

        switch ($request->getParam('attribute')) {
            case 'sku':
                $block = $this->getLayout()->createBlock(
                    'advprodfilter/adminhtml_rule_chooser_sku',
                    'product_rule_chooser_sku',
                    array(
                        'js_form_object' => $request->getParam('form'),
                        'store' => $request->getParam('store', 0)
                    )
                );
                break;

            case 'category_ids':
                $ids = $request->getParam('selected', array());
                if (is_array($ids)) {
                    foreach ($ids as $key => &$id) {
                        $id = (int)$id;
                        if ($id <= 0) {
                            unset($ids[$key]);
                        }
                    }

                    $ids = array_unique($ids);
                } else {
                    $ids = array();
                }

                $block = $this->getLayout()->createBlock(
                    'advprodfilter/adminhtml_rule_chooser_category',
                    'promo_widget_chooser_category_ids',
                    array('js_form_object' => $request->getParam('form'))
                )->setCategoryIds($ids);
                break;

            default:
                $block = false;
                break;
        }

        if ($block) {
            $this->getResponse()->setBody($block->toHtml());
        }
    }

    //########################################

    public function categoriesJsonAction()
    {
        if ($categoryId = (int)$this->getRequest()->getPost('id')) {
            $this->getRequest()->setParam('id', $categoryId);

            if (!$category = $this->_initCategory()) {
                return;
            }

            $this->getResponse()->setBody(
                $this->getLayout()->createBlock('adminhtml/catalog_category_tree')
                    ->getTreeJson($category)
            );
        }
    }

    protected function _initCategory()
    {
        $categoryId = (int)$this->getRequest()->getParam('id', false);
        $storeId = (int)$this->getRequest()->getParam('store');

        $category = Mage::getModel('catalog/category');
        $category->setStoreId($storeId);

        if ($categoryId) {
            $category->load($categoryId);
            if ($storeId) {
                $rootId = Mage::app()->getStore($storeId)->getRootCategoryId();
                if (!in_array($rootId, $category->getPathIds())) {
                    $this->_redirect('*/*/', array('_current' => true, 'id' => null));
                    return false;
                }
            }
        }

        Mage::register('category', $category);
        Mage::register('current_category', $category);

        return $category;
    }
}
