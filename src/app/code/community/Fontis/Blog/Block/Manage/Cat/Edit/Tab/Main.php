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

class Fontis_Blog_Block_Manage_Cat_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * @return Fontis_Blog_Block_Manage_Cat_Edit_Tab_Main
     */
    protected function _prepareForm()
    {
        $blogHelper = Mage::helper("blog");
        /** @var $form Varien_Data_Form */
        $form = Mage::getModel("varien/data_form");
        $this->setForm($form);
        $fieldset = $form->addFieldset("category_form", array("legend" => $blogHelper->__("Category Information")));

        $fieldset->addField("title", "text", array(
            "label"     => $blogHelper->__("Title"),
            "name"      => "title",
            "required"  => true,
        ));

        $fieldset->addField("identifier", "text", array(
            "label"     => $blogHelper->__("Identifier"),
            "name"      => "identifier",
            "class"     => "validate-identifier",
            "required"  => true,
        ));

        $fieldset->addField("blog_id", "select", array(
            "label"     => $blogHelper->__("Blog"),
            "name"      => "blog_id",
            "required"  => true,
            "values"    => Mage::getModel("blog/blog")->getCollection()->toDisplayOptionArray(true),
        ));

        $fieldset->addField("sort_order", "text", array(
            "label"     => $blogHelper->__("Sort Order"),
            "name"      => "sort_order",
            "class"     => "validate-number",
        ));

        $fieldset->addType("blogimage", Mage::getConfig()->getBlockClassName("blog/form_image"));

        $fieldset->addField("image", "blogimage", array(
            "label"    => $blogHelper->__("Image"),
            "name"     => "image",
            "required" => false,
        ));

        $fieldset->addField("meta_keywords", "editor", array(
            "name" => "meta_keywords",
            "label" => $blogHelper->__("Keywords"),
            "title" => $blogHelper->__("Meta Keywords"),
        ));

        $fieldset->addField("meta_description", "editor", array(
            "name" => "meta_description",
            "label" => $blogHelper->__("Description"),
            "title" => $blogHelper->__("Meta Description"),
        ));

        if (Mage::getSingleton("adminhtml/session")->getBlogData()) {
            $form->setValues(Mage::getSingleton("adminhtml/session")->getBlogData());
            Mage::getSingleton("adminhtml/session")->setBlogData(null);
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
