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

class Fontis_Blog_HomeController extends Fontis_Blog_Controller_Abstract_Frontend
{
    public function indexAction()
    {
        Mage::register("fontis_blog_index", "home");

        $layout = $this->prepareLayout("blog-home");

        if ($head = $layout->getBlock("head")) {
            /** @var $head Mage_Page_Block_Html_Head */
            $blog = $this->getBlog();
            $head->setTitle($blog->getTitle());
            if ($blogDescription = $blog->getSetting("blog/description")) {
                $head->setDescription($blogDescription);
            }
            // This setting currently doesn't exist because it is generally unused in modern web development.
            //$head->setKeywords(Mage::getStoreConfig("fontis_blog/blog/keywords"));
        }
        $this->renderLayout();
    }
}
