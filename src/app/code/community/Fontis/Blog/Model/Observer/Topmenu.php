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

class Fontis_Blog_Model_Observer_Topmenu
{
    /**
     * @var Fontis_Blog_Model_Blog
     */
    protected $_currentBlog;

    public function __construct()
    {
        $this->_currentBlog = Mage::registry("current_blog_object");
    }

    /**
     * Add menu items for any blogs that are set to display in the main menu.
     *
     * Only compatible with CE1.9/EE1.14+ - in all earlier versions, this method will never be called.
     *
     * @listen page_block_html_topmenu_gethtml_before
     * @param Varien_Event_Observer $observer
     * @return Fontis_Blog_Model_Observer
     */
    public function prepareMenu(Varien_Event_Observer $observer)
    {
        /** @var $menu Varien_Data_Tree_Node */
        $menu = $observer->getMenu();
        $tree = $menu->getTree();

        /** @var $blogCollection Fontis_Blog_Model_Blog[]|Fontis_Blog_Model_Mysql4_Blog_Collection */
        $blogCollection = Mage::getModel("blog/blog")->getCollection();
        $blogCollection->addEnableFilter()
            ->addStoreFilter(Mage::app()->getStore());

        foreach ($blogCollection as $blog) {
            if ($blog->getSettingFlag("menu/primary") === false) {
                continue;
            }

            $nodeData = array(
                "name"      => $blog->getLinkLabel(),
                "id"        => "blog-" . $blog->getId(),
                "url"       => $blog->getBlogUrl(),
                "is_active" => $this->_isBlogActive($blog),
            );

            $node = new Varien_Data_Tree_Node($nodeData, "id", $tree, $menu);
            $menu->addChild($node);
        }

        return $this;
    }

    /**
     * Determine if the supplied blog post is active for the current page
     *
     * If a there is a current blog for this page, and the ID of that blog matches
     * the ID of the supplied blog, that blog is considered active so true is returned.
     *
     * If there is no active blog, or the active blog has a different ID to the
     * supplied blog, false will be returned.
     *
     * @param Fontis_Blog_Model_Blog $blog
     * @return bool
     */
    protected function _isBlogActive(Fontis_Blog_Model_Blog $blog)
    {
        if (is_object($this->_currentBlog) && $this->_currentBlog->getId() == $blog->getId()) {
            return true;
        }

        return false;
    }
}
