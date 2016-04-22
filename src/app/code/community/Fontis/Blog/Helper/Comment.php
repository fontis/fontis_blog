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

class Fontis_Blog_Helper_Comment extends Mage_Core_Helper_Abstract
{
    /**
     * @param array $data
     * @return array
     */
    public function validateCommentData(array $data)
    {
        $return = array();
        if (isset($data["comment"]) && ($comment = trim($data["comment"]))) {
            $return["comment"] = $comment;
        }
        if (isset($data["user"]) && ($user = trim(strip_tags($data["user"])))) {
            $return["user"] = $user;
        }
        if (isset($data["email"]) && ($email = trim(strip_tags($data["email"])))) {
            $validator = new Zend_Validate_EmailAddress();
            if ($validator->isValid($email)) {
                $return["email"] = $email;
            }
        }
        if (isset($data["in_reply_to"]) && is_numeric($data["in_reply_to"])) {
            $inReplyToCheck = Mage::getResourceModel("blog/comment")->doesCommentExist($data["in_reply_to"]);
            if ($inReplyToCheck === true) {
                $return["in_reply_to"] = $data["in_reply_to"];
            } else {
                $return["in_reply_to"] = null;
            }
        } else {
            $return["in_reply_to"] = null;
        }
        return $return;
    }
}
