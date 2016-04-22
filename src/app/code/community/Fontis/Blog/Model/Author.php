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
 * @method int getAuthorId()
 * @method string getName()
 * @method string getIdentifier()
 * @method int getBlogId()
 * @method Fontis_Blog_Model_Tag setBlog(Fontis_Blog_Model_Blog $blog)
 */
class Fontis_Blog_Model_Author extends Mage_Core_Model_Abstract
{
    const CACHE_TAG = "blog_author";

    const DEFAULT_ROUTE = "author";

    /**
     * @var string
     */
    protected $_eventPrefix = "blog_author";

    /**
     * @var string
     */
    protected $_eventObject = "blog_author";

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    protected function _construct()
    {
        $this->_init("blog/author");
    }

    /**
     * @return Fontis_Blog_Model_Blog
     */
    public function getBlog()
    {
        if (!$this->hasData("blog")) {
            if (!$blog = Mage::registry("current_blog_object")) {
                $blog = Mage::getModel("blog/blog")->load($this->getBlogId());
            }
            if ($storeId = $this->getStoreId()) {
                $blog->setStoreId($storeId);
            }
            $this->setData("blog", $blog);
        }
        return $this->getData("blog");
    }

    /**
     * @return string
     */
    public function getAuthorUrl()
    {
        if ($identifier = $this->getIdentifier()) {
            $blog = $this->getBlog();
            return $blog->getBlogUrl($blog->getAuthorRoute() . "/" . $identifier);
        }
        return "";
    }

    /**
     * @return string
     */
    public function getAuthorUrlPath()
    {
        if ($identifier = $this->getIdentifier()) {
            $blog = $this->getBlog();
            return $blog->getRoute() . "/" . $blog->getAuthorRoute() . "/" . $identifier;
        }
        return "";
    }

    /**
     * Get the number of posts created by a particular author, optionally filtered by a blog.
     *
     * @param bool $onlyEnabled
     * @param Fontis_Blog_Model_Blog|int $blog
     * @return int
     */
    public function getPostCount($onlyEnabled = true, $blog = null)
    {
        return $this->getResource()->getPostCount($this, $onlyEnabled, $blog);
    }

    /**
     * @return Fontis_Blog_Model_Author
     */
    protected function _beforeDelete()
    {
        $this->_protectFromNonAdmin();
        return parent::_beforeDelete();
    }
}
