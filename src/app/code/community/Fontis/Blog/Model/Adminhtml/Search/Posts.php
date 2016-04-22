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

/**
 * @method Fontis_Blog_Model_Adminhtml_Search_Posts setResults(array $results)
 */
class Fontis_Blog_Model_Adminhtml_Search_Posts extends Varien_Object
{
    /**
     * @return Fontis_Blog_Model_Adminhtml_Search_Posts
     */
    public function load()
    {
        $results = array();

        if (!$this->hasStart() || !$this->hasLimit() || !$this->hasQuery()) {
            $this->setResults($results);
            return $this;
        }
        $query = $this->getQuery();

        /** @var $postCollection Fontis_Blog_Model_Post[]|Fontis_Blog_Model_Mysql4_Post_Collection */
        $postCollection = Mage::getModel("blog/post")->getCollection();
        $postCollection->addFieldToFilter(array("title", "post_content"), array(
            array("like" => "%$query%"),
            array("like" => "%$query%"),
        ));
        $postCollection->setCurPage($this->getStart())
            ->setPageSize($this->getLimit());

        /** @var $helper Fontis_Blog_Helper_Data */
        $helper = Mage::helper("blog");
        /** @var $adminHelper Mage_Adminhtml_Helper_Data */
        $adminHelper = Mage::helper("adminhtml");
        $type = $helper->__("Blog Posts");
        foreach ($postCollection as $post) {
            $results[] = array(
                "id"            => "blog/post/" . $post->getId(),
                "type"          => $type,
                "name"          => $post->getTitle(),
                "description"   => "",
                "url"           => $adminHelper->getUrl("*/blog_post/edit", array("id" => $post->getId())),
            );
        }

        $this->setResults($results);

        return $this;
    }
}
