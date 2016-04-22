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

class Fontis_Blog_Helper_Data extends Mage_Core_Helper_Abstract
{
    const GLOBAL_CACHE_TAG          = "fontis_blog";

    const BLOG_MEDIA_TOPLEVEL       = "fontis_blog";
    const BLOG_MEDIA_MAIN           = "main";
    const BLOG_MEDIA_POSTS          = "posts";

    const BLOG_DEFAULT_ROUTE_ARCHIVE    = "archive";

    /**
     * @var bool
     */
    protected $_singleBlogMode = null;

    protected $_bmImagesRoute = null;

    protected $_fpcProcessor = false;

    /**
     * Do not include plurals in this list.
     * They should be accounted for in the checkForReservedWord() function.
     *
     * @var array
     */
    protected $_reservedKeywords = array(
        "post"      => true,
        "cat"       => true,
        "tag"       => true,
        "page"      => true,
        "rss"       => true,
        "archive"   => true,
        "comment"   => true,
        "status"    => true,
        "author"    => true,
    );

    /**
     * @return bool
     */
    public function isSingleBlogMode()
    {
        if ($this->_singleBlogMode === null) {
            $collection = Mage::getModel("blog/blog")->getCollection();
            if ($collection->getSize() < 2) {
                $this->_singleBlogMode = true;
            } else {
                $this->_singleBlogMode = false;
            }
        }
        return $this->_singleBlogMode;
    }

    /**
     * Check to see if a string is in the list of reserved words for the extension.
     *
     * @param string $word
     * @return bool
     */
    public function checkForReservedWord($word)
    {
        if (isset($this->_reservedKeywords[$word])) {
            return true;
        }
        // Account for plurals.
        if (isset($this->_reservedKeywords[rtrim($word, "s")])) {
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        /** @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::getSingleton("customer/session")->getCustomer();
        return trim($customer->getName());
    }

    /**
     * @return string
     */
    public function getUserEmail()
    {
        /** @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::getSingleton("customer/session")->getCustomer();
        return $customer->getEmail();
    }

    /**
     * @return string
     */
    public function getMediaUrl()
    {
        return Mage::getBaseUrl("media") . self::BLOG_MEDIA_TOPLEVEL . "/";
    }

    /**
     * @return string
     */
    public function getBlogArchiveRoute()
    {
        return self::BLOG_DEFAULT_ROUTE_ARCHIVE;
    }

    /**
     * Creates a slug from any string of text.
     *
     * @param string $str
     * @param string $delimiter
     * @return string
     */
    public function toAscii($str, $delimiter = "-")
    {
        // Convert all text to ASCII, assuming the input text is UTF-8
        $clean = iconv("UTF-8", 'ASCII//TRANSLIT', $str);
        // Strip all charaters that aren't letters, numbers, forward-slashes, underscores,
        // pipes, spaces, pluses, or dashes.
        $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", "", $clean);
        // Strip any leading or trailing dashes.
        $clean = strtolower(trim($clean, "-"));
        // Replace all forward-slashes, underscores, pipes, spaces, pluses and dashes with
        // the delimiter (which defaults to a dash).
        $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);
        return $clean;
    }

    /**
     * @return bool
     */
    public function useRecaptcha()
    {
        // Is the Fontis reCAPTCHA module enabled?
        if ($this->isModuleOutputEnabled("Fontis_Recaptcha") && Mage::helper("fontis_recaptcha")->isEnabled()) {
            // If the user is logged in, they might not need to fill in the reCAPTCHA form.
            if (!(Mage::getStoreConfig("fontis_recaptcha/recaptcha/when_loggedin") && (Mage::getSingleton("customer/session")->isLoggedIn()))) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $segment
     * @return int|null
     */
    public function verifyNumericRouteSegment($segment)
    {
        if (!is_numeric($segment)) {
            // This means the number isn't actually a number.
            return null;
        }

        $number = (int) $segment;
        if (strcmp($segment, $number) !== 0) {
            // This means the number was probably a decimal, or was padded with leading zeroes.
            return null;
        }

        return $number;
    }

    /**
     * @param array $dateParts
     * @param bool $strictLengthChecks
     * @return bool
     */
    public function verifyArchiveDateSegments(array $dateParts, $strictLengthChecks = true)
    {
        if (isset($dateParts["year"])) {
            // We've been passed an associate array. Convert it into what we're expecting.
            // We can't blindly use array_values() because we don't know what the actual order
            // of the elements in the array is.
            $newDateParts = array($dateParts["year"]);
            if (isset($dateParts["month"])) {
                $newDateParts[] = $dateParts["month"];
                if (isset($dateParts["day"])) {
                    $newDateParts[] = $dateParts["day"];
                }
            }
            $dateParts = $newDateParts;
        }

        // Start by verifying the year.
        $year = $dateParts[0];
        if ($strictLengthChecks === true && strlen($year) !== 4) {
            // Years must be specified in full.
            return false;
        }
        $yearNumber = $this->verifyNumericRouteSegment($year);
        if ($yearNumber === null) {
            return false;
        }

        // Next, verify the month, if it's been specified.
        if (!isset($dateParts[1])) {
            return true;
        }
        $month = $dateParts[1];
        if (($strictLengthChecks === true && strlen($month) !== 2) || !is_numeric($month)) {
            // Months must be specified with two digits (even Jan-Sep).
            return false;
        }
        $monthNumber = (int) $month;
        if ($monthNumber < 1 || $monthNumber > 12) {
            // Obviously not a valid month.
            return false;
        }

        // Finally, validate the day, if it's been specified.
        if (!isset($dateParts[2])) {
            return true;
        }
        $day = $dateParts[2];
        if (($strictLengthChecks === true && strlen($day) !== 2) || !is_numeric($day)) {
            // Days must also be specified with two digits (even 1st-9th).
            return false;
        }
        $dayNumber = (int) $day;
        if ($dayNumber < 1) {
            // Obviously not a valid day.
            return false;
        }

        return checkdate($monthNumber, $dayNumber, $yearNumber);
    }

    /**
     * @param array $dateParts
     * @return string
     */
    public function getArchiveUrlPath(array $dateParts)
    {
        if (!$this->verifyArchiveDateSegments($dateParts, false)) {
            return "";
        }

        $path = $this->getBlogArchiveRoute() . "/" . $dateParts["year"];
        if (isset($dateParts["month"])) {
            $path .= "/" . str_pad($dateParts["month"], 2, "0", STR_PAD_LEFT);
            if (isset($dateParts["day"])) {
                $path .= "/" . str_pad($dateParts["day"], 2, "0", STR_PAD_LEFT);
            }
        }

        return $path;
    }

    /**
     * @return object|null
     */
    public function getFpcProcessor()
    {
        if ($this->_fpcProcessor === false) {
            $fpcProcessor = Mage::getConfig()->getNode("global/cache/fpc_processor");
            if ($fpcProcessor) {
                $this->_fpcProcessor = Mage::getSingleton($fpcProcessor);
            } else {
                $this->_fpcProcessor = null;
            }
        }
        return $this->_fpcProcessor;
    }

    /**
     * @param array|string $tag
     */
    public function addTagToFpc($tag)
    {
        $fpcProcessor = $this->getFpcProcessor();
        if ($fpcProcessor && method_exists($fpcProcessor, "addRequestTag")) {
            $fpcProcessor->addRequestTag($tag);
        }
    }

    /**
     * @param array|string $tags
     */
    public function clearFpcTags($tags)
    {
        if (!is_array($tags)) {
            $tags = array($tags);
        }
        Mage::app()->cleanCache($tags);
    }

    /**
     * If a new post is created, or an existing post is enabled or unhidden, clear any FPC entries
     * for pages that this new post should show up on.
     *
     * @param Fontis_Blog_Model_Post $post
     */
    public function enablePost(Fontis_Blog_Model_Post $post)
    {
        $tags = array(
            Fontis_Blog_Block_Blog::CACHE_TAG,
            Fontis_Blog_Block_Rss::CACHE_TAG,
            Fontis_Blog_Block_Archive::CACHE_TAG,
        );
        foreach ($post->getCatIds() as $cat) {
            $tags[] = Fontis_Blog_Block_Cat::CACHE_TAG . "_" . $cat;
        }
        $this->clearFpcTags($tags);
    }

    /**
     * When a post is deleted, hidden or disabled, it needs to be removed from the frontend immediately.
     * All FPC entries with blog content should be cleared to ensure this happens.
     */
    public function disablePost()
    {
        $this->clearFpcTags(self::GLOBAL_CACHE_TAG);
    }

    /**
     * @param string $html
     * @return string
     */
    public function closeTags($html)
    {
        // Put all opened tags into an array
        preg_match_all("#<([a-z]+)( .*)?(?!/)>#iU", $html, $result);
        $openedTags = $result[1];

        // Put all closed tags into an array
        preg_match_all("#</([a-z]+)>#iU", $html, $result);
        $closedTags = $result[1];
        $lenOpened = count($openedTags);

        // All tags are closed
        if (count($closedTags) == $lenOpened) {
            return $html;
        }
        $openedTags = array_reverse($openedTags);

        // Close tags
        for ($i = 0; $i < $lenOpened; $i++) {
            if (!in_array($openedTags[$i], $closedTags)) {
                $html .= "</" . $openedTags[$i] . ">";
            } else {
                unset($closedTags[array_search($openedTags[$i], $closedTags)]);
            }
        }

        return $html;
    }
}
