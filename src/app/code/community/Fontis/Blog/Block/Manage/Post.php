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

class Fontis_Blog_Block_Manage_Post extends Fontis_Blog_Block_Manage_Abstract
{
    /**
     * @var string
     */
    protected $blogGridName = "Post";

    /**
     * @var string
     */
    protected $_controller = "manage_post";

    public function __construct()
    {
        $this->_headerText = Mage::helper("blog")->__("Blog Post Manager");
        parent::__construct();
    }

    /**
     * @return string
     */
    public function getAddNewButtonHtml()
    {
        $child = $this->getChild("add_new_button");
        if (Mage::helper("blog")->isSingleBlogMode()) {
            /** @var $blog Fontis_Blog_Model_Blog */
            $blog = Mage::getModel("blog/blog")->loadFirstBlog();
            if ($blogId = $blog->getId()) {
                $child->setData("onclick", "setLocation('" . $this->getUrl("*/*/new", array("blog" => $blogId)) . "')");
                $html = $child->toHtml();
            } else {
                $html = $this->__("No Blog Exists") . "&nbsp;&nbsp;";
                $html .= 'Click <a href="' . $this->getUrl("blog_blog") . '">here</a> to create a blog.&nbsp;&nbsp;';
                $child->setData("class", "add disabled");
                $child->setData("onclick", "alert('" . $this->__("Cannot create a new post. Please create a blog first.") . "'); return false;");
                $html .= $child->toHtml();
            }
        } else {
            if ($blogId = $this->getChild("grid")->getBlogId()) {
                $child->setData("onclick", "setLocation('" . $this->getUrl("*/*/new", array("blog" => $blogId)) . "')");
                $html = $child->toHtml();
            } else {
                $html = $this->__("No Blog Selected") . "&nbsp;&nbsp;";
                $child->setData("class", "add disabled");
                // The slashes are escaped so they can be evaluated by the Javascript.
                $child->setData("onclick", "alert('" . $this->__("Cannot create a new post. Please choose a blog in the \\'Choose Blog\\' dropdown.") . "'); return false;");
                $html .= $child->toHtml();
            }
        }
        return $html;
    }
}
