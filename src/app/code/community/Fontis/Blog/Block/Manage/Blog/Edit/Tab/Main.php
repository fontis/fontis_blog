<?php
/**
 * Fontis Blog Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/osl-3.0.php
 *
 * Parts of this software are derived from code originally developed by
 * Robert Chambers <magento@robertchambers.co.uk>
 * and released as "Lazzymonk's Blog" 0.5.8 in 2009.
 *
 * @category   Fontis
 * @package    Fontis_Blog
 * @copyright  Copyright (c) 2016 Fontis Pty. Ltd. (https://www.fontis.com.au)
 * @license    https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Fontis_Blog_Block_Manage_Blog_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * @return Fontis_Blog_Block_Manage_Blog_Edit_Tab_Main
     */
    protected function _prepareForm()
    {
        $blogHelper = Mage::helper("blog");
        /** @var $form Varien_Data_Form */
        $form = Mage::getModel("varien/data_form");
        $this->setForm($form);
        $fieldset = $form->addFieldset("blog_form", array("legend" => $blogHelper->__("Blog Information")));

        $fieldset->addField("title", "text", array(
            "label"     => $blogHelper->__("Title"),
            "name"      => "main[title]",
            "required"  => true,
        ));

        $fieldset->addField("route", "text", array(
            "label"     => $blogHelper->__("Route"),
            "name"      => "main[route]",
            "class"     => "validate-identifier",
            "required"  => true,
        ));

        $fieldset->addField("status", "select", array(
            "label"     => $blogHelper->__("Status"),
            "name"      => "main[status]",
            "required"  => true,
            "values"    => Mage::getSingleton("blog/system_blogStatus")->toOptionArray(),
            "after_element_html" => '<span class="hint">' . $blogHelper->__("This only affects whether or not the blog is visible on the frontend.") . '</span>',
        ));

        // Check is single store mode
        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField("stores", "multiselect", array(
                "name"      => "main[stores][]",
                "label"     => Mage::helper("cms")->__("Stores"),
                "title"     => Mage::helper("cms")->__("Stores"),
                "required"  => true,
                "values"    => Mage::getSingleton("adminhtml/system_store")->getStoreValuesForForm(false, false),
            ));
        }

        $session = Mage::getSingleton("adminhtml/session");
        if ($blogData = $session->getFormData()) {
            $form->setValues($blogData);
            $session->setFormData(null);
        } elseif (Mage::registry("blog_data")) {
            $form->setValues(Mage::registry("blog_data")->getData());
        }

        return parent::_prepareForm();
    }

    /** These methods are necessary only to fulfil the interface's contract. */

    /**
     * This value is set in the layout.
     * Unless otherwise overridden, it's pulled out of Varien_Object's _data array.
     *
     * @return string
     */
    public function getTabLabel()
    {
        return parent::getTabLabel();
    }

    /**
     * This value is set in the layout.
     * Unless otherwise overridden, it's pulled out of Varien_Object's _data array.
     *
     * @return string
     */
    public function getTabTitle()
    {
        return parent::getTabTitle();
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        if (!$this->hasCanShowTab()) {
            return true;
        }
        return parent::getCanShowTab();
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return (bool) parent::getIsHidden();
    }
}
