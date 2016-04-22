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

class Fontis_Blog_Block_Manage_Blog_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId("blogGrid");
        $this->setDefaultSort("id");
        $this->setDefaultDir("asc");
        $this->setSaveParametersInSession(true);
        $this->_emptyText = Mage::helper("blog")->__("No blogs found.");
    }

    /**
     * @return Mage_Core_Model_Store
     */
    protected function getStore()
    {
        $storeId = (int) $this->getRequest()->getParam("store", Mage_Core_Model_App::ADMIN_STORE_ID);
        return Mage::app()->getStore($storeId);
    }

    /**
     * @return Fontis_Blog_Block_Manage_Blog_Grid
     */
    protected function _prepareCollection()
    {
        /** @var $collection Fontis_Blog_Model_Mysql4_Blog_Collection */
        $collection = Mage::getModel("blog/blog")->getCollection();
        $store = $this->getStore();
        if ($store->getId()) {
            $collection->addStoreFilter($store);
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return Fontis_Blog_Block_Manage_Blog_Grid
     */
    protected function _prepareColumns()
    {
        $blogHelper = Mage::helper("blog");

        $this->addColumn("blog_id", array(
            "header"    => $blogHelper->__("ID"),
            "align"     => "right",
            "width"     => "80px",
            "type"      => "number",
            "index"     => "blog_id",
        ));

        $this->addColumn("title", array(
            "header"    => $blogHelper->__("Title"),
            "align"     => "left",
            "index"     => "title",
        ));

        $this->addColumn("route", array(
            "header"    => $blogHelper->__("Route"),
            "align"     => "left",
            "index"     => "route",
        ));

        $this->addColumn("status", array(
            "header"    => $blogHelper->__("Status"),
            "align"     => "left",
            "width"     => "80px",
            "index"     => "status",
            "type"      => "options",
            "options"   => Mage::getSingleton("blog/system_blogStatus")->getOptions(),
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
                )
            ),
            "filter"    => false,
            "sortable"  => false,
            "is_system" => true,
        ));

        return parent::_prepareColumns();
    }

    /**
     * @param object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl("*/*/edit", array("id" => $row->getId()));
    }
}
