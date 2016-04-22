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

class Fontis_Blog_Block_Blog extends Fontis_Blog_Block_Abstract
{
    const CACHE_TAG = "fontis_blog_index";

    /**
     * @return Fontis_Blog_Block_Blog
     */
    protected function _prepareLayout()
    {
        $this->getBlogHelper()->addTagToFpc(array(self::CACHE_TAG, Fontis_Blog_Helper_Data::GLOBAL_CACHE_TAG));
        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getHeader()
    {
        $blog = $this->getBlog();
        $blogTitle = $blog->getTitle();
        if ($headerImageUrl = $blog->getHeaderImageUrl()) {
            $content = '<img src="' . $headerImageUrl . '" alt="' . $blogTitle . '" />';
        } else {
            $content = $blogTitle;
        }
        return $content;
    }

    /**
     * @return Fontis_Blog_Model_Post[]|Fontis_Blog_Model_Mysql4_Post_Collection
     */
    public function getPosts()
    {
        if (!$this->hasData("blog_posts")) {
            $blog = $this->getBlog();

            /** @var $postCollection Fontis_Blog_Model_Post[]|Fontis_Blog_Model_Mysql4_Post_Collection */
            $postCollection = Mage::getModel("blog/post")->getCollection()
                ->addBlogFilter($blog)
                ->setOrder("created_time", "desc");
            Mage::getSingleton("blog/status")->addEnabledFilterToCollection($postCollection);

            $page = $this->getRequest()->getParam("page");
            $postCollection->setPageSize((int) $blog->getSetting("lists/perpage"));
            $postCollection->setCurPage($page);

            $this->setData("blog_posts", $postCollection);
        }
        return $this->getData("blog_posts");
    }

    /**
     * @return Fontis_Blog_Model_Cat[]|Fontis_Blog_Model_Mysql4_Cat_Collection
     */
    public function getCategories()
    {
        if (!$this->hasData("categories")) {
            $blog = $this->getBlog();

            $categoryCollection = Mage::getModel("blog/cat")->getCollection()
                ->addBlogFilter($blog)
                ->setOrder("sort_order", "asc");

            $this->setData("categories", $categoryCollection);
        }
        return $this->getData("categories");
    }
}
