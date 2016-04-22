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

class Fontis_Blog_Block_Tag extends Fontis_Blog_Block_Abstract
{
    const CACHE_TAG = "fontis_blog_tags";

    /**
     * @var Fontis_Blog_Model_Tag
     */
    protected $_tag = null;

    /**
     * @return Fontis_Blog_Block_Tag
     */
    protected function _prepareLayout()
    {
        $this->getBlogHelper()->addTagToFpc(array(self::CACHE_TAG, Fontis_Blog_Helper_Data::GLOBAL_CACHE_TAG));
        return parent::_prepareLayout();
    }

    /**
     * @return Fontis_Blog_Model_Post[]|Fontis_Blog_Model_Mysql4_Post_Collection
     */
    public function getPosts()
    {
        if ($this->_posts) {
            return $this->_posts;
        }

        /** @var $collection Fontis_Blog_Model_Post[]|Fontis_Blog_Model_Mysql4_Post_Collection */
        $collection = Mage::getModel("blog/post")->getCollection();
        $collection->addBlogFilter($this->getBlog())
            ->setOrder("created_time", "desc")
            ->addTagFilter($this->getTag());
        Mage::getSingleton("blog/status")->addEnabledFilterToCollection($collection);

        $this->_posts = $collection;
        return $collection;
    }

    /**
     * @return Fontis_Blog_Model_Tag
     */
    public function getTag()
    {
        if (!$this->_tag) {
            $this->_tag = Mage::registry("current_blog_tag");
        }
        return $this->_tag;
    }

    /**
     * @return string
     */
    public function getHeader()
    {
        return $this->getBlogHelper()->__("Posts tagged with %s", $this->getTag()->getName());
    }
}
