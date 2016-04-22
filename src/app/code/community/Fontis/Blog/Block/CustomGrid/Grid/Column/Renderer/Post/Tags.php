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

class Fontis_Blog_Block_CustomGrid_Grid_Column_Renderer_Post_Tags extends Fontis_Blog_Block_CustomGrid_Grid_Column_Renderer_AbstractList
{
    /**
     * @param Varien_Object $row
     * @param string $resultSeparator
     * @return string
     */
    protected function _renderRow(Varien_Object $row, $resultSeparator)
    {
        if ($this->getColumn()->getDisplayIds()) {
            return implode($resultSeparator, $row->getTagIds());
        } else {
            /** @var $categories Fontis_Blog_Model_Tag[]|Fontis_Blog_Model_Mysql4_Tag_Collection */
            $categories = $row->getTags();
            $result = $categories->getColumnValues("name");
            return implode($resultSeparator, $result);
        }
    }
}
