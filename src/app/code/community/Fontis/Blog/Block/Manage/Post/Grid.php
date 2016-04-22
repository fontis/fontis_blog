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

class Fontis_Blog_Block_Manage_Post_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId("postGrid");
        $this->setDefaultSort("created_time");
        $this->setDefaultDir("desc");
        $this->setSaveParametersInSession(true);
        $this->_emptyText = Mage::helper("blog")->__("No posts found.");
    }

    /**
     * @return int
     */
    public function getBlogId()
    {
        return (int) $this->getRequest()->getParam("blog", 0);
    }

    /**
     * @return Fontis_Blog_Block_Manage_Post_Grid
     */
    protected function _prepareCollection()
    {
        /** @var $collection Fontis_Blog_Model_Mysql4_Post_Collection */
        $collection = Mage::getModel("blog/post")->getCollection();
        if ($blog = $this->getBlogId()) {
            $collection->addBlogFilter($blog);
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return Fontis_Blog_Block_Manage_Post_Grid
     */
    protected function _prepareColumns()
    {
        /** @var $blogHelper Fontis_Blog_Helper_Data */
        $blogHelper = Mage::helper("blog");

        $this->addColumn("post_id", array(
            "header"    => $blogHelper->__("ID"),
            "align"     => "right",
            "width"     => "80px",
            "type"      => "number",
            "index"     => "post_id",
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

        $this->addColumn("author", array(
            "header"    => $blogHelper->__("Author"),
            "width"     => "140px",
            "type"      => "options",
            "options"   => Mage::getSingleton("blog/system_authors")->getOptionArray(),
            "index"     => "author_id",
            "getter"    => function(Fontis_Blog_Model_Post $row) {
                    return $row->getAuthor()->getName();
                },
            "filter_condition_callback" => function(Fontis_Blog_Model_Mysql4_Post_Collection $collection, Mage_Adminhtml_Block_Widget_Grid_Column $column) {
                    $value = $column->getFilter()->getValue();
                    if ($value) {
                        $collection->addAuthorFilter($value);
                    }
                },
        ));

        $this->addColumn("created_time", array(
            "header"    => $blogHelper->__("Created"),
            "align"     => "left",
            "width"     => "120px",
            "type"      => "date",
            "index"     => "created_time",
        ));

        $this->addColumn("update_time", array(
            "header"    => $blogHelper->__("Updated"),
            "align"     => "left",
            "width"     => "120px",
            "type"      => "date",
            "index"     => "update_time",
        ));

        $this->addColumn("status", array(
            "header"    => $blogHelper->__("Status"),
            "align"     => "left",
            "width"     => "80px",
            "index"     => "status",
            "type"      => "options",
            "options"   => Mage::getSingleton("blog/status")->getOptionArray(),
        ));

        $this->addColumn("action", array(
            "header"    => $blogHelper->__("Action"),
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
     * @return Fontis_Blog_Block_Manage_Post_Grid
     */
    protected function _prepareMassaction()
    {
        $blogHelper = Mage::helper("blog");
        $this->setMassactionIdField("post_id");
        $this->getMassactionBlock()->setFormFieldName("post_id");

        $this->getMassactionBlock()->addItem("delete", array(
             "label"    => $blogHelper->__("Delete"),
             "url"      => $this->getUrl("*/*/massDelete"),
             "confirm"  => $blogHelper->__("Are you sure? This action cannot be undone."),
        ));

        $statuses = Mage::getSingleton("blog/status")->getOptionArray();

        array_unshift($statuses, array("label" => "", "value" => ""));
        $this->getMassactionBlock()->addItem("status", array(
            "label" => $blogHelper->__("Change status"),
            "url"   => $this->getUrl("*/*/massStatus", array("_current" => true)),
            "additional" => array(
                "visibility" => array(
                     "name"     => "status",
                     "type"     => "select",
                     "class"    => "required-entry",
                     "label"    => $blogHelper->__("Status"),
                     "values"   => $statuses,
                )
            )
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
