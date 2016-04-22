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

class Fontis_Blog_Model_Observer_Algolia
{
    const INCLUDE_IN_SEARCH_SETTING = "search/include_in_search";
    const ATTRIBUTES_TO_INDEX_SETTING = "search/attributes_to_index";
    const OBJECT_ID_PREFIX = "blog";

    /**
     * @return bool
     */
    protected function _isAlgoliaInstalled()
    {
        if (Mage::helper("core")->isModuleEnabled("Fontis_Algolia")) {
            $algoliaVersion = (string)Mage::getConfig()->getModuleConfig("Fontis_Algolia")->version;
            return version_compare($algoliaVersion, '1.4.0', '>=');
        }
        return false;
    }

    /**
     * Check if the Algolia extension is installed, enabled and Indexing is set to Update on Save.
     *
     * @param int $storeId
     * @return bool
     */
    protected function _isAlgoliaActive($storeId = null)
    {
        if ($this->_isAlgoliaInstalled() && Mage::getStoreConfigFlag(Fontis_Algolia_Block_Config::ACTIVE, $storeId)) {
            if (Mage::getSingleton("index/indexer")->getProcessByCode("fontis_algolia")->getMode() == Mage_Index_Model_Process::MODE_REAL_TIME) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add blog content to the Algolia CMS index.
     *
     * @listen fontis_algolia_cms_entity_index_before
     * @param Varien_Event_Observer $observer
     */
    public function getAllBlogContent(Varien_Event_Observer $observer)
    {
        if ($this->_isAlgoliaInstalled() === false) {
            return;
        }

        /** @var $indexData Fontis_Algolia_Model_Cms_Collection */
        $indexData = $observer->getContent();

        /** @var $blogs Fontis_Blog_Model_Blog[]|Fontis_Blog_Model_Mysql4_Blog_Collection */
        $blogs = Mage::getModel("blog/blog")->getCollection();

        foreach ($blogs as $blog) {
            if (!$blog->getSettingFlag(self::INCLUDE_IN_SEARCH_SETTING)) {
                continue;
            }

            $this->_getPostIndexContent($blog, $indexData);
        }
    }

    /**
     * Add all posts to the indexData object
     *
     * @param Fontis_Blog_Model_Blog $blog
     * @param Fontis_Algolia_Model_Cms_Collection $indexData
     */
    protected function _getPostIndexContent(Fontis_Blog_Model_Blog $blog, $indexData)
    {
        /** @var $postCollection Fontis_Blog_Model_Post[]|Fontis_Blog_Model_Mysql4_Post_Collection */
        $postCollection = Mage::getModel("blog/post")->getCollection();
        $postCollection->addBlogFilter($blog);
        Mage::getSingleton("blog/status")->addEnabledFilterToCollection($postCollection);

        foreach ($postCollection as $post) {
            $post->setBlog($blog);
            $blogPostContent = $this->_getBlogPostContent($post);
            $indexData->addItem($blogPostContent);
        }
    }

    /**
     * Get the indexable content for a blog post.
     *
     * @param Fontis_Blog_Model_Post $post
     * @param bool $batch True if we are generating blog content for a batch operation.
     * @return Varien_Object
     */
    protected function _getBlogPostContent(Fontis_Blog_Model_Post $post, $batch = true)
    {
        $blogPostContent = Mage::getModel("varien/object");
        $blogPostContent->setData("objectID", $this->_getObjectId($post->getId()));

        if ($batch === true) {
            $blogPostContent->setData("objectAction", Fontis_Algolia_Helper_Data::ADD_INDEX_ACTION);
        }

        $blogPostContent->setPath($post->getPostUrlPath());

        $postCats = $post->getCategories();
        if ($postCats !== null) {
            $blogPostContent->setCategories(implode(",", $postCats->getColumnValues("title")));
        }

        $postTags = $post->getTags();
        if ($postTags !== null) {
            $blogPostContent->setTags(implode(",", $postTags->getColumnValues("name")));
        }

        $blog = $post->getBlog();
        $blogPostContent->setBlog($blog->getTitle());

        foreach ($blog->getSettingCsv(self::ATTRIBUTES_TO_INDEX_SETTING) as $attribute) {
            // We want to store image URLs instead of the image value.
            if (strpos($attribute, "image") !== false) {
                $attribute = $attribute . "_url";
            }

            $blogPostContent->setDataUsingMethod($attribute, $post->getDataUsingMethod($attribute));
        }

        return $blogPostContent;
    }

    /**
     * Get the object ID for the Algolia index.
     *
     * @param string|int $postId
     * @return string
     */
    protected function _getObjectId($postId)
    {
        return implode("-", array(self::OBJECT_ID_PREFIX, $postId));
    }

    /**
     * Reindex a post after it is saved.
     *
     * @listen blog_post_save_commit_after
     * @param Varien_Event_Observer $observer
     */
    public function reindexPost(Varien_Event_Observer $observer)
    {
        if ($this->_isAlgoliaInstalled() === false) {
            return;
        }

        /** @var $post Fontis_Blog_Model_Post */
        // We need to reload the post model to properly load the category, tag and image attributes.
        $post = $observer->getBlogPost();
        $this->_reindexPost($post);
    }

    /**
     * Reindex a post in all stores that it is visible in.
     *
     * @param Fontis_Blog_Model_Post $post
     */
    protected function _reindexPost(Fontis_Blog_Model_Post $post)
    {
        /** @var $blog Fontis_Blog_Model_Blog */
        $blog = $post->getBlog();

        if (!$blog->getSettingFlag(self::INCLUDE_IN_SEARCH_SETTING)) {
            return;
        }

        /** @var $cmsIndexer Fontis_Algolia_Model_Indexer_Cms */
        $cmsIndexer = Mage::getSingleton("fontis_algolia/indexer_cms");
        /** @var $appEmulation Mage_Core_Model_App_Emulation */
        $appEmulation = Mage::getSingleton("core/app_emulation");

        $storeIds = $blog->getStores();
        foreach ($storeIds as $storeId) {
            if (!$this->_isAlgoliaActive($storeId)) {
                continue;
            }

            // Emulate the store we are currently reindexing the content for
            $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

            $postContent = $this->_getBlogPostContent($post, false);
            $cmsIndexer->addCmsContent($postContent, $storeId);

            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
        }
    }

    /**
     * Remove a post from the index after it is saved.
     *
     * @listen blog_post_delete_commit_after
     * @param Varien_Event_Observer $observer
     */
    public function deletePost(Varien_Event_Observer $observer)
    {
        if ($this->_isAlgoliaInstalled() === false) {
            return;
        }

        $this->_deletePost($observer->getBlogPost());
    }

    /**
     * Reindex all posts for a blog after it is saved in all stores.
     *
     * @listen blog_blog_save_commit_after
     * @param Varien_Event_Observer $observer
     */
    public function reindexBlog(Varien_Event_Observer $observer)
    {
        if ($this->_isAlgoliaInstalled() === false) {
            return;
        }

        $this->_reindexBlog($observer->getBlog());
    }

    /**
     * Reindex a blog's posts.
     *
     * @param Fontis_Blog_Model_Blog $blog
     * @return Fontis_Blog_Model_Observer_Algolia
     */
    protected function _reindexBlog(Fontis_Blog_Model_Blog $blog)
    {
        if (!$blog->getSettingFlag(self::INCLUDE_IN_SEARCH_SETTING) || !$blog->getStatus()) {
            $this->_deleteBlog($blog);
            return $this;
        }

        /** @var $cmsIndexer Fontis_Algolia_Model_Indexer_Cms */
        $cmsIndexer = Mage::getSingleton("fontis_algolia/indexer_cms");
        /** @var $appEmulation Mage_Core_Model_App_Emulation */
        $appEmulation = Mage::getSingleton("core/app_emulation");

        $storeIds = Mage::helper("fontis_algolia")->getStoreIds();
        foreach ($storeIds as $storeId) {
            if (!$this->_isAlgoliaActive($storeId)) {
                continue;
            }

            // If this blog isn't visible in this store we want to remove it from the index for this store.
            if (!$blog->isInStore($storeId)) {
                $this->_deleteBlog($blog, $storeId);
                continue;
            }

            // Emulate the store we are currently reindexing the content for
            $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

            /** @var $cmsContent Fontis_Algolia_Model_Cms_Collection */
            $indexData = Mage::getModel("fontis_algolia/cms_collection");
            $this->_getPostIndexContent($blog, $indexData);
            $cmsIndexer->batchReindexCmsCollection($indexData, $storeId);

            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
        }
    }

    /**
     * Delete all posts for a blog from the index after it is deleted in all stores.
     *
     * @listen blog_blog_delete_commit_after
     * @param Varien_Event_Observer $observer
     */
    public function deleteBlog(Varien_Event_Observer $observer)
    {
        if ($this->_isAlgoliaInstalled() === false) {
            return;
        }

        $blog = $observer->getBlog();
        $this->_deleteBlog($blog);
    }

    /**
     * Delete all posts for a blog from the index.
     *
     * @param Fontis_Blog_Model_Blog $blog
     * @param string|int $storeId
     */
    protected function _deleteBlog(Fontis_Blog_Model_Blog $blog, $storeId = null)
    {
        /** @var $postCollection Fontis_Blog_Model_Post[]|Fontis_Blog_Model_Mysql4_Post_Collection */
        $postCollection = Mage::getModel("blog/post")->getCollection()
            ->addBlogFilter($blog);
        Mage::getSingleton("blog/status")->addEnabledFilterToCollection($postCollection);

        foreach ($postCollection as $post) {
            $this->_deletePost($post, $storeId);
        }
    }

    /**
     * Delete a blog post from all CMS indexes.
     *
     * @param Fontis_Blog_Model_Post $post
     * @param string|int $storeId
     */
    protected function _deletePost(Fontis_Blog_Model_Post $post, $storeId = null)
    {
        /** @var $helper Fontis_Algolia_Helper_Data */
        $helper = Mage::helper("fontis_algolia");

        $storeIds = $helper->getStoreIds($storeId);
        /** @var $blog Fontis_Blog_Model_Blog */
        $blog = $post->getBlog();

        /** @var $cmsIndexer Fontis_Algolia_Model_Indexer_Cms */
        $cmsIndexer = Mage::getSingleton("fontis_algolia/indexer_cms");
        foreach ($storeIds as $storeId) {
            if (!$this->_isAlgoliaActive($storeId)|| !$blog->isInStore($storeId)) {
                continue;
            }

            $cmsIndexer->deleteCmsContent($this->_getObjectId($post->getId()), $storeId);
        }
    }

    /**
     * Reindex all posts for a category if the title changes.
     *
     * @listen blog_cat_save_commit_after
     * @param Varien_Event_Observer $observer
     */
    public function reindexCat(Varien_Event_Observer $observer)
    {
        if ($this->_isAlgoliaInstalled() === false) {
            return;
        }

        /** @var $category Fontis_Blog_Model_Cat */
        $category = $observer->getBlogCat();

        if ($category->getData("title") === $category->getOrigData("title")) {
            return;
        }

        /** @var $blog Fontis_Blog_Model_Blog */
        $blog = $category->getBlog();
        $this->_reindexBlog($blog);
    }

    /**
     * Reindex all posts for the blog that this category belongs to.
     *
     * @listen blog_cat_delete_commit_after
     * @param Varien_Event_Observer $observer
     */
    public function deleteCat(Varien_Event_Observer $observer)
    {
        if ($this->_isAlgoliaInstalled() === false) {
            return;
        }

        /** @var $category Fontis_Blog_Model_Cat */
        $category = $observer->getBlogCat();

        /** @var $blog Fontis_Blog_Model_Blog */
        $blog = $category->getBlog();
        $this->_reindexBlog($blog);
    }
}
