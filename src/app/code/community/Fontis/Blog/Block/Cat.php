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

class Fontis_Blog_Block_Cat extends Fontis_Blog_Block_Abstract
{
    const CACHE_TAG = "fontis_blog_cat";

    /**
     * @var Fontis_Blog_Model_Cat
     */
    protected $_cat = null;

    /**
     * @return Fontis_Blog_Block_Cat
     */
    protected function _prepareLayout()
    {
        $this->getBlogHelper()->addTagToFpc(array(self::CACHE_TAG . "_" . $this->getCat()->getId(), Fontis_Blog_Helper_Data::GLOBAL_CACHE_TAG));
        return parent::_prepareLayout();
    }

    /**
     * @return Fontis_Blog_Model_Post[]|Fontis_Blog_Model_Mysql4_Post_Collection
     */
    public function getPosts()
    {
        if (!$this->hasData("cat_posts")) {
            $cat = $this->getCat();

            $blog = $this->getBlog();
            $page = (int) $this->getRequest()->getParam("page");
            $postCollection = Mage::getModel("blog/post")->getCollection()
                ->addBlogFilter($blog)
                ->addCatFilter($cat->getCatId())
                ->setOrder("created_time", "desc");
            Mage::getSingleton("blog/status")->addEnabledFilterToCollection($postCollection);

            $postCollection->setPageSize((int) $blog->getSetting("lists/perpage"));
            $postCollection->setCurPage($page);

            $this->setData("cat_posts", $postCollection);
        }
        return $this->getData("cat_posts");
    }

    /**
     * @return Fontis_Blog_Model_Cat
     */
    public function getCat()
    {
        if ($this->_cat === null) {
            $this->_cat = Mage::registry("current_blog_category");
        }
        return $this->_cat;
    }

    /**
     * @return string
     */
    public function getHeader()
    {
        $category = $this->getCat();
        if ($imageUrl = $category->getImageUrl()) {
            $content = '<img src="' . $imageUrl . '" alt="' . $category->getTitle() . '" />';
        } else {
            $content = $category->getTitle();
        }

        return $content;
    }
}
