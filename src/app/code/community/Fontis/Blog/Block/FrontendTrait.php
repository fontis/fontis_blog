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

trait Fontis_Blog_Block_FrontendTrait
{
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
    public function getCommentsEnabled()
    {
        return $this->getBlog()->getSettingFlag("comments/enabled");
    }

    /**
     * @param int $commentCount
     * @return string
     */
    public function getCommentTotalString($commentCount)
    {
        if ($commentCount == 1) {
            return $this->getBlogHelper()->__("%s Comment", $commentCount);
        } else {
            return $this->getBlogHelper()->__("%s Comments", $commentCount);
        }
    }

    /**
     * @param Fontis_Blog_Model_Post $post
     * @return array
     */
    public function getPostImageDimensions(Fontis_Blog_Model_Post $post)
    {
        return Mage::helper("blog/image")->getImageDimensions($post);
    }

    /**
     * @param Fontis_Blog_Model_Post $post
     * @return array
     */
    public function getPostSmallImageDimensions(Fontis_Blog_Model_Post $post)
    {
        return Mage::helper("blog/image")->getSmallImageDimensions($post);
    }
}
