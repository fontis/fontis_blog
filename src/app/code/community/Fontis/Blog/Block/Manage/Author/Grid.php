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

class Fontis_Blog_Block_Manage_Author_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId("authorGrid");
        $this->setDefaultSort("author_id");
        $this->setDefaultDir("asc");
        $this->setSaveParametersInSession(true);
        $this->_emptyText = Mage::helper("blog")->__("No authors found.");
    }

    /**
     * @return Fontis_Blog_Block_Manage_Author_Grid
     */
    protected function _prepareCollection()
    {
        /** @var $collection Fontis_Blog_Model_Mysql4_Author_Collection */
        $collection = Mage::getModel("blog/author")->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return Fontis_Blog_Block_Manage_Author_Grid
     */
    protected function _prepareColumns()
    {
        $blogHelper = Mage::helper("blog");

        $this->addColumn("author_id", array(
            "header"    => $blogHelper->__("ID"),
            "align"     => "right",
            "width"     => "80px",
            "type"      => "number",
            "index"     => "author_id",
        ));

        $this->addColumn("name", array(
            "header"    => $blogHelper->__("Name"),
            "align"     => "left",
            "index"     => "name",
        ));

        $this->addColumn("identifier", array(
            "header"    => $blogHelper->__("Identifier"),
            "align"     => "left",
            "index"     => "identifier",
        ));

        $this->addColumn("action", array(
            "header"    =>  $blogHelper->__("Action"),
            "width"     => "100px",
            "type"      => "action",
            "getter"    => "getId",
            "actions"   => array(
                array(
                    "caption"   => $blogHelper->__("Edit"),
                    "url"       => array("base" => "*/*/edit"),
                    "field"     => "id",
                ),
            ),
            "filter"    => false,
            "sortable"  => false,
            "is_system" => true,
        ));

        return parent::_prepareColumns();
    }

    /**
     * @param Varien_Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl("*/*/edit", array("id" => $row->getId()));
    }
}
