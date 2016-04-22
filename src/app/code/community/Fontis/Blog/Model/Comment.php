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
 * @method int getCommentId()
 * @method Fontis_Blog_Model_Comment setPostId(int $postId)
 * @method int getPostId()
 * @method Fontis_Blog_Model_Comment setBlogId(int $blogId)
 * @method int getBlogId()
 * @method string getComment()
 * @method string getUser()
 * @method string getEmail()
 * @method Fontis_Blog_Model_Comment setInReplyTo(int $commentId)
 * @method int|null getInReplyTo()
 * @method Fontis_Blog_Model_Comment setStatus(int $status)
 * @method int getStatus()
 * @method int getCustomerId()
 * @method Fontis_Blog_Model_Comment setCreatedTime(string $createdTime)
 * @method Fontis_Blog_Model_Comment setUpdateTime(string $updateTime)
 */
class Fontis_Blog_Model_Comment extends Mage_Core_Model_Abstract
{
    const CACHE_TAG = "blog_comment";

    const COMMENT_UNAPPROVED = 1;
    const COMMENT_APPROVED = 2;

    /**
     * @var string
     */
    protected $_eventPrefix = "blog_comment";

    /**
     * @var string
     */
    protected $_eventObject = "blog_comment";

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    public function _construct()
    {
        $this->_init("blog/comment");
    }

    /**
     * @return Fontis_Blog_Model_Post
     */
    public function getPost()
    {
        if (!$this->hasData("post")) {
            if ($post = Mage::registry("current_blog_post")) {
                $this->setData("post", $post);
            } else {
                $post = Mage::getModel("blog/post")->load($this->getPostId());
                $this->setData("post", $post);
            }
        }
        return $this->getData("post");
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
            $this->setData("blog", $blog);
        }
        return $this->getData("blog");
    }

    /**
     * Returns the time at which the comment was submitted, formatted according to
     * the blog's date format setting.
     *
     * @param bool $includeTime
     * @return string
     */
    public function getCreatedTime($includeTime = true)
    {
        return Mage::helper("core")->formatTime($this->getData("created_time"), $this->getBlog()->getSetting("blog/dateformat"), $includeTime);
    }

    /**
     * Returns the time at which the comment was updated, formatted according to
     * the blog's date format setting.
     *
     * @param bool $includeTime
     * @return string
     */
    public function getUpdateTime($includeTime = true)
    {
        return Mage::helper("core")->formatTime($this->getData("update_time"), $this->getBlog()->getSetting("blog/dateformat"), $includeTime);
    }

    public function sendNewCommentNotificationEmail()
    {
        $blog = $this->getBlog();
        if ($blog->getSetting("comments/recipient_email") == null || $this->getStatus() != Fontis_Blog_Model_Comment::COMMENT_UNAPPROVED) {
            return;
        }

        /** @var Mage_Core_Model_Translate $translate */
        $translate = Mage::getSingleton("core/translate");

        $translate->setTranslateInline(false);
        try {
            $emailTemplateData = Mage::getModel("varien/object")->setData(array(
                "comment" => $this->getComment(),
                "email" => $this->getEmail(),
                "url" => Mage::app()->getStore(Mage_Core_Model_App::ADMIN_STORE_ID)->getUrl("adminhtml/blog_comment/edit", array("id" => $this->getId())),
                "user" => $this->getUser(),
            ));

            /* @var $mailTemplate Mage_Core_Model_Email_Template */
            $mailTemplate = Mage::getModel("core/email_template");
            $mailTemplate->setDesignConfig(array("area" => "frontend"));
            $mailTemplate->sendTransactional(
                $blog->getSetting("comments/email_template"),
                $blog->getSetting("comments/sender_email_identity"),
                $blog->getSetting("comments/recipient_email"),
                null,
                array("data" => $emailTemplateData)
            );
        } catch (Exception $e) {
            Mage::logException($e);
        } finally {
            $translate->setTranslateInline(true);
        }
    }
}
