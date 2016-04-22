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
 * @method int getBlogId()
 * @method string getTitle()
 * @method string getRoute()
 * @method bool getStatus()
 * @method Fontis_Blog_Model_Blog setStoreId(int $storeId)
 * @method int getStoreId()
 */
class Fontis_Blog_Model_Blog extends Mage_Core_Model_Abstract
{
    const CACHE_TAG = "blog_blog";

    const DEFAULT_TAG_LABEL = "Tags";
    const DEFAULT_AUTHOR_LABEL = "Authors";

    /**
     * @var array
     */
    protected $_settings = array();

    /**
     * @var array
     */
    protected $_origSettings = array();

    /**
     * @var string
     */
    protected $_eventPrefix = "blog_blog";

    /**
     * @var string
     */
    protected $_eventObject = "blog";

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    protected function _construct()
    {
        $this->_init("blog/blog");
    }

    /**
     * Should only be used to load the blog in single blog mode.
     * It exists because we can't know the ID number of the blog.
     *
     * @return Fontis_Blog_Model_Blog
     */
    public function loadFirstBlog()
    {
        $firstBlogId = $this->_getResource()->getFirstBlogId();
        return $this->load($firstBlogId);
    }

    /**
     * @param array $pathInfo
     * @param int $storeId Defaults to the current store.
     * @return int|null If null, we couldn't find a match. If an integer, we did find
     *      a match. The result is how many path segments are in the blog's route.
     */
    public function loadByPathInfo(array $pathInfo, $storeId = null)
    {
        if (!isset($pathInfo[0])) {
            // If there's no path information at all, don't bother doing anything
            return null;
        }
        if ($storeId === null) {
            if (!($storeId = $this->getStoreId())) {
                $storeId = Mage::app()->getStore()->getId();
            }
        }

        /** @var $blogs Fontis_Blog_Model_Mysql4_Blog_Collection */
        $blogs = $this->getCollection();
        $blogs->addEnableFilter()
            ->addStoreFilter($storeId)
            ->addFieldToFilter("route", array("like" => array($pathInfo[0] . "%")))
            ->addOrder("CHAR_LENGTH(route)", Varien_Data_Collection_Db::SORT_ORDER_DESC);

        foreach ($blogs as $blog) {
            /** @var $blog Fontis_Blog_Model_Blog */
            $routeParts = explode("/", $blog->getData("route"));
            foreach ($routeParts as $key => $routePart) {
                if (!isset($pathInfo[$key])) {
                    // The blog's route is too long.
                    continue 2;
                }
                if ($routePart !== $pathInfo[$key]) {
                    // A part of the blog's route doesn't match the supplied path info.
                    continue 2;
                }
            }
            $this->load($blog->getData("route"), "route");
            return count($routeParts);
        }

        // We couldn't find a match.
        return null;
    }

    /**
     * @return Fontis_Blog_Model_Blog
     */
    protected function _beforeDelete()
    {
        $this->_protectFromNonAdmin();
        return parent::_beforeDelete();
    }

    /**
     * Check if a blog is visible in a given store.
     *
     * @param int|Mage_Core_Model_Store $store
     * @return bool
     */
    public function isInStore($store)
    {
        if ($store instanceof Mage_Core_Model_Store) {
            $store = $store->getId();
        }

        return in_array($store, $this->getStores());
    }

    /**
     * @param string|array $key
     * @param mixed $value
     * @return Fontis_Blog_Model_Blog
     */
    public function setSetting($key, $value = null)
    {
        $this->_hasDataChanges = true;
        if (is_array($key)) {
            $this->_settings = $key;
        } elseif (strpos($key, "/")) {
            $splitKey = explode("/", $key);
            $this->_settings[$splitKey[0]][$splitKey[1]] = $value;
        } else {
            $this->_settings[$key] = $value;
        }
        return $this;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getSetting($key = null)
    {
        if ($key === null) {
            return $this->_settings;
        }
        $key = (string) $key;
        if (strpos($key, "/")) {
            $splitKey = explode("/", $key);
            if (isset($this->_settings[$splitKey[0]][$splitKey[1]])) {
                return $this->_settings[$splitKey[0]][$splitKey[1]];
            } else {
                return Mage::getStoreConfig("fontis_blog/$key");
            }
        }
        return null;
    }

    /**
     * Clone of Mage::getStoreConfigFlag()
     *
     * @param string $key
     * @return bool
     */
    public function getSettingFlag($key)
    {
        $flag = $this->getSetting($key);
        if (!empty($flag) && "false" !== $flag) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get a setting stored as a CSV as an array.
     *
     * @param string $key
     * @return array
     */
    public function getSettingCsv($key)
    {
        $csv = $this->getSetting($key);
        if (!is_numeric($csv)) {
            return explode(',', $csv);
        }

        return null;
    }

    /**
     * If this function returns true, it means the setting is applied directly on the blog.
     * If it returns false, it means it is inheriting the setting from the defaults in the
     * system configuration. If it returns null, you didn't supply a proper key.
     *
     * @param string $key
     * @return bool|null
     */
    public function hasSetting($key)
    {
        $key = (string) $key;
        if (strpos($key, "/")) {
            $splitKey = explode("/", $key);
            if (isset($this->_settings[$splitKey[0]][$splitKey[1]])) {
                return true;
            } else {
                return false;
            }
        }
        return null;
    }

    /**
     * @param string $key
     * @return Fontis_Blog_Model_Blog
     */
    public function unsetSetting($key)
    {
        if (strpos($key, "/")) {
            $splitKey = explode("/", $key);
            unset($this->_settings[$splitKey[0]][$splitKey[1]]);
        }
        return $this;
    }

    /**
     * @param string|null $key
     * @return mixed
     */
    public function getOrigSetting($key = null)
    {
        if ($key === null) {
            return $this->_origSettings;
        }
        $key = (string) $key;
        if (strpos($key, "/")) {
            $splitKey = explode("/", $key);
            if (isset($this->_origSettings[$splitKey[0]][$splitKey[1]])) {
                return $this->_origSettings[$splitKey[0]][$splitKey[1]];
            }
        }
        return null;
    }

    /**
     * Initialize object original data
     *
     * @param string|null $key
     * @param mixed $value
     * @return Fontis_Blog_Model_Blog
     */
    public function setOrigSetting($key = null, $value = null)
    {
        if ($key === null) {
            $this->_origSettings = $this->_settings;
        } else {
            $this->_origSettings[$key] = $value;
        }
        return $this;
    }

    /**
     * Compare object data with original data
     *
     * @param string $field
     * @return bool
     */
    public function settingHasChangedFor($field)
    {
        $newData = $this->getSetting($field);
        $origData = $this->getOrigSetting($field);
        return $newData != $origData;
    }

    /**
     * Designed for use in the admin panel only.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->getId() . " - " . $this->getTitle();
    }

    /**
     * @param string $route
     * @return string
     */
    public function getBlogUrl($route = '')
    {
        $params = array();
        $baseRoute = $this->getRoute();

        // If the route provided is for the base route then make sure Magento generates the URL using the "direct"
        // method, rather than as it would normally. This prevents problems with trailing slashes.
        if ($route == '') {
            $params['_direct'] = $baseRoute;
            $baseRoute = '';
        }

        if ($storeId = $this->getStoreId()) {
            $params['_store'] = $storeId;
        }

        return Mage::getUrl($baseRoute, $params) . $route;
    }

    /**
     * @return null|string
     */
    public function getHeaderImageUrl()
    {
        if ($headerImage = $this->getSetting("blog/header_image")) {
            $urlPrefix = Mage::helper('blog')->getMediaUrl() . Fontis_Blog_Helper_Data::BLOG_MEDIA_MAIN . "/";
            return $urlPrefix . $headerImage;
        } else {
            return null;
        }
    }

    /**
     * @return string
     */
    public function getCatRoute()
    {
        $route = $this->getSetting("routing/cat");
        if ($route) {
            return trim($route, "/");
        } else {
            return Fontis_Blog_Model_Cat::DEFAULT_ROUTE;
        }
    }

    /**
     * @return string
     */
    public function getTagRoute()
    {
        $route = $this->getSetting("routing/tag");
        if ($route) {
            return trim($route, "/");
        } else {
            return Fontis_Blog_Model_Tag::DEFAULT_ROUTE;
        }
    }

    /**
     * @return string
     */
    public function getTagIndexUrl()
    {
        return $this->getBlogUrl($this->getTagRoute());
    }

    /**
     * @return string
     */
    public function getTagLabel()
    {
        $label = $this->getSetting("menu/tag_label");
        if ($label) {
            return $label;
        } else {
            return self::DEFAULT_TAG_LABEL;
        }
    }

    /**
     * @return string
     */
    public function getAuthorRoute()
    {
        $route = $this->getSetting("routing/author");
        if ($route) {
            return trim($route, "/");
        } else {
            return Fontis_Blog_Model_Author::DEFAULT_ROUTE;
        }
    }

    /**
     * @return string
     */
    public function getAuthorIndexUrl()
    {
        return $this->getBlogUrl($this->getAuthorRoute());
    }

    /**
     * @return string
     */
    public function getAuthorLabel()
    {
        $label = $this->getSetting("authors/label");
        if ($label) {
            return $label;
        } else {
            return self::DEFAULT_AUTHOR_LABEL;
        }
    }

    /**
     * @return bool
     */
    public function useRecaptcha()
    {
        if ($this->getSetting("comments/recaptcha")) {
            if (Mage::helper("blog")->useRecaptcha()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param Fontis_Blog_Model_Post $post
     * @return string
     */
    public function getPostPageTitle(Fontis_Blog_Model_Post $post)
    {
        $string = $this->getSetting("posts/title_format");
        $tokens = array(
            "[blog:title]",
            "[post:title]",
        );
        $replacements = array(
            $this->getTitle(),
            $post->getTitle(),
        );
        return str_replace($tokens, $replacements, $string);
    }

    /**
     * @param Fontis_Blog_Model_Author $author
     * @return string
     */
    public function getAuthorPageHeading(Fontis_Blog_Model_Author $author)
    {
        $string = $this->getSetting("authors/heading_format");
        $tokens = array(
            "[author:name]",
        );
        $replacements = array(
            $author->getName(),
        );
        return str_replace($tokens, $replacements, $string);
    }

    /**
     * @return string
     */
    public function getLinkLabel()
    {
        if ($title = $this->getSetting("menu/link_label")) {
            return $title;
        } else {
            return $this->getTitle();
        }
    }

    /**
     * @return string
     */
    public function getRssFeedTitle()
    {
        if ($title = $this->getSetting("rss/title")) {
            return $title;
        } else {
            return $this->getTitle();
        }
    }
}
