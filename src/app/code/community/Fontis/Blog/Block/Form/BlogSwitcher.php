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
 * @method Fontis_Blog_Block_Form_BlogSwitcher setCurrentBlogId(int $blogId)
 * @method int getCurrentBlogId()
 * @method bool hasCurrentBlogId()
 */
class Fontis_Blog_Block_Form_BlogSwitcher extends Mage_Adminhtml_Block_Template
{
    /**
     * @var array
     */
    protected $_blogs = null;

    public function __construct()
    {
        parent::__construct();
        if (!$this->getTemplate()) {
            $this->setTemplate("fontis/blog/forms/switcher.phtml");
        }
    }

    /**
     * @return string
     */
    public function getSwitchUrl()
    {
        if ($url = $this->getData("switch_url")) {
            return $url;
        }
        return $this->getUrl("*/*/*");
    }

    /**
     * @return array
     */
    public function getBlogs()
    {
        if ($this->_blogs === null) {
            $this->_blogs = Mage::getModel("blog/blog")->getCollection()->toDisplayOptionArray(false);
        }
        return $this->_blogs;
    }

    /**
     * @return int
     */
    public function getBlogId()
    {
        if ($this->hasCurrentBlogId()) {
            return $this->getCurrentBlogId();
        } else {
            return $this->getRequest()->getParam("blog");
        }
    }

    /**
     * @return string
     */
    public function getDefaultBlogName()
    {
        return $this->__("All Blogs");
    }
}
