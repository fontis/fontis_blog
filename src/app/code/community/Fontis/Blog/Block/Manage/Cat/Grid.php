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

class Fontis_Blog_Block_Manage_Cat_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId("catGrid");
        $this->setDefaultSort("cat_id");
        $this->setDefaultDir("asc");
        $this->setSaveParametersInSession(true);
        $this->_emptyText = Mage::helper("blog")->__("No categories found.");
    }

    /**
     * @return int
     */
    protected function getBlogId()
    {
        return (int) $this->getRequest()->getParam("blog", 0);
    }

    /**
     * @return Fontis_Blog_Block_Manage_Cat_Grid
     */
    protected function _prepareCollection()
    {
        /** @var $collection Fontis_Blog_Model_Mysql4_Cat_Collection */
        $collection = Mage::getModel("blog/cat")->getCollection();
        if ($blog = $this->getBlogId()) {
            $collection->addBlogFilter($blog);
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return Fontis_Blog_Block_Manage_Cat_Grid
     */
    protected function _prepareColumns()
    {
        $blogHelper = Mage::helper("blog");

        $this->addColumn("cat_id", array(
            "header"    => $blogHelper->__("ID"),
            "align"     => "right",
            "width"     => "80px",
            "type"      => "number",
            "index"     => "cat_id",
        ));

        $this->addColumn("title", array(
            "header"    => $blogHelper->__("Title"),
            "align"     => "left",
            "index"     => "title",
        ));

        $this->addColumn("identifier", array(
            "header"    => $blogHelper->__("Identifier"),
            "align"     => "left",
            "index"     => "identifier",
        ));

        $this->addColumn("sort_order", array(
            "header"    => $blogHelper->__("Sort Order"),
            "align"     => "center",
            "width"     => "50px",
            "index"     => "sort_order",
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
                array(
                    "caption"   => $blogHelper->__("Delete"),
                    "url"       => array("base" => "*/*/delete"),
                    "field"     => "id",
                    "confirm"   => true,
                ),
            ),
            "filter"    => false,
            "sortable"  => false,
            "is_system" => true,
        ));

        return parent::_prepareColumns();
    }

    /**
     * @return Fontis_Blog_Block_Manage_Cat_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField("cat_id");
        $this->getMassactionBlock()->setFormFieldName("cat_id");

        $this->getMassactionBlock()->addItem("delete", array(
             "label"    => Mage::helper("blog")->__("Delete"),
             "url"      => $this->getUrl("*/*/massDelete"),
             "confirm"  => Mage::helper("blog")->__("Are you sure? This action cannot be undone."),
        ));

        return $this;
    }

    protected function _prepareMassactionColumn()
    {
        parent::_prepareMassactionColumn();
        $this->getColumn("massaction")->setData("width", "60px");
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
