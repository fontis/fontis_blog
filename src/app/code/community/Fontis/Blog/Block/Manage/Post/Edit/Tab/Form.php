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

class Fontis_Blog_Block_Manage_Post_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * @return Fontis_Blog_Block_Manage_Post_Edit_Tab_Form
     */
    protected function _prepareForm()
    {
        /** @var $blogHelper Fontis_Blog_Helper_Data */
        $blogHelper = Mage::helper("blog");
        /** @var $form Varien_Data_Form */
        $form = Mage::getModel("varien/data_form");
        $this->setForm($form);
        /** @var $post Fontis_Blog_Model_Post */
        $post = Mage::registry("blog_data");

        $fieldset = $form->addFieldset("blog_form", array(
            "legend" => $blogHelper->__("Post Information"))
        );

        $blogId = $post->getBlogId();
        if (!$blogId) {
            $blogId = $this->getRequest()->getParam("blog");
        }
        /** @var $blog Fontis_Blog_Model_Blog */
        $blog = Mage::getModel("blog/blog")->load($blogId);

        $fieldset->addType("label_text", Mage::getConfig()->getBlockClassName("blog/form_label"));
        $fieldset->addField("blog_id", "label_text", array(
            "label"     => "Blog",
            "text"      => $blog->getTitle(),
            "bold"      => true,
            "after_element_html" => '<input type="hidden" value="' . $blogId . '" name="blog_id" />',
        ));

        $fieldset->addField("title", "text", array(
            "label"     => $blogHelper->__("Title"),
            "class"     => "required-entry",
            "required"  => true,
            "name"      => "title",
        ));

        $fieldset->addField("identifier", "text", array(
            "label"                 => $blogHelper->__("Identifier"),
            "required"              => true,
            "name"                  => "identifier",
            "class"                 => "validate-identifier",
            "after_element_html"    => '<span class="hint">&nbsp;eg: domain.com/blog/identifier</span>',
        ));

        $fieldset->addField("author_id", "select", array(
            "label"                 => $blogHelper->__("Author"),
            "name"                  => "author_id",
            "required"              => true,
            "values"                => Mage::getModel("blog/system_authors")->toOptionArray(),
        ));

        /** @var $catCollection Fontis_Blog_Model_Cat[]|Fontis_Blog_Model_Mysql4_Cat_Collection */
        $catCollection = Mage::getModel("blog/cat")->getCollection()
            ->addBlogFilter($blogId)
            ->setOrder("sort_order", "asc");

        $fieldset->addField("cat_ids", "multiselect", array(
            "name"      => "cat_ids[]",
            "label"     => $blogHelper->__("Category"),
            "title"     => $blogHelper->__("Category"),
            "required"  => true,
            "values"    => $catCollection->toOptionArray("cat_id"),
        ));

        $fieldset->addField("status", "select", array(
            "label"     => $blogHelper->__("Status"),
            "name"      => "status",
            "values"    => Mage::getSingleton("blog/status")->toOptionArray(),
            "style"     => "width: 152px;",
            "after_element_html" => '<span class="hint">&nbsp;Hidden posts will not show in the blog but can still be accessed directly.</span>',
        ));

        // The options for this field appear to be backwards. Unfortunately they aren't.
        // The checks in the code are therefore for whether or not comments are disabled,
        // not enabled. This is a great example of why you should always frame things in
        // the positive, not the negative.
        // This WILL be fixed in Fontis_Blog v3.
        $fieldset->addField("comments", "select", array(
            "label"     => $blogHelper->__("Enable Comments"),
            "name"      => "comments",
            "values"    => array(
                array(
                    "value" => 0,
                    "label" => $blogHelper->__("Enabled"),
                ),
                array(
                    "value" => 1,
                    "label" => $blogHelper->__("Disabled"),
                ),
            ),
            "style"     => "width: 152px;",
            "after_element_html" => '<span class="hint">&nbsp;Disabling will close the post to new comments. It will not hide existing comments.</span>',
        ));

        $fieldset->addType("blogimage", Mage::getConfig()->getBlockClassName("blog/form_image"));

        $imageAfterElementText = Mage::getConfig()->getNode("adminhtml/blog/form/image_help_text");
        $fieldset->addField(Fontis_Blog_Model_Post::POST_IMAGE_FIELDNAME, "blogimage", array(
            "label"     => $blogHelper->__("Image"),
            "name"      => Fontis_Blog_Model_Post::POST_IMAGE_FIELDNAME,
            "required"  => false,
            "after_element_html" => '<span class="hint"><br />' . $imageAfterElementText . '</span>',
        ));
        $smallImageAfterElementText = Mage::getConfig()->getNode("adminhtml/blog/form/smallimage_help_text");
        $fieldset->addField(Fontis_Blog_Model_Post::POST_SMALLIMAGE_FIELDNAME, "blogimage", array(
            "label"     => $blogHelper->__("Small Image"),
            "name"      => Fontis_Blog_Model_Post::POST_SMALLIMAGE_FIELDNAME,
            "required"  => false,
            "after_element_html" => '<span class="hint"><br />' . $smallImageAfterElementText . '</span>',
        ));

        /** @var $wysiwyg Mage_Cms_Model_Wysiwyg_Config */
        $wysiwyg = Mage::getSingleton("cms/wysiwyg_config");
        $isGlobalWysiwygEnabled = $wysiwyg->isEnabled();
        $wysiwygConfig = array(
            "add_variables" => true,
            "add_widgets"   => true,
            "add_images"    => true,
        );

        $summaryWysiwygState = $blog->getSetting("blog/wysiwyg_summary");
        $summaryWysiwygEnabled = $isGlobalWysiwygEnabled && ($summaryWysiwygState == Fontis_Blog_Model_System_WysiwygEnabled::WYSIWYG_DEFAULT ? true : $this->isWysiwygEnabled($summaryWysiwygState));
        $summaryWysiwygConfig = $wysiwyg->getConfig(array_merge($this->wysiwygInitialise($summaryWysiwygState), $wysiwygConfig));
        $fieldset->addField("summary_content", "editor", array(
            "name"      => "summary_content",
            "label"     => $blogHelper->__("Summary Content"),
            "title"     => $blogHelper->__("Summary Content"),
            "style"     => "width: 600px; height: 180px;",
            "wysiwyg"   => $summaryWysiwygEnabled,
            "config"    => $summaryWysiwygConfig,
        ));

        $postWysiwygState = $blog->getSetting("blog/wysiwyg_post");
        $postWysiwygEnabled = $isGlobalWysiwygEnabled && ($postWysiwygState == Fontis_Blog_Model_System_WysiwygEnabled::WYSIWYG_DEFAULT ? true : $this->isWysiwygEnabled($postWysiwygState));
        $postWysiwygConfig = $wysiwyg->getConfig(array_merge($this->wysiwygInitialise($postWysiwygState), $wysiwygConfig));
        $fieldset->addField("post_content", "editor", array(
            "name"      => "post_content",
            "label"     => $blogHelper->__("Content"),
            "title"     => $blogHelper->__("Content"),
            "style"     => "width: 600px; height: 360px;",
            "wysiwyg"   => $postWysiwygEnabled,
            "config"    => $postWysiwygConfig,
        ));

        $fieldset->addType("tag_arrayinput", Mage::getConfig()->getBlockClassName("blog/form_arrayInput_tag"));

        // Get the tags this post has been tagged with
        $tagModel = Mage::getModel("blog/tag");
        /** @var $tagCollection Fontis_Blog_Model_Mysql4_Tag_Collection */
        $tagCollection = $tagModel->getCollection();
        $tagCollection->addFieldToFilter($tagModel->getIdFieldName(), array("in" => $post->getTagIds()));
        $tagArray = $tagCollection->toArray();
        $fieldset->addField("tag_ids", "tag_arrayinput", array(
            "values" => $tagArray["items"],
            "after_element_html" => '<span class="hint">Editing a tag will edit that tag for all posts. If you want to replace one tag with another, remove the first tag, then add a row for the new tag.</span>',
        ));

        $session = Mage::getSingleton("adminhtml/session");
        if ($blogData = $session->getBlogData()) {
            $form->setValues($blogData);
            $session->setBlogData(null);
        } elseif (Mage::registry("blog_data")) {
            $form->setValues(Mage::registry("blog_data")->getData());
        }

        return parent::_prepareForm();
    }

    /**
     * @param string $state
     * @return array
     */
    protected function wysiwygInitialise($state)
    {
        if ($state == Fontis_Blog_Model_System_WysiwygEnabled::WYSIWYG_DEFAULT) {
            /** @var $wysiwyg Mage_Cms_Model_Wysiwyg_Config */
            $wysiwyg = Mage::getSingleton("cms/wysiwyg_config");
            return array(
                "enabled"   => $wysiwyg->isEnabled(),
                "hidden"    => $wysiwyg->isHidden(),
            );
        } else {
            return array(
                "enabled"   => $this->isWysiwygEnabled($state),
                "hidden"    => $this->isWysiwygHidden($state),
            );
        }
    }

    /**
     * @param string $state
     * @return bool
     */
    protected function isWysiwygEnabled($state)
    {
        return in_array($state, array(Mage_Cms_Model_Wysiwyg_Config::WYSIWYG_ENABLED, Mage_Cms_Model_Wysiwyg_Config::WYSIWYG_HIDDEN));
    }

    /**
     * @param string $state
     * @return bool
     */
    protected function isWysiwygHidden($state)
    {
        return $state == Mage_Cms_Model_Wysiwyg_Config::WYSIWYG_HIDDEN;
    }
}
