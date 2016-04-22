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

abstract class Fontis_Blog_Block_Manage_Abstract extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * @var bool
     */
    protected $useBlogFilter = true;

    /**
     * @var string
     */
    protected $blogGridName = null;

    /**
     * @var string
     */
    protected $_blockGroup = "blog";

    /**
     * @return Fontis_Blog_Block_Manage_Abstract
     */
    protected function _prepareLayout()
    {
        $addNewButton = $this->_addButtonChildBlock("add_new_button");
        $addNewButton->setData(array(
            "label"     => Mage::helper("blog")->__("Add " . $this->blogGridName),
            "onclick"   => "setLocation('" . $this->getUrl("*/*/new") . "')",
            "class"     => "add",
        ));

        parent::_prepareLayout();

        if ($this->useBlogFilter()) {
            /** @var $blogSwitcherBlock Fontis_Blog_Block_Form_BlogSwitcher */
            $blogSwitcherBlock = $this->getLayout()->createBlock("blog/form_blogSwitcher");
            if ($blogId = $this->getChild("grid")->getBlogId()) {
                $blogSwitcherBlock->setCurrentBlogId($blogId);
            }
            $this->setChild("blog_switcher", $blogSwitcherBlock);
        }

        return $this;
    }

    /**
     * @return bool
     */
    protected function useBlogFilter()
    {
        if ($this->useBlogFilter && !Mage::helper("blog")->isSingleBlogMode()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getAddNewButtonHtml()
    {
        return $this->getChildHtml("add_new_button");
    }

    /**
     * @return string
     */
    public function getSwitcherHtml()
    {
        return $this->getChildHtml("blog_switcher");
    }
}
