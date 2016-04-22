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

class Fontis_Blog_Model_CustomGrid_Custom_Column_Post_Tags extends Fontis_Blog_Model_CustomGrid_Custom_Column_AbstractList
{
    /**
     * @var string
     */
    protected $_renderer = "blog/customGrid_grid_column_renderer_post_tags";

    /**
     * @param Varien_Data_Collection_Db $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $columnBlock
     * @return Fontis_Blog_Model_CustomGrid_Custom_Column_Post_Tags
     */
    public function addFilterToGridCollection(
        Varien_Data_Collection_Db $collection,
        Mage_Adminhtml_Block_Widget_Grid_Column $columnBlock
    ) {
        $tagIds = array_map("intval", array_filter(array_unique(explode(",", $columnBlock->getFilter()->getValue()))));
        $collection->addTagFilter($tagIds);
        return $this;
    }
}
