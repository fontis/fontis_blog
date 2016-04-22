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

class Fontis_Blog_Model_Sitemap_Collection implements Fontis_Sitemap_Model_Collection_Interface
{
    /**
     * @var Mage_Core_Model_Store
     */
    protected $store;

    /**
     * @var Fontis_Sitemap_Helper_Data
     */
    protected $helper;

    /**
     * @var bool
     */
    protected $canUseChangeFreq;

    /**
     * @var bool
     */
    protected $canUsePriority;

    /**
     * @param Mage_Core_Model_Store $store
     */
    public function __construct(Mage_Core_Model_Store $store)
    {
        $this->store = $store;
        $this->helper = Mage::helper("fontis_sitemap");
        $this->canUseChangeFreq = $this->helper->canUseChangeFreq($this->store);
        $this->canUsePriority = $this->helper->canUsePriority($this->store);
    }

    /**
     * @param Fontis_Blog_Model_Blog $blog
     * @param string $type
     * @return string|null
     */
    protected function getChangeFreq(Fontis_Blog_Model_Blog $blog, $type)
    {
        if ($this->canUseChangeFreq === true) {
            return $blog->getSetting("sitemap/changefreq_$type");
        } else {
            return null;
        }
    }

    /**
     * @param Fontis_Blog_Model_Blog $blog
     * @param string $type
     * @return string|null
     */
    protected function getPriority(Fontis_Blog_Model_Blog $blog, $type)
    {
        if ($this->canUsePriority === true) {
            return $blog->getSetting("sitemap/priority_$type");
        } else {
            return null;
        }
    }

    /**
     * @param Fontis_Sitemap_Model_Sitemap_Entry_Collection $collection
     */
    public function addUrlsToSitemap(Fontis_Sitemap_Model_Sitemap_Entry_Collection $collection)
    {
        /** @var $blogCollection Fontis_Blog_Model_Blog[]|Fontis_Blog_Model_Mysql4_Blog_Collection */
        $blogCollection = Mage::getModel("blog/blog")->getCollection();
        $blogCollection->addEnableFilter()
            ->addStoreFilter($this->store);

        foreach ($blogCollection as $blog) {
            $blog->setStoreId($this->store->getId());
            // This ensures we're using the correct store for URL generation.
            // We also set the blog object on each post, cat and tag as we see them to ensure URL generation
            // happens correctly. Doing it this way avoids lots of unnecessary object instantiations.

            /** @var $blogIndexEntry Fontis_Sitemap_Model_Sitemap_Entry */
            $blogIndexEntry = $collection->getNewEmptyItem();
            $blogIndexEntry->setSitemapData("blog_" . $blog->getId(), $blog->getRoute(), null, $this->getChangeFreq($blog, "list"), $this->getPriority($blog, "list"));
            $collection->addItem($blogIndexEntry);

            $this->addPostUrlsToSitemap($collection, $blog);
            $this->addCatUrlsToSitemap($collection, $blog);
            $this->addTagUrlsToSitemap($collection, $blog);
            if ($blog->getSettingFlag("authors/enabled")) {
                $this->addAuthorUrlsToSitemap($collection, $blog);
            }
        }
    }

    /**
     * @param Fontis_Sitemap_Model_Sitemap_Entry_Collection $collection
     * @param Fontis_Blog_Model_Blog $blog
     */
    protected function addPostUrlsToSitemap(Fontis_Sitemap_Model_Sitemap_Entry_Collection $collection, Fontis_Blog_Model_Blog $blog)
    {
        // Get post sitemap settings
        $changeFreq = $this->getChangeFreq($blog, "post");
        $priority = $this->getPriority($blog, "post");

        // Get collection of posts
        /** @var $postCollection Fontis_Blog_Model_Post[]|Fontis_Blog_Model_Mysql4_Post_Collection */
        $postCollection = Mage::getModel("blog/post")->getCollection();
        $postCollection->addBlogFilter($blog);
        Mage::getSingleton("blog/status")->addEnabledFilterToCollection($postCollection);

        foreach ($postCollection as $post) {
            $post->setBlog($blog);

            /** @var $entry Fontis_Sitemap_Model_Sitemap_Entry */
            $entry = $collection->getNewEmptyItem();
            $entry->setSitemapData("blog_post_" . $post->getId(), $post->getPostUrlPath(), $this->formatDateUtc($post->getData("update_time")), $changeFreq, $priority);
            $collection->addItem($entry);
        }
    }

    /**
     * @param string $date
     * @return string
     */
    protected function formatDateUtc($date)
    {
        return date("c", strtotime($date));
    }

    /**
     * @param Fontis_Sitemap_Model_Sitemap_Entry_Collection $collection
     * @param Fontis_Blog_Model_Blog $blog
     */
    protected function addCatUrlsToSitemap(Fontis_Sitemap_Model_Sitemap_Entry_Collection $collection, Fontis_Blog_Model_Blog $blog)
    {
        // Get list sitemap settings
        $changeFreq = $this->getChangeFreq($blog, "list");
        $priority = $this->getPriority($blog, "list");

        // Get collection of categories
        /** @var $catCollection Fontis_Blog_Model_Cat[]|Fontis_Blog_Model_Mysql4_Cat_Collection */
        $catCollection = Mage::getModel("blog/cat")->getCollection();
        $catCollection->addBlogFilter($blog);

        foreach ($catCollection as $cat) {
            $cat->setBlog($blog);

            /** @var $entry Fontis_Sitemap_Model_Sitemap_Entry */
            $entry = $collection->getNewEmptyItem();
            $entry->setSitemapData("blog_cat_" . $cat->getId(), $cat->getCatUrlPath(), null, $changeFreq, $priority);
            $collection->addItem($entry);
        }
    }

    /**
     * @param Fontis_Sitemap_Model_Sitemap_Entry_Collection $collection
     * @param Fontis_Blog_Model_Blog $blog
     */
    protected function addTagUrlsToSitemap(Fontis_Sitemap_Model_Sitemap_Entry_Collection $collection, Fontis_Blog_Model_Blog $blog)
    {
        // Get list sitemap settings
        $changeFreq = $this->getChangeFreq($blog, "list");
        $priority = $this->getPriority($blog, "list");

        // Get collection of tags
        /** @var $tagCollection Fontis_Blog_Model_Tag[]|Fontis_Blog_Model_Mysql4_Tag_Collection */
        $tagCollection = Mage::getModel("blog/tag")->getCollection();
        $tagCollection->addBlogFilter($blog);

        foreach ($tagCollection as $tag) {
            $tag->setBlog($blog);

            /** @var $entry Fontis_Sitemap_Model_Sitemap_Entry */
            $entry = $collection->getNewEmptyItem();
            $entry->setSitemapData("blog_tag_" . $tag->getId(), $tag->getTagUrlPath(), null, $changeFreq, $priority);
            $collection->addItem($entry);
        }
    }

    /**
     * @param Fontis_Sitemap_Model_Sitemap_Entry_Collection $collection
     * @param Fontis_Blog_Model_Blog $blog
     */
    protected function addAuthorUrlsToSitemap(Fontis_Sitemap_Model_Sitemap_Entry_Collection $collection, Fontis_Blog_Model_Blog $blog)
    {
        // Get list sitemap settings
        $changeFreq = $this->getChangeFreq($blog, "list");
        $priority = $this->getPriority($blog, "list");

        // Get collection of authors
        /** @var $authorCollection Fontis_Blog_Model_Author[]|Fontis_Blog_Model_Mysql4_Author_Collection */
        $authorCollection = Mage::getModel("blog/author")->getCollection();
        $authorCollection->addBlogFilter($blog);

        foreach ($authorCollection as $author) {
            $author->setBlog($blog);

            /** @var $entry Fontis_Sitemap_Model_Sitemap_Entry */
            $entry = $collection->getNewEmptyItem();
            $entry->setSitemapData("blog_author_" . $author->getId(), $author->getAuthorUrlPath(), null, $changeFreq, $priority);
            $collection->addItem($entry);
        }
    }
}
