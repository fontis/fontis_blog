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

class Fontis_Blog_Model_System_SearchAttributes
{
    const ID_SUFFIX = '_id';

    protected $_excludedAttributes = array(
        'status',
        'comments',
        'update_user',
    );

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = array();
        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getModel('core/resource');
        $attributes = $resource->getConnection(Mage_Core_Model_Resource::DEFAULT_READ_RESOURCE)
            ->describeTable($resource->getTableName('blog/post'));

        $length = strlen(self::ID_SUFFIX);
        foreach ($attributes as $attribute) {
            $columnName = $attribute['COLUMN_NAME'];
            if (substr($columnName, -$length) !== self::ID_SUFFIX && !in_array($columnName, $this->_excludedAttributes)) {
                $optionArray[] = array('value' => $columnName, 'label' => $columnName);
            }
        }
        return $optionArray;
    }
}
