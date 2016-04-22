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

use Fontis\MetadataBuilder\DataInterface as MetadataObjInterface;
use Fontis\MetadataBuilder\Renderer\OpenGraph as OpenGraphRenderer;

class Fontis_Blog_Helper_Provider_RichSnippets extends Mage_Core_Helper_Abstract
{
    const GRAPH_TYPE_ARTICLE = 'article';

    /**
     * Used for generating open graph metadata tags for blog home page
     *
     * @param string $index
     * @param Fontis\MetadataBuilder\DataInterface $data
     * @return Fontis\MetadataBuilder\DataInterface
     */
    public function getBlogMetadata($index, MetadataObjInterface $data)
    {
        /** @var Fontis_Blog_Model_Blog $blog */
        $blog = Mage::registry("current_blog_object");

        $data->setTitle($blog->getTitle());
        $data->setNamespaceData(OpenGraphRenderer::DATA_NAMESPACE, OpenGraphRenderer::PROPERTY_TYPE, self::GRAPH_TYPE_ARTICLE);
        $data->setImage($blog->getHeaderImageUrl());
        $data->setDescription($blog->getSetting("blog/description"));

        return $data;
    }

    /**
     * Used for generating open graph metadata tags for blog categories
     *
     * @param Fontis_Blog_Model_Cat $cat
     * @param Fontis\MetadataBuilder\DataInterface $data
     * @return Fontis\MetadataBuilder\DataInterface
     */
    public function getBlogCatMetadata(Fontis_Blog_Model_Cat $cat, MetadataObjInterface $data)
    {
        $data->setTitle($cat->getTitle());
        $data->setNamespaceData(OpenGraphRenderer::DATA_NAMESPACE, OpenGraphRenderer::PROPERTY_TYPE, self::GRAPH_TYPE_ARTICLE);
        $data->setImage($cat->getImageUrl());
        $data->setDescription($cat->getMetaDescription());

        return $data;
    }

    /**
     * Used for generating open graph metadata tags for blog entries
     *
     * @param Fontis_Blog_Model_Post $post
     * @param Fontis\MetadataBuilder\DataInterface $data
     * @return Fontis\MetadataBuilder\DataInterface
     */
    public function getBlogPostMetadata(Fontis_Blog_Model_Post $post, MetadataObjInterface $data)
    {
        $blog = $post->getBlog();

        $data->setTitle($post->getTitle());
        $data->setNamespaceData(OpenGraphRenderer::DATA_NAMESPACE, OpenGraphRenderer::PROPERTY_TYPE, self::GRAPH_TYPE_ARTICLE);
        $data->setDescription($post->getMetaDescription());

        switch ($blog->getSetting("opengraph/post_default_image")) {
            case Fontis_Blog_Model_Post::POST_SMALLIMAGE_FIELDNAME:
                $imageUrl = $post->getSmallImageUrl();
                if (!$imageUrl) {
                    $imageUrl = $post->getImageUrl();
                }
                break;
            case Fontis_Blog_Model_Post::POST_IMAGE_FIELDNAME:
            default:
                $imageUrl = $post->getImageUrl();
                break;
        }
        $data->setImage($imageUrl);

        return $data;
    }
}
