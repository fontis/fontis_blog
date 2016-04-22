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

class Fontis_Blog_Manage_Blog_CatController extends Fontis_Blog_Controller_Abstract_Adminhtml
{
    /**
     * @var string
     */
    protected $_aclPath = "cat";

    /**
     * @var string
     */
    protected $_menuPath = "cat";

    public function deleteAction()
    {
        $request = $this->getRequest();
        if ($catId = $request->getParam("id")) {
            try {
                Mage::getModel("blog/cat")->load($catId)->delete();

                Mage::helper("blog")->disablePost();

                $this->_getSession()->addSuccess($this->__("Category was successfully deleted."));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect("*/*/edit", array("id" => $request->getParam("id")));
                return;
            }
        } else {
            $this->_getSession()->addError($this->__("Unable to find category to delete."));
        }
        $this->_redirectWithBlogFilterApplied();
    }

    public function editAction()
    {
        $catId = $this->getRequest()->getParam("id");
        /** @var $cat Fontis_Blog_Model_Cat */
        $cat = Mage::getModel("blog/cat")->load($catId);

        if ($catId == 0 || $cat->getId()) {
            $this->_renderCatEditPage($cat);
        } else {
            $this->_getSession()->addError($this->__("Category does not exist."));
            $this->_redirectWithBlogFilterApplied();
        }
    }

    public function newAction()
    {
        $this->_renderCatEditPage(Mage::getModel("blog/cat"));
    }

    /**
     * @param Fontis_Blog_Model_Cat $cat
     * @return Fontis_Blog_Manage_Blog_CatController
     */
    protected function _renderCatEditPage(Fontis_Blog_Model_Cat $cat)
    {
        $formData = $this->_getSession()->getFormData(true);
        if (!empty($formData)) {
            $cat->setData($formData);
        }

        Mage::register("blog_data", $cat);

        $this->_initAction();

        $this->_addBreadcrumb($text = $this->__("Blog Manager"), $text);
        $this->_addBreadcrumb($text = $this->__("Category Manager"), $text);

        return $this->renderLayout();
    }

    public function saveAction()
    {
        $request = $this->getRequest();
        $adminhtmlSession = $this->_getSession();

        if (!$request->isPost()) {
            $adminhtmlSession->addError($this->__("Unable to find category to save."));
            $this->_redirectWithBlogFilterApplied();
            return;
        }

        $data = $request->getPost();
        $catId = $request->getParam("id");

        $cat = Mage::getModel("blog/cat");
        if ($catId) {
            $cat->load($catId);
            $newCat = false;
        } else {
            $newCat = true;
        }

        if (!empty($data[Fontis_Blog_Model_Cat::CAT_IMAGE_FIELDNAME])) {
            $imageData = $data[Fontis_Blog_Model_Cat::CAT_IMAGE_FIELDNAME];
        }
        unset($data[Fontis_Blog_Model_Cat::CAT_IMAGE_FIELDNAME]);

        $cat->setData($data)->setId($catId);

        /** @var $imageHelper Fontis_Blog_Helper_Image */
        $imageHelper = Mage::helper("blog/image");
        try {
            if (!empty($imageData["delete"]) && $imageData["delete"] == 1) {
                $cat->setData(Fontis_Blog_Model_Cat::CAT_IMAGE_FIELDNAME, "");
            } else {
                $imageHelper->processUploadCat($cat, Fontis_Blog_Model_Cat::CAT_IMAGE_FIELDNAME);
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $adminhtmlSession->addWarning($this->__("Image uploading for category %s failed.", $catId));
        }

        try {
            $cat->save();
            $adminhtmlSession->addSuccess($this->__("Category was successfully saved."));
            $adminhtmlSession->setFormData(false);

            if ($newCat) {
                // At the moment, the only place a new category would need to show up is in the sidebar.
                $this->getHelper()->clearFpcTags(Fontis_Blog_Block_Menu::CACHE_TAG);
            } else {
                // If the name or identifier of a category has changed, we need to redo all blog pages to ensure the change is reflected.
                if ($cat->getData("title") != $cat->getOrigData("title") || $cat->getData("identifier") != $cat->getOrigData("identifier")) {
                    $this->getHelper()->disablePost();
                }
            }

            if ($request->getParam("back")) {
                $this->_redirect("*/*/edit", array("id" => $cat->getId()));
            } else {
                $this->_redirectWithBlogFilterApplied();
            }
        } catch (Exception $e) {
            $adminhtmlSession->addError($e->getMessage());
            $adminhtmlSession->setFormData($data);
            $this->_redirect("*/*/edit", array("id" => $catId));
        }
    }

    public function massDeleteAction()
    {
        $catIds = $this->getRequest()->getParam("cat_id");
        if (is_array($catIds)) {
            try {
                foreach ($catIds as $catId) {
                    Mage::getModel("blog/cat")->load($catId)->delete();
                }
                Mage::helper("blog")->disablePost();

                $this->_getSession()->addSuccess($this->__("Total of %d categories were successfully deleted", count($catIds)));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        } else {
            $this->_getSession()->addError($this->__("Please select categories"));
        }
        $this->_redirectWithBlogFilterApplied();
    }
}
