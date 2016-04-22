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

class Fontis_Blog_Block_Author_List extends Mage_Core_Block_Template
{
    /**
     * @return Fontis_Blog_Model_Author[]|Fontis_Blog_Model_Mysql4_Author_Collection
     */
    public function getAuthors()
    {
        /** @var $authorCollection Fontis_Blog_Model_Author[]|Fontis_Blog_Model_Mysql4_Author_Collection */
        $authorCollection = Mage::getModel("blog/author")->getCollection();
        $authorCollection->addBlogFilter($this->getBlog(), true)
            ->setOrder("main_table.name", Zend_Db_Select::SQL_ASC);

        return $authorCollection;
    }

    /**
     * @return Fontis_Blog_Model_Blog
     */
    public function getBlog()
    {
        return Mage::registry("current_blog_object");
    }
}
