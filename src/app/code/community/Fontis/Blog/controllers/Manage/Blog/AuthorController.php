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

class Fontis_Blog_Manage_Blog_AuthorController extends Fontis_Blog_Controller_Abstract_Adminhtml
{
    /**
     * @var string
     */
    protected $_aclPath = "authors";

    /**
     * @var string
     */
    protected $_menuPath = "authors";

    /**
     * Overridden to not bother about saving the blog ID in the session, as it
     * doesn't apply to the author index page.
     */
    public function indexAction()
    {
        $this->_initAction()->renderLayout();
    }

    public function editAction()
    {
        $authorId = $this->getRequest()->getParam("id");
        /** @var $author Fontis_Blog_Model_Author */
        $author = Mage::getModel("blog/author")->load($authorId);

        if ($authorId == 0 || $author->getId()) {
            $this->_renderAuthorEditPage($author);
        } else {
            $this->_getSession()->addError($this->__("Author does not exist."));
            $this->_redirect("*/*/");
        }
    }

    public function newAction()
    {
        $this->_renderAuthorEditPage(Mage::getModel("blog/author"));
    }

    /**
     * @param Fontis_Blog_Model_Author $author
     * @return Fontis_Blog_Manage_Blog_AuthorController
     */
    protected function _renderAuthorEditPage(Fontis_Blog_Model_Author $author)
    {
        $formData = $this->_getSession()->getFormData(true);
        if (!empty($formData)) {
            $author->setData($formData);
        }

        Mage::register("blog_data", $author);

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
            $adminhtmlSession->addError($this->__("Unable to find author to save."));
            $this->_redirect("*/*/");
            return;
        }

        $postData = $request->getPost();
        $authorId = $request->getParam("id");

        /** @var $author Fontis_Blog_Model_Author */
        $author = Mage::getModel("blog/author");
        if ($authorId) {
            $author->load($authorId);
        }
        $author->setData($postData)->setId($authorId);

        try {
            $author->save();
            $adminhtmlSession->addSuccess($this->__("Author was saved successfully."));
            $adminhtmlSession->setFormData(false);
            if ($request->getParam("back")) {
                $this->_redirect("*/*/edit", array("id" => $authorId));
            } else {
                $this->_redirect("*/*/");
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $adminhtmlSession->addError($e->getMessage());
            $adminhtmlSession->setFormData($postData);
            $this->_redirect("*/*/edit", array("id" => $authorId));
        }
    }
}
