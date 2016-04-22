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

class Fontis_Blog_Block_Manage_Comment extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * @var string
     */
    protected $_blockGroup = "blog";

    /**
     * @var string
     */
    protected $_controller = "manage_comment";

    public function __construct()
    {
        $this->_headerText = Mage::helper("blog")->__("Blog Comment Manager");
        parent::__construct();
        $this->_removeButton("add");
    }

    /**
     * @return Fontis_Blog_Block_Manage_Comment
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if (!Mage::helper("blog")->isSingleBlogMode()) {
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
     * @return string
     */
    public function getSwitcherHtml()
    {
        return $this->getChildHtml("blog_switcher");
    }
}
