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

class Fontis_Blog_Manage_Blog_BlogController extends Fontis_Blog_Controller_Abstract_Adminhtml
{
    /**
     * @var string
     */
    protected $_aclPath = "blogs";

    /**
     * @var string
     */
    protected $_menuPath = "blogs";

    /**
     * Overridden to not bother about saving the blog ID in the session, as it
     * doesn't apply to the blog index page.
     */
    public function indexAction()
    {
        $this->_initAction()->renderLayout();
    }

    public function editAction()
    {
        $blogId = $this->getRequest()->getParam("id");
        /** @var $blog Fontis_Blog_Model_Blog */
        $blog = Mage::getModel("blog/blog")->load($blogId);

        if ($blogId == 0 || $blog->getId()) {
            $this->_renderBlogEditPage($blog);
        } else {
            $this->_getSession()->addError($this->__("Blog does not exist."));
            $this->_redirect("*/*/");
        }
    }

    public function newAction()
    {
        $this->_renderBlogEditPage(Mage::getModel("blog/blog"));
    }

    /**
     * @param Fontis_Blog_Model_Blog $blog
     * @return Fontis_Blog_Manage_Blog_BlogController
     */
    protected function _renderBlogEditPage(Fontis_Blog_Model_Blog $blog)
    {
        $formData = $this->_getSession()->getFormData(true);
        if (!empty($formData)) {
            if (isset($formData["main"])) {
                $blog->setData($formData["main"]);
            }
            if (isset($formData["settings"])) {
                $blog->setSetting($formData["settings"]);
            }
        }

        Mage::register("blog_data", $blog);

        return $this->_initAction()->renderLayout();
    }

    /**
     * Called whenever a new or existing blog is saved.
     */
    public function saveAction()
    {
        $request = $this->getRequest();
        $adminhtmlSession = $this->_getSession();

        if (!$request->isPost()) {
            $adminhtmlSession->addError($this->__("Unable to find blog to save."));
            $this->_redirect("*/*/");
            return;
        }

        $postData = $request->getPost();
        $blogId = $request->getParam("id");

        /** @var $blog Fontis_Blog_Model_Blog */
        $blog = Mage::getModel("blog/blog");
        if ($blogId) {
            $blog->load($blogId);
        }
        $blog->setData($postData["main"])->setId($blogId);
        $blog->setSetting($postData["settings"]);

        try {
            $blog->save();
            $adminhtmlSession->addSuccess($this->__("Blog was saved successfully."));
            $adminhtmlSession->setFormData(false);
            if ($request->getParam("back")) {
                $this->_redirect("*/*/edit", array("id" => $blog->getId()));
            } else {
                $this->_redirect("*/*/");
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $adminhtmlSession->addError($e->getMessage());
            $adminhtmlSession->setFormData($postData);
            $this->_redirect("*/*/edit", array("id" => $blogId));
        }
    }
}
