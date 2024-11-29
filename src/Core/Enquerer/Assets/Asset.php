<?php

namespace ModulesPress\Core\Enquerer\Assets;

/**
 * The `Asset` class represents a generic asset with a URL.
 * It is used as a base class for other specific asset types (like scripts, styles, etc.).
 */
class Asset
{
    /**
     * Constructor to initialize the asset with its URL.
     *
     * @param string $assetUrl The URL of the asset.
     */
    public function __construct(
        private readonly string $assetUrl
    ) {}

    /**
     * Gets the URL of the asset.
     *
     * @return string The URL of the asset.
     */
    public function getUrl(): string
    {
        return $this->assetUrl;
    }
}
