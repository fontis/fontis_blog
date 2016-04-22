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
 * @method Fontis_Blog_Block_Post_Comment setComments(array $children)
 * @method array getComments()
 */
class Fontis_Blog_Block_Post_Comment extends Mage_Core_Block_Template
{
    const GRAVATAR_SECURE_BASE_URL  = "https://secure.gravatar.com/avatar/";

    /**
     * @var Fontis_Blog_Helper_Data
     */
    protected $_blogHelper = null;

    /**
     * @return Fontis_Blog_Model_Blog
     */
    public function getBlog()
    {
        return Mage::registry("current_blog_object");
    }

    /**
     * @return Fontis_Blog_Helper_Data
     */
    public function getBlogHelper()
    {
        if ($this->_blogHelper === null) {
            $this->_blogHelper = Mage::helper("blog");
        }
        return $this->_blogHelper;
    }

    /**
     * @return bool
     */
    public function canPostNewComments()
    {
        return Mage::registry("current_blog_post")->canPostNewComments();
    }

    /**
     * @param array $children
     * @return string
     */
    public function renderChildComments($children)
    {
        /** @var $block Fontis_Blog_Block_Post_Comment */
        $block = $this->getLayout()->createBlock("blog/post_comment");
        $block->setTemplate($this->getTemplate())
            ->setComments($children);
        return $block->toHtml();
    }

    /**
     * @return bool
     */
    public function isGravatarEnabled()
    {
        if ($this->getBlog()->getSetting("comments/grav_enabled")) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return int
     */
    public function getGravatarSize()
    {
        $size = $this->getBlog()->getSetting("comments/grav_size");
        if (!$size || !is_numeric($size)) {
            $size = 75;
        } elseif ($size < 1) {
            $size = 1;
        } elseif ($size > 2048) {
            $size = 2048;
        }
        return $size;
    }

    /**
     * @param string $emailAddress
     * @return string|null
     */
    public function getGravatarUrl($emailAddress)
    {
        if (!$this->isGravatarEnabled()) {
            return null;
        }

        $url = self::GRAVATAR_SECURE_BASE_URL;
        $url .= md5(strtolower(trim($emailAddress))) . ".jpg?";

        $url .= "s=" . $this->getGravatarSize();

        if ($this->getBlog()->getSetting("comments/grav_default")) {
            $url .= "&d=mm";
        } else {
            $url .= "&d=blank";
        }

        return $url;
    }

    /**
     * @param Fontis_Blog_Model_Comment $comment
     * @return string
     */
    public function getMicrodataDatePublished(Fontis_Blog_Model_Comment $comment)
    {
        return date("c", strtotime($comment->getData("created_time")));
    }
}
