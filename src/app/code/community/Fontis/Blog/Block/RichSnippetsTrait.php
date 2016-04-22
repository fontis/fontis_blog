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

trait Fontis_Blog_Block_RichSnippetsTrait
{
    /**
     * @var bool
     */
    private $_isRichSnippetsExtensionAvailable = null;

    /**
     * @param string $file
     * @param array $params
     * @return string
     */
    abstract public function getSkinUrl($file = null, array $params = array());

    /**
     * @return bool
     */
    private function isRichSnippetsExtensionAvailable()
    {
        if ($this->_isRichSnippetsExtensionAvailable === null) {
            $this->_isRichSnippetsExtensionAvailable = Mage::helper("blog")->isModuleEnabled("Fontis_RichSnippets");
        }
        return $this->_isRichSnippetsExtensionAvailable;
    }

    /**
     * @deprecated Use Rich Snippets function instead, as it is more generic.
     * @param Fontis_Blog_Model_Post $post
     * @return string
     */
    public function getMicrodataDatePublished(Fontis_Blog_Model_Post $post)
    {
        return $this->getRichSnippetsDatePublished($post);
    }

    /**
     * @param Fontis_Blog_Model_Post $post
     * @return string
     */
    public function getRichSnippetsDatePublished(Fontis_Blog_Model_Post $post)
    {
        return date("c", strtotime($post->getData("created_time")));
    }

    /**
     * @param Fontis_Blog_Model_Post $post
     * @return string
     */
    public function getRichSnippetsDateModified(Fontis_Blog_Model_Post $post)
    {
        return date("c", strtotime($post->getData("update_time")));
    }

    /**
     * @return string
     */
    public function getRichSnippetsPublisherName()
    {
        return Mage::app()->getStore()->getFrontendName();
    }

    /**
     * @return string
     */
    public function getRichSnippetsPublisherLogo()
    {
        if ($this->isRichSnippetsExtensionAvailable()) {
            return Mage::helper('fontis_richsnippets/config_organization')->getLogoUrl();
        } else {
            return $this->getSkinUrl(Mage::getStoreConfig("design/header/logo_src"));
        }
    }
}
