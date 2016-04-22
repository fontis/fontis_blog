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

class Fontis_Blog_Model_System_Authors
{
    /**
     * @return Fontis_Blog_Model_Author[]|Fontis_Blog_Model_Mysql4_Author_Collection
     */
    protected function getAuthorCollection()
    {
        /** @var $collection Fontis_Blog_Model_Mysql4_Author_Collection */
        $collection = Mage::getResourceModel("blog/author_collection");
        $collection->addOrder("name", "asc");
        return $collection;
    }

    /**
     * @return array
     */
    public function getOptionArray()
    {
        $authorCollection = $this->getAuthorCollection();
        $options = array();
        foreach ($authorCollection as $author) {
            $options[$author->getAuthorId()] = $author->getName();
        }
        return $options;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $authorCollection = $this->getAuthorCollection();
        $options = array();
        foreach ($authorCollection as $author) {
            $options[] = array(
                "value" => $author->getAuthorId(),
                "label" => $author->getName(),
            );
        }
        return $options;
    }
}
