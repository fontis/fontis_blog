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

class Fontis_Blog_Block_Manage_Comment_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId("commentGrid");
        $this->setDefaultSort("status");
        $this->setDefaultDir("asc");
        $this->setSaveParametersInSession(true);
        $this->_emptyText = Mage::helper("blog")->__("No comments found.");
    }

    /**
     * @return int
     */
    protected function getBlogId()
    {
        return (int) $this->getRequest()->getParam("blog", 0);
    }

    /**
     * @return Fontis_Blog_Block_Manage_Comment_Grid
     */
    protected function _prepareCollection()
    {
        /** @var $collection Fontis_Blog_Model_Mysql4_Comment_Collection */
        $collection = Mage::getModel("blog/comment")->getCollection();
        if ($blog = $this->getBlogId()) {
            $collection->addBlogFilter($blog);
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $blogHelper = Mage::helper("blog");

        /*$this->addColumn("comment_id", array(
            "header"    => $blogHelper->__("ID"),
            "align"     => "right",
            "width"     => "50px",
            "index"     => "post_id",
        ));*/

        $this->addColumn("comment", array(
            "header"    => $blogHelper->__("Comment"),
            "align"     =>"left",
            "index"     => "comment",
        ));


        $this->addColumn("user", array(
            "header"    => $blogHelper->__("Poster"),
            "width"     => "160px",
            "index"     => "user",
        ));

        $this->addColumn("email", array(
            "header"    => $blogHelper->__("Email Address"),
            "width"     => "170px",
            "index"     => "email",
        ));

        $this->addColumn("created_time", array(
            "header"    => $blogHelper->__("Created"),
            "align"     => "center",
            "width"     => "120px",
            "type"      => "date",
            "index"     => "created_time",
        ));

        $this->addColumn("status", array(
            "header"    => $blogHelper->__("Status"),
            "align"     => "center",
            "width"     => "80px",
            "index"     => "status",
            "type"      => "options",
            "options"   => array(
                Fontis_Blog_Model_Comment::COMMENT_UNAPPROVED   => "Unapproved",
                Fontis_Blog_Model_Comment::COMMENT_APPROVED     => "Approved",
            ),
        ));

        $this->addColumn("action", array(
            "header"    =>  $blogHelper->__("Action"),
            "width"     => "100px",
            "type"      => "action",
            "getter"    => "getId",
            "actions"   => array(
                array(
                    "caption"   => $blogHelper->__("Approve"),
                    "url"       => array("base" => "*/*/approve"),
                    "field"     => "id",
                ),
                array(
                    "caption"   => $blogHelper->__("Unapprove"),
                    "url"       => array("base" => "*/*/unapprove"),
                    "field"     => "id",
                ),
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
     * @return Fontis_Blog_Block_Manage_Comment_Grid
     */
    protected function _prepareMassaction()
    {
        $blogHelper = Mage::helper("blog");
        $this->setMassactionIdField("comment_id");
        $this->getMassactionBlock()->setFormFieldName("comment_id");

        $areYouSure = $blogHelper->__("Are you sure?");

        $this->getMassactionBlock()->addItem("approve", array(
             "label"    => $blogHelper->__("Approve"),
             "url"      => $this->getUrl("*/*/massApprove"),
             "confirm"  => $areYouSure,
        ));

        $this->getMassactionBlock()->addItem("unapprove", array(
             "label"    => $blogHelper->__("Unapprove"),
             "url"      => $this->getUrl("*/*/massUnapprove"),
             "confirm"  => $areYouSure,
        ));

        $this->getMassactionBlock()->addItem("delete", array(
            "label"    => $blogHelper->__("Delete"),
            "url"      => $this->getUrl("*/*/massDelete"),
            "confirm"  => $areYouSure,
        ));

        return $this;
    }

    protected function _prepareMassactionColumn()
    {
        parent::_prepareMassactionColumn();
        $this->getColumn("massaction")->setData("width", "80px");
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
