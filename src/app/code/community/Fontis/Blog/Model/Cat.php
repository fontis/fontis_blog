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
 * @method int getCatId()
 * @method string getTitle()
 * @method string getIdentifier()
 * @method string getMetaKeywords()
 * @method string getMetaDescription()
 * @method Fontis_Blog_Model_Cat setBlogId(int $blogId)
 * @method int getBlogId()
 * @method Fontis_Blog_Model_Tag setBlog(Fontis_Blog_Model_Blog $blog)
 */
class Fontis_Blog_Model_Cat extends Mage_Core_Model_Abstract
{
    const CACHE_TAG = "blog_cat";

    const DEFAULT_ROUTE = "cat";
    const CAT_IMAGE_FIELDNAME = "image";
    const CAT_IMAGE_BASEPATH = "fontis_blog/cats/";

    /**
     * @var string
     */
    protected $_eventPrefix = "blog_cat";

    /**
     * @var string
     */
    protected $_eventObject = "blog_cat";

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    protected function _construct()
    {
        $this->_init("blog/cat");
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
    public function getCatUrl()
    {
        if ($identifier = $this->getIdentifier()) {
            $blog = $this->getBlog();
            return $blog->getBlogUrl($blog->getCatRoute() . "/" . $identifier);
        }
        return "";
    }

    /**
     * @return string
     */
    public function getCatUrlPath()
    {
        if ($identifier = $this->getIdentifier()) {
            $blog = $this->getBlog();
            return $blog->getRoute() . "/" . $blog->getCatRoute() . "/" . $identifier;
        }
        return "";
    }

    /**
     * @return string|null
     */
    public function getImageUrl()
    {
        if ($image = $this->getData(self::CAT_IMAGE_FIELDNAME)) {
            return Mage::getBaseUrl("media") . Fontis_Blog_Model_Cat::CAT_IMAGE_BASEPATH . $image;
        } else {
            return null;
        }
    }

    /**
     * @return string|null
     */
    public function getImageDir()
    {
        if ($image = $this->getData(self::CAT_IMAGE_FIELDNAME)) {
            return Mage::getBaseDir("media") . Fontis_Blog_Model_Cat::CAT_IMAGE_BASEPATH . $image;
        } else {
            return null;
        }
    }

    /**
     * @return Fontis_Blog_Model_Cat
     */
    protected function _beforeDelete()
    {
        $this->_protectFromNonAdmin();
        return parent::_beforeDelete();
    }
}
