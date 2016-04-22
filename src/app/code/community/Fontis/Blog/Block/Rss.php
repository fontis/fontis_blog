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

class Fontis_Blog_Block_Rss extends Mage_Core_Block_Abstract
{
    const CACHE_TAG = "fontis_blog_rss";

    protected function _construct()
    {
        //setting cache to save the rss for 10 minutes
        $key = "fontis_blog_rss_" . Mage::registry("current_blog_object")->getId();
        if ($cat = Mage::registry("current_blog_category")) {
            $key .= "_" . $cat->getId();
        }
        $this->setCacheKey($key);
        $this->setCacheLifetime(600);
    }

    /**
     * @return Fontis_Blog_Block_Rss
     */
    protected function _prepareLayout()
    {
        Mage::helper("blog")->addTagToFpc(array(self::CACHE_TAG, Fontis_Blog_Helper_Data::GLOBAL_CACHE_TAG));
        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        /** @var $blog Fontis_Blog_Model_Blog */
        $blog = Mage::registry("current_blog_object");
        $title = $blog->getRssFeedTitle();

        $data = array(
            "title"         => $title,
            "description"   => $title,
            "link"          => $blog->getBlogUrl(),
            "charset"       => "UTF-8",
            "entries"       => array(),
        );

        if ($rssImage = $blog->getSetting("rss/image")) {
            $data["image"] = $this->getSkinUrl($rssImage);
        }

        /** @var $postCollection Fontis_Blog_Model_Post[]|Fontis_Blog_Model_Mysql4_Post_Collection */
        $postCollection = Mage::getModel("blog/post")->getCollection()
            ->addBlogFilter($blog)
            ->setOrder("created_time", "desc");
        if ($cat = Mage::registry("current_blog_category")) {
            Mage::getSingleton("blog/status")->addCatFilterToCollection($postCollection, $cat->getId());
        }
        Mage::getSingleton("blog/status")->addEnabledFilterToCollection($postCollection);

        $limit = (int) $blog->getSetting("rss/posts");
        if ($limit > 0) {
            $postCollection->getSelect()->limit($limit);
        }

        if ($postCollection->getSize() > 0) {
            $useSummary = $blog->getSetting("rss/usesummary");
            $showAuthor = $blog->getSetting("rss/showauthor");
            foreach ($postCollection as $post) {
                /** @var $post Fontis_Blog_Model_Post */
                $entry = array(
                    "title"         => $post->getTitle(),
                    "link"          => $post->getPostUrl(),
                    "lastUpdate"    => strtotime($post->getData("created_time")),
                    "guid"          => $post->getPostUrl(),
                    "description"   => ($useSummary ? $post->getPostSummary() : $post->getPostContent()),
                );
                if ($showAuthor) {
                    $entry["author"] = $post->getAuthor()->getName();
                }

                $data["entries"][] = $entry;
            }
        }

        try {
            $rssFeedFromArray = Zend_Feed::importArray($data, "rss");
            return $rssFeedFromArray->saveXML();
        } catch (Exception $e) {
            Mage::logException($e);
            return Mage::helper("blog")->__("Error while producing RSS feed.");
        }
    }
}
