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

class Fontis_Blog_Manage_Blog_PostController extends Fontis_Blog_Controller_Abstract_Adminhtml
{
    /**
     * @var string
     */
    protected $_aclPath = "posts";

    /**
     * @var string
     */
    protected $_menuPath = "posts";

    public function editAction()
    {
        $postId = $this->getRequest()->getParam("id");
        /** @var $post Fontis_Blog_Model_Post */
        $post = Mage::getModel("blog/post")->load($postId);

        if ($postId == 0 || $post->getId()) {
            $this->_renderPostEditPage($post);
        } else {
            $this->_getSession()->addError($this->__("Post does not exist."));
            $this->_redirectWithBlogFilterApplied();
        }
    }

    public function newAction()
    {
        $blogId = $this->getRequest()->getParam("blog");
        /** @var $blog Fontis_Blog_Model_Blog */
        $blog = Mage::getModel("blog/blog")->load($blogId);

        if ($blog->getId()) {
            $this->_renderPostEditPage(Mage::getModel("blog/post"));
        } else {
            $this->_getSession()->addError($this->__("Chosen blog does not exist."));
            $this->_redirect("*/*/");
        }
    }

    /**
     * @param Fontis_Blog_Model_Post $post
     * @return Fontis_Blog_Manage_Blog_PostController
     */
    protected function _renderPostEditPage(Fontis_Blog_Model_Post $post)
    {
        $formData = $this->_getSession()->getFormData(true);
        if (!empty($formData)) {
            $post->setData($formData);
        }

        Mage::register("blog_data", $post);

        return $this->_initAction()->renderLayout();
    }

    /**
     * Called whenever a new or existing post is saved.
     */
    public function saveAction()
    {
        $request = $this->getRequest();
        $adminhtmlSession = $this->_getSession();

        if (!$request->isPost()) {
            $adminhtmlSession->addError($this->__("Unable to find post to save."));
            $this->_redirectWithBlogFilterApplied();
            return;
        }

        $helper = $this->getHelper();
        $postData = $request->getPost();
        $postId = $request->getParam("id");

        /** @var $post Fontis_Blog_Model_Post */
        $post = Mage::getModel("blog/post");
        if ($postId) {
            $post->load($postId);
            $newPost = false;
        } else {
            $newPost = true;
        }
        $imageData = null;
        if (!empty($postData[Fontis_Blog_Model_Post::POST_IMAGE_FIELDNAME])) {
            $imageData = $postData[Fontis_Blog_Model_Post::POST_IMAGE_FIELDNAME];
        }
        unset($postData[Fontis_Blog_Model_Post::POST_IMAGE_FIELDNAME]);
        $smallImageData = null;
        if (!empty($postData[Fontis_Blog_Model_Post::POST_SMALLIMAGE_FIELDNAME])) {
            $smallImageData = $postData[Fontis_Blog_Model_Post::POST_SMALLIMAGE_FIELDNAME];
        }
        unset($postData[Fontis_Blog_Model_Post::POST_SMALLIMAGE_FIELDNAME]);
        $post->addData($postData)->setId($postId);

        try {
            $nowTime = now();
            if ($request->getParam("created_time") == null) {
                $post->setCreatedTime($nowTime)->setUpdateTime($nowTime);
            } else {
                $post->setUpdateTime($nowTime);
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $adminhtmlSession->addError($e->getMessage());
            $adminhtmlSession->setFormData($postData);
            $this->_redirect("*/*/edit", array("id" => $postId));
            return;
        }

        // Check for uploaded images.
        /** @var $imageHelper Fontis_Blog_Helper_Image */
        $imageHelper = Mage::helper("blog/image");
        try {
            if (!empty($imageData["delete"]) && $imageData["delete"] == 1) {
                $post->setData(Fontis_Blog_Model_Post::POST_IMAGE_FIELDNAME, "");
            } else {
                $imageHelper->processUploadPost($post, Fontis_Blog_Model_Post::POST_IMAGE_FIELDNAME);
            }
            if (!empty($smallImageData["delete"]) && $smallImageData["delete"] == 1) {
                $post->setData(Fontis_Blog_Model_Post::POST_SMALLIMAGE_FIELDNAME, "");
            } else {
                $imageHelper->processUploadPost($post, Fontis_Blog_Model_Post::POST_SMALLIMAGE_FIELDNAME);
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $adminhtmlSession->addWarning($this->__("Image uploading for post %s failed.", $postId));
        }

        try {
            /** @var $adminSessionUser Mage_Admin_Model_User */
            $adminSessionUser = Mage::getSingleton("admin/session")->getUser();
            $post->setUpdateUser($adminSessionUser->getName());

            $post->save();
            $adminhtmlSession->addSuccess($helper->__("Post was saved successfully."));
            $adminhtmlSession->setFormData(false);

            // If this is a new post, we need to ensure it shows up immediately on the frontend.
            $newStatus = $post->getStatus();
            if ($newPost && $newStatus == Fontis_Blog_Model_Status::STATUS_ENABLED) {
                $helper->enablePost($post);
            } else if (!$newPost) {
                $oldStatus = $post->getOrigData("status");
                if ($oldStatus == Fontis_Blog_Model_Status::STATUS_ENABLED && ($newStatus == Fontis_Blog_Model_Status::STATUS_DISABLED || $newStatus == Fontis_Blog_Model_Status::STATUS_HIDDEN)) {
                    // If this is an existing post that has been hidden, we need to ensure it disappears off the frontend immediately.
                    $helper->disablePost();
                } else if (($oldStatus == Fontis_Blog_Model_Status::STATUS_DISABLED || $oldStatus == Fontis_Blog_Model_Status::STATUS_HIDDEN) && $newStatus == Fontis_Blog_Model_Status::STATUS_ENABLED) {
                    $helper->enablePost($post);
                }
            }

            if ($request->getParam("back")) {
                $this->_redirect("*/*/edit", array("id" => $post->getId()));
            } else {
                $this->_redirectWithBlogFilterApplied();
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $adminhtmlSession->addError($e->getMessage());
            $adminhtmlSession->setFormData($postData);
            $this->_redirect("*/*/edit", array("id" => $postId));
        }
    }

    public function deleteAction()
    {
        $postId = $this->getRequest()->getParam("id");
        if ($postId > 0) {
            try {
                Mage::getModel("blog/post")->load($postId)->delete();
                Mage::helper("blog")->disablePost();

                $this->_getSession()->addSuccess($this->__("Post was successfully deleted."));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect("*/*/edit", array("id" => $postId));
                return;
            }
        }
        $this->_redirectWithBlogFilterApplied();
    }

    public function massDeleteAction()
    {
        $postIds = $this->getRequest()->getPost("post_id");
        if (!is_array($postIds)) {
            $this->getSession()->addError($this->__("Please select post(s)."));
            $this->_redirectWithBlogFilterApplied();
            return;
        }

        try {
            foreach ($postIds as $postId) {
                Mage::getModel("blog/post")->load($postId)->delete();
            }
            Mage::helper("blog")->disablePost();

            $this->_getSession()->addSuccess($this->__("Total of %d post(s) were successfully deleted", count($postIds)));
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_redirectWithBlogFilterApplied();
    }

    public function massStatusAction()
    {
        $request = $this->getRequest();
        $postIds = $request->getPost("post_id");
        if (!is_array($postIds)) {
            $this->_getSession()->addError($this->__("Please select post(s)."));
            $this->_redirectWithBlogFilterApplied();
            return;
        }

        /** @var $helper Fontis_Blog_Helper_Data */
        $helper = Mage::helper("blog");
        $disabledPosts = false;

        try {
            $newStatus = $request->getParam("status");
            foreach ($postIds as $postId) {
                /** @var $post Fontis_Blog_Model_Post */
                $post = Mage::getModel("blog/post")->load($postId);
                $oldStatus = $post->getStatus();
                $post->setStatus($newStatus)
                    ->setIsMassupdate(true)
                    ->save();

                if ($oldStatus == Fontis_Blog_Model_Status::STATUS_ENABLED && ($newStatus == Fontis_Blog_Model_Status::STATUS_DISABLED || $newStatus == Fontis_Blog_Model_Status::STATUS_HIDDEN)) {
                    $disabledPosts = true;
                } else if (($oldStatus == Fontis_Blog_Model_Status::STATUS_DISABLED || $oldStatus == Fontis_Blog_Model_Status::STATUS_HIDDEN) && $newStatus == Fontis_Blog_Model_Status::STATUS_ENABLED) {
                    $helper->enablePost($post);
                }
            }

            if ($disabledPosts === true) {
                $helper->disablePost();
            }

            $this->_getSession()->addSuccess($this->__("Total of %d record(s) were successfully updated.", count($postIds)));
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_redirectWithBlogFilterApplied();
    }
}
