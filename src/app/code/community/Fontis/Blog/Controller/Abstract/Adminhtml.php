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

class Fontis_Blog_Controller_Abstract_Adminhtml extends Mage_Adminhtml_Controller_Action
{
    /**
     * @var Fontis_Blog_Helper_Data
     */
    protected $_helper = null;

    /**
     * @var string
     */
    protected $_aclPath;

    /**
     * @var string
     */
    protected $_menuPath;

    /**
     * @return Fontis_Blog_Helper_Data
     */
    protected function getHelper()
    {
        if ($this->_helper === null) {
            $this->_helper = Mage::helper("blog");
        }
        return $this->_helper;
    }

    /**
     * Check if admin has permissions to visit related pages
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        if (!$this->_aclPath) {
            return false;
        }
        return Mage::getSingleton("admin/session")->isAllowed("blog/" . $this->_aclPath);
    }

    /**
     * @return Fontis_Blog_Controller_Abstract_Adminhtml
     */
    protected function _initAction()
    {
        $this->loadLayout()->_setActiveMenu("blog/" . $this->_menuPath);
        return $this;
    }

    /**
     * @param string $pathSegment
     * @return Fontis_Blog_Controller_Abstract_Adminhtml
     */
    protected function _redirectWithBlogFilterApplied($pathSegment = "index")
    {
        $urlParams = array();
        if ($currentBlogId = $this->_getSession()->getCurrentBlogId()) {
            $urlParams["blog"] = $currentBlogId;
        }
        return $this->_redirect("*/*/$pathSegment", $urlParams);
    }

    public function indexAction()
    {
        // By setting this every time, it resets automatically when users
        // leave the blog interface and come back to it.
        $blogId = $this->getRequest()->getUserParam("blog");
        $this->_getSession()->setCurrentBlogId($blogId);

        $this->_initAction()->renderLayout();
    }
}
