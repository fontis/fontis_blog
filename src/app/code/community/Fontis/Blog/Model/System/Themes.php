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

class Fontis_Blog_Model_System_Themes
{
    /**
     * @var array
     */
    protected $_options = null;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->_options === null) {
            /**
             * The system config code behaves slightly differently depending on whether you pass a method in the system
             * config, or if you create a wrapper like we've done here.
             */
            $this->_options = Mage::getModel("core/design_source_design")->getAllOptions();
        }
        return $this->_options;
    }
}
