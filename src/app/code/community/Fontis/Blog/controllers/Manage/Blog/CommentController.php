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

class Fontis_Blog_Manage_Blog_CommentController extends Fontis_Blog_Controller_Abstract_Adminhtml
{
    /**
     * @var string
     */
    protected $_aclPath = "comment";

    /**
     * @var string
     */
    protected $_menuPath = "comment";

    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var array
     */
    protected $_publicActions = array(
        "edit",
    );

    public function deleteAction()
    {
        $commentId = $this->getRequest()->getParam("id");
        if (!$commentId) {
            $this->_getSession()->addError($this->__("Unable to find comment to delete."));
            $this->_redirectWithBlogFilterApplied();
            return;
        }

        try {
            $comment = Mage::getModel("blog/comment");
            $comment->setId($commentId)->delete();

            $this->_getSession()->addSuccess($this->__("Comment was successfully deleted."));
            $this->_redirectWithBlogFilterApplied();
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect("*/*/edit", array("id" => $commentId));
        }
    }

    public function approveAction()
    {
        $commentId = $this->getRequest()->getParam("id");
        if (!$commentId) {
            $this->_getSession()->addError($this->__("Unable to find comment to modify."));
            $this->_redirectWithBlogFilterApplied();
            return;
        }

        $result = $this->_changeSingleCommentStatus($commentId, Fontis_Blog_Model_Comment::COMMENT_APPROVED);
        if ($result === null) {
            $this->_getSession()->addError($this->__("Unable to change status of comment."));
        } else {
            if ($result === true) {
                $this->_getSession()->addSuccess($this->__("Comment was approved."));
            } else {
                $this->_getSession()->addNotice($this->__("Comment was already approved."));
            }
        }
        $this->_redirectWithBlogFilterApplied();
    }

    /**
     * @return Fontis_Blog_Manage_Blog_CommentController
     */
    public function unapproveAction()
    {
        $commentId = $this->getRequest()->getParam("id");
        if (!$commentId) {
            $this->_getSession()->addError($this->__("Unable to find comment to modify."));
            $this->_redirectWithBlogFilterApplied();
            return;
        }

        $result = $this->_changeSingleCommentStatus($commentId, Fontis_Blog_Model_Comment::COMMENT_UNAPPROVED);
        if ($result === null) {
            $this->_getSession()->addError($this->__("Unable to change status of comment."));
        } else {
            if ($result === true) {
                $this->_getSession()->addSuccess($this->__("Comment was unapproved."));
            } else {
                $this->_getSession()->addNotice($this->__("Comment was already unapproved."));
            }
        }
        $this->_redirectWithBlogFilterApplied();
    }

    /**
     * @param int $commentId
     * @param int $status
     * @return bool|null Returns true if the status was changed, false if the status was already the same
     *      status, or null if the status could not be changed.
     */
    protected function _changeSingleCommentStatus($commentId, $status)
    {
        try {
            /** @var $comment Fontis_Blog_Model_Comment */
            $comment = Mage::getModel("blog/comment")->load($commentId);
            if ($comment->getId()) {
                if ($comment->getStatus() == $status) {
                    return false;
                } else {
                    $comment->setOrigData()
                        ->setStatus($status)
                        ->save();
                    Mage::dispatchEvent("fontis_blog_comment_status_changed", array("comment" => $comment));
                    return true;
                }
            } else {
                return null;
            }
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            return null;
        }
    }

    public function massDeleteAction()
    {
        $commentIds = $this->getRequest()->getParam("comment_id");
        if (!is_array($commentIds)) {
            $this->_getSession()->addError($this->__("Please select comment(s)."));
            $this->_redirectWithBlogFilterApplied();
            return;
        }

        try {
            foreach ($commentIds as $commentId) {
                $comment = Mage::getModel("blog/comment")->load($commentId);
                $comment->delete();
            }
            $this->_getSession()->addSuccess($this->__("Total of %d comments(s) were successfully deleted.", count($commentIds)));
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_redirectWithBlogFilterApplied();
    }

    public function massApproveAction()
    {
        $commentIds = $this->getRequest()->getParam("comment_id");
        if (is_array($commentIds)) {
            $this->_changeMassCommentStatus($commentIds, Fontis_Blog_Model_Comment::COMMENT_APPROVED);
        } else {
            $this->_getSession()->addError($this->__("Please select comment(s)."));
        }
        $this->_redirectWithBlogFilterApplied();
    }

    public function massUnapproveAction()
    {
        $commentIds = $this->getRequest()->getParam("comment_id");
        if (is_array($commentIds)) {
            $this->_changeMassCommentStatus($commentIds, Fontis_Blog_Model_Comment::COMMENT_UNAPPROVED);
        } else {
            $this->_getSession()->addError($this->__("Please select comment(s)."));
        }
        $this->_redirectWithBlogFilterApplied();
    }

    /**
     * @param array $commentIds
     * @param int $status
     */
    protected function _changeMassCommentStatus($commentIds, $status)
    {
        try {
            foreach ($commentIds as $commentId) {
                $comment = Mage::getSingleton("blog/comment")
                    ->load($commentId)
                    ->setStatus($status)
                    ->setIsMassupdate(true)
                    ->save();
            }
            if ($status == Fontis_Blog_Model_Comment::COMMENT_APPROVED) {
                $this->_getSession()->addSuccess(
                    $this->__("Total of %d comment(s) were successfully approved.", count($commentIds))
                );
            } else {
                $this->_getSession()->addSuccess(
                    $this->__("Total of %d comment(s) were successfully unapproved.", count($commentIds))
                );
            }
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
    }

    public function editAction()
    {
        $commentId = $this->getRequest()->getParam("id");
        $comment = Mage::getModel("blog/comment")->load($commentId);

        if (!$comment->getId()) {
            $this->_getSession()->addError(Mage::helper("blog")->__("Comment does not exist."));
            $this->_redirectWithBlogFilterApplied();
            return;
        }

        $data = $this->_getSession()->getFormData(true);
        if (!empty($data)) {
            $comment->setData($data);
        }

        Mage::register("current_blog_comment", $comment);
        $this->_initAction()->renderLayout();
    }

    public function saveAction()
    {
        $request = $this->getRequest();
        $adminhtmlSession = $this->_getSession();

        if (!$request->isPost()) {
            $adminhtmlSession->addError(Mage::helper("blog")->__("Unable to find comment to save."));
            $this->_redirectWithBlogFilterApplied();
            return;
        }

        $postData = $request->getPost();
        $commentId = $request->getParam("id");

        /** @var $comment Fontis_Blog_Model_Comment */
        $comment = Mage::getModel("blog/comment");
        $comment->setData($postData)->setId($commentId);

        try {
            if ($comment->getData("created_time") == null || $comment->getData("update_time") == null) {
                $comment->setCreatedTime(now())->setUpdateTime(now());
            } else {
                $comment->setUpdateTime(now());
            }

            $comment->save();

            $adminhtmlSession->addSuccess(Mage::helper("blog")->__("Comment was successfully saved."));
            $adminhtmlSession->setFormData(false);

            if ($request->getParam("back")) {
                $this->_redirect("*/*/edit", array("id" => $comment->getId()));
            } else {
                $this->_redirectWithBlogFilterApplied();
            }
        } catch (Exception $e) {
            $adminhtmlSession->addError($e->getMessage());
            $adminhtmlSession->setFormData($postData);
            $this->_redirect("*/*/edit", array("id" => $commentId));
        }
    }
}
