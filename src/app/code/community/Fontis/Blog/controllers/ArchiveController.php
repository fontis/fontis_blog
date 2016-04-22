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

class Fontis_Blog_ArchiveController extends Fontis_Blog_Controller_Abstract_Frontend
{
    public function listAction()
    {
        $this->renderPage(Mage::helper("blog")->__("Archives"), "blog-archive-list");
    }

    public function viewAction()
    {
        $dateParts = Mage::registry("current_blog_archive_date");

        $archiveType = Fontis_Blog_Model_System_ArchiveType::YEARLY;
        $year = $dateParts[0];
        if (isset($dateParts[1])) {
            $month = $dateParts[1];
            $archiveType = Fontis_Blog_Model_System_ArchiveType::MONTHLY;
        } else {
            $month = 1;
        }
        if (isset($dateParts[2])) {
            $day = $dateParts[2];
            $archiveType = Fontis_Blog_Model_System_ArchiveType::DAILY;
        } else {
            $day = 1;
        }

        if (!checkdate($month, $day, $year)) {
            $this->_forward("noroute");
            return;
        }

        Mage::register("current_blog_archive_type", $archiveType);

        $archiveLabel = $this->__("Archives");
        $dateString = Fontis_Blog_Model_System_ArchiveType::getTypeFormat($archiveType);
        $this->renderPage($archiveLabel . " - " . date($dateString, mktime(0, 0, 0, $month, $day, $year)), "blog-archive-view");
    }

    /**
     * @param string $pageTitle
     * @param array|string $layoutHandle
     * @return Fontis_Blog_ArchiveController
     */
    protected function renderPage($pageTitle, $layoutHandle)
    {
        $layout = $this->prepareLayout($layoutHandle);
        $blog = $this->getBlog();

        $blogTitle = $blog->getTitle();
        if ($head = $layout->getBlock("head")) {
            /** @var $head Mage_Page_Block_Html_Head */
            $head->setTitle($blogTitle . " - " . $pageTitle);
            // These settings currently don't exist.
            //$head->setKeywords(Mage::getStoreConfig("fontis_blog/blog/keywords"));
            //$head->setDescription(Mage::getStoreConfig("fontis_blog/blog/description"));
        }
        return $this->renderLayout();
    }

    /**
     * @param bool $linkifyBlogCrumb
     * @return array
     */
    protected function getBreadcrumbs($linkifyBlogCrumb = true)
    {
        $breadcrumbs = parent::getBreadcrumbs(true);
        $blog = $this->getBlog();
        /** @var $helper Fontis_Blog_Helper_Data */
        $helper = Mage::helper("blog");

        if ($dateParts = Mage::registry("current_blog_archive_date")) {
            $archiveLink = $blog->getBlogUrl($helper->getBlogArchiveRoute());
        } else {
            $archiveLink = null;
        }

        $breadcrumbs["archive"] = array(
            "label" => $helper->__("Archives"),
            "title" => $helper->__("Archives"),
            "link"  => $archiveLink,
        );

        if (isset($dateParts[0])) {
            $archiveLink = (isset($dateParts[1]) ? $archiveLink . "/" . $dateParts[0] : null);
            $breadcrumbs["year"] = array(
                "label" => $dateParts[0],
                "title" => $dateParts[0],
                "link"  => $archiveLink,
            );

            if (isset($dateParts[1])) {
                $archiveLink = (isset($dateParts[2]) ? $archiveLink . "/" . $dateParts[1] : null);
                $breadcrumbs["month"] = array(
                    "label" => $dateParts[1],
                    "title" => $dateParts[1],
                    "link"  => $archiveLink,
                );

                if (isset($dateParts[2])) {
                    $breadcrumbs["day"] = array(
                        "label" => $dateParts[2],
                        "title" => $dateParts[2],
                    );
                }
            }
        }

        return $breadcrumbs;
    }
}
