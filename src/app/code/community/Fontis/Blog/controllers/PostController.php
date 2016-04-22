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

class Fontis_Blog_PostController extends Fontis_Blog_Controller_Abstract_Frontend
{
    public function viewAction()
    {
        $post = $this->getPost();

        $layout = $this->prepareLayout("blog-post-view");

        if ($head = $layout->getBlock("head")) {
            /** @var $head Mage_Page_Block_Html_Head */
            $head->setTitle($this->getBlog()->getPostPageTitle($post));
            $head->setKeywords($post->getMetaKeywords());
            $head->setDescription($post->getMetaDescription());
        }
        $this->initLayoutMessages(array("customer/session"))->renderLayout();
    }

    /**
     * @return Fontis_Blog_Model_Post
     */
    protected function getPost()
    {
        return Mage::registry("current_blog_post");
    }

    /**
     * @param bool $linkifyBlogCrumb
     * @return array
     */
    protected function getBreadcrumbs($linkifyBlogCrumb = true)
    {
        $breadcrumbs = parent::getBreadcrumbs($linkifyBlogCrumb);
        $post = $this->getPost();
        $breadcrumbs["blog_post"] = array(
            "label" => $post->getTitle(),
            "title" => $post->getTitle(),
        );
        return $breadcrumbs;
    }

    public function submitCommentAction()
    {
        $request = $this->getRequest();
        /** @var $blogHelper Fontis_Blog_Helper_Data */
        $blogHelper = Mage::helper("blog");
        /** @var $customerSession Mage_Customer_Model_Session */
        $customerSession = Mage::getSingleton("customer/session");

        $blog = $this->getBlog();
        $post = $this->getPost();
        $postData = $request->getPost();

        if ($post->canPostNewComments() === false) {
            // We don't need to check whether or not comments are disabled here because the router does that for us.
            if ($blog->getSettingFlag("comments/login") === true && !$customerSession->isLoggedIn()) {
                $customerSession->addError($blogHelper->__("You must be logged in to comment."));
                $this->_redirectReferer();
                return;
            } else {
                // Catch-all in case something goes horribly wrong.
                $this->_forward("noroute");
                return;
            }
        }

        if (!$request->isPost()) {
            $customerSession->addError($blogHelper->__("No comment data found."));
            $this->_redirectReferer();
            return;
        }

        // Validate the data the user sent us. If they didn't get us proper information, let them know.
        $commentData = Mage::helper("blog/comment")->validateCommentData($postData);
        if (empty($commentData["comment"]) || empty($commentData["user"]) || empty($commentData["email"])) {
            $customerSession->addError($blogHelper->__("Some comment data was missing or invalid. Please try again."));
            $this->_redirectReferer();
            return;
        }
        $commentData["post_id"] = $post->getId();

        // Validate the user's reCAPTCHA solution.
        try {
            if (!$this->validateRecaptcha($postData)) {
                $customerSession->addError($blogHelper->__("Your reCAPTCHA solution was incorrect. Please try again."));
                $this->_redirectReferer();
                return;
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $customerSession->addError($blogHelper->__("Sorry, an unknown error occurred. Please try again."));
            $this->_redirectReferer();
            return;
        }

        // Prepare our comment model.
        /** @var $comment Fontis_Blog_Model_Comment */
        $comment = Mage::getModel("blog/comment")
            ->setData($commentData)
            ->setBlog($blog)
            ->setBlogId($blog->getId())
            ->setCustomerId($customerSession->getCustomer()->getId())
            ->setCreatedTime(now());
        if ($blog->getSetting("comments/approval")) {
            $comment->setStatus(Fontis_Blog_Model_Comment::COMMENT_APPROVED);
        } else if ($customerSession->isLoggedIn() && $blog->getSetting("comments/loginauto")) {
            $comment->setStatus(Fontis_Blog_Model_Comment::COMMENT_APPROVED);
        } else {
            $comment->setStatus(Fontis_Blog_Model_Comment::COMMENT_UNAPPROVED);
        }

        try {
            $comment->save();

            if ($comment->getStatus() == Fontis_Blog_Model_Comment::COMMENT_UNAPPROVED) {
                $customerSession->addSuccess($blogHelper->__("Your comment has been submitted and is awaiting approval."));
            } else {
                $customerSession->addSuccess($blogHelper->__("Your comment has been submitted."));
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $customerSession->addError($blogHelper->__("Sorry, an error occurred while saving your comment. Please try again."));
            $this->_redirectReferer();
            return;
        }

        $comment->sendNewCommentNotificationEmail();

        $this->_redirectReferer();
    }

    /**
     * @param array $postData
     * @return bool
     */
    protected function validateRecaptcha(array $postData)
    {
        if ($this->getBlog()->useRecaptcha()) {
            if (!empty($postData["recaptcha_challenge_field"])) {
                $challengeField = $postData["recaptcha_challenge_field"];
            } else {
                return false;
            }
            if (!empty($postData["recaptcha_response_field"])) {
                $responseField = $postData["recaptcha_response_field"];
            } else {
                return false;
            }
            $privateKey = Mage::getStoreConfig("fontis_recaptcha/setup/private_key");
            return Mage::helper("fontis_recaptcha")->recaptcha_check_answer(
                $privateKey,
                $_SERVER["REMOTE_ADDR"],
                $challengeField,
                $responseField
            );
        } else {
            // If reCAPTCHA isn't available or enabled, always return true.
            return true;
        }
    }
}
