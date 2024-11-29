<?php

namespace ModulesPress\Core\Enquerer\Assets;

/**
 * Class StaticAsset
 *
 * Represents a static asset, such as images, fonts, or other uncompiled JavaScript/CSS files, in WordPress.
 *
 * @package ModulesPress\Core\Enquerer\Assets
 */
class StaticAsset extends Asset
{
    /**
     * StaticAsset constructor.
     *
     * @param string $assetUrl The URL to the static asset (e.g., an image or font file).
     */
    public function __construct(
        private readonly string $assetUrl // URL to the static asset
    ) {
        parent::__construct($assetUrl); // Call the parent constructor to set the asset URL
    }
}
