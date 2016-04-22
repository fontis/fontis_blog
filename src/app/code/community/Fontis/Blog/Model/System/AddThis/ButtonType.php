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

class Fontis_Blog_Model_System_AddThis_ButtonType
{
    const TYPE_SHARING_RESPONSIVE   = "addthis_responsive_sharing";
    const TYPE_SHARING_JUMBO        = "addthis_jumbo_share";
    const TYPE_SHARING_CUSTOM       = "addthis_custom_sharing";
    const TYPE_SHARING_TOOLBOX      = "addthis_sharing_toolbox";
    const TYPE_SHARING_NATIVE       = "addthis_native_toolbox";

    const TYPE_FOLLOW_CUSTOM        = "addthis_custom_follow";
    const TYPE_FOLLOW_HORIZONTAL    = "addthis_horizontal_follow_toolbox";
    const TYPE_FOLLOW_VERTICAL      = "addthis_vertical_follow_toolbox";

    /**
     * @var array
     */
    protected $_types = array(
        self::TYPE_SHARING_RESPONSIVE   => "Responsive Sharing Buttons",
        self::TYPE_SHARING_JUMBO        => "Jumbo Share Counter",
        self::TYPE_SHARING_CUSTOM       => "Custom Sharing Buttons",
        self::TYPE_SHARING_TOOLBOX      => "Sharing Buttons",
        self::TYPE_SHARING_NATIVE       => "Original Sharing Buttons",

        self::TYPE_FOLLOW_CUSTOM        => "Custom Follow Buttons",
        self::TYPE_FOLLOW_HORIZONTAL    => "Horizontal Follow Buttons",
        self::TYPE_FOLLOW_VERTICAL      => "Vertical Follow Buttons",
    );

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $types = array(array("value" => "", "label" => "Disabled"));
        foreach ($this->_types as $type => $label) {
            $types[] = array(
                "value" => $type,
                "label" => $label,
            );
        }
        return $types;
    }
}
