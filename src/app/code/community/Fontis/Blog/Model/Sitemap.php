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

/**
 * @method Fontis_Blog_Model_Sitemap setStoreId(int $storeId)
 * @method int getStoreId()
 * @method string getSitemapFilename()
 * @method Fontis_Blog_Model_Sitemap setSitemapTime(string $date)
 */
class Fontis_Blog_Model_Sitemap extends Mage_Sitemap_Model_Sitemap
{
    /**
     * @var string
     */
    protected $_eventPrefix = "blog_sitemap";

    /**
     * @var string
     */
    protected $_eventObject = "blog_sitemap";

    /**
     * Generate XML file
     *
     * @return Mage_Sitemap_Model_Sitemap
     */
    public function generateXml()
    {
        /** @var $io Varien_Io_File */
        $io = Mage::getModel("varien/io_file");
        $io->setAllowCreateFolders(true);
        $io->open(array("path" => $this->getPath()));
        $io->streamOpen($this->getSitemapFilename());

        $io->streamWrite('<?xml version="1.0" encoding="UTF-8"?>' . "\n");
        $io->streamWrite('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');

        $storeId = $this->getStoreId();
        $date = Mage::getSingleton("core/date")->gmtDate("Y-m-d");
        $baseUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);

        /**
         * Generate categories sitemap
         */
        $changefreq = (string) Mage::getStoreConfig("sitemap/category/changefreq", $storeId);
        $priority   = (string) Mage::getStoreConfig("sitemap/category/priority", $storeId);
        $collection = Mage::getResourceModel("sitemap/catalog_category")->getCollection($storeId);
        foreach ($collection as $item) {
            $xml = sprintf('<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $item->getUrl()),
                $date,
                $changefreq,
                $priority
            );
            $io->streamWrite($xml);
        }
        unset($collection);

        /**
         * Generate products sitemap
         */
        $changefreq = (string) Mage::getStoreConfig("sitemap/product/changefreq", $storeId);
        $priority   = (string) Mage::getStoreConfig("sitemap/product/priority", $storeId);
        $collection = Mage::getResourceModel("sitemap/catalog_product")->getCollection($storeId);
        foreach ($collection as $item) {
            $xml = sprintf('<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $item->getUrl()),
                $date,
                $changefreq,
                $priority
            );
            $io->streamWrite($xml);
        }
        unset($collection);

        /**
         * Generate cms pages sitemap
         */
        $changefreq = (string) Mage::getStoreConfig("sitemap/page/changefreq", $storeId);
        $priority   = (string) Mage::getStoreConfig("sitemap/page/priority", $storeId);
        $collection = Mage::getResourceModel("sitemap/cms_page")->getCollection($storeId);
        foreach ($collection as $item) {
            $xml = sprintf('<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $item->getUrl()),
                $date,
                $changefreq,
                $priority
            );
            $io->streamWrite($xml);
        }
        unset($collection);

        /**
         * Generate blog pages sitemap
         */
        /** @var $blogCollection Fontis_Blog_Model_Blog[]|Fontis_Blog_Model_Mysql4_Blog_Collection */
        $blogCollection = Mage::getModel("blog/blog")->getCollection()
            ->addStoreFilter($storeId)
            ->addEnableFilter();
        foreach ($blogCollection as $blog) {
            $blog->setStoreId($storeId);
            // This ensures we're using the correct store for URL generation.
            // We also set the blog object on each post, cat and tag as we see them to ensure URL generation
            // happens correctly. Doing it this way avoids lots of unnecessary object instantiations.

            // Get post sitemap settings
            $postChangefreq = (string) $blog->getSetting("sitemap/changefreq_post");
            $postPriority   = (string) $blog->getSetting("sitemap/priority_post");

            // Get collection of posts
            /** @var $postCollection Fontis_Blog_Model_Post[]|Fontis_Blog_Model_Mysql4_Post_Collection */
            $postCollection = Mage::getModel("blog/post")->getCollection();
            $postCollection->addBlogFilter($blog);
            Mage::getSingleton("blog/status")->addEnabledFilterToCollection($postCollection);
            foreach ($postCollection as $post) {
                $post->setBlog($blog);
                $xml = sprintf('<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                    htmlspecialchars($post->getPostUrl()),
                    date("Y-m-d", strtotime($post->getData("update_time"))),
                    $postChangefreq,
                    $postPriority
                );
                $io->streamWrite($xml);
            }
            unset($postCollection);

            // Get list sitemap settings
            $listChangefreq = (string) $blog->getSetting("sitemap/changefreq_list");
            $listPriority   = (string) $blog->getSetting("sitemap/priority_list");

            // Add the main blog index page to the sitemap.
            $xml = sprintf('<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($blog->getBlogUrl()),
                $date,
                $listChangefreq,
                $listPriority
            );
            $io->streamWrite($xml);

            // Get collection of categories
            /** @var $catCollection Fontis_Blog_Model_Cat[]|Fontis_Blog_Model_Mysql4_Cat_Collection */
            $catCollection = Mage::getModel("blog/cat")->getCollection();
            $catCollection->addBlogFilter($blog);
            foreach ($catCollection as $cat) {
                $cat->setBlog($blog);
                $xml = sprintf('<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                    htmlspecialchars($cat->getCatUrl()),
                    $date,
                    $listChangefreq,
                    $listPriority
                );
                $io->streamWrite($xml);
            }
            unset($catCollection);

            // Get collection of tags
            /** @var $tagCollection Fontis_Blog_Model_Tag[]|Fontis_Blog_Model_Mysql4_Tag_Collection */
            $tagCollection = Mage::getModel("blog/tag")->getCollection();
            $tagCollection->addBlogFilter($blog);
            foreach ($tagCollection as $tag) {
                $tag->setBlog($blog);
                $xml = sprintf('<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                    htmlspecialchars($tag->getTagUrl()),
                    $date,
                    $listChangefreq,
                    $listPriority
                );
                $io->streamWrite($xml);
            }
            unset($tagCollection);
        }

        $io->streamWrite('</urlset>');
        $io->streamClose();

        $this->setSitemapTime(Mage::getSingleton("core/date")->gmtDate("Y-m-d H:i:s"));
        $this->save();

        return $this;
    }
}
