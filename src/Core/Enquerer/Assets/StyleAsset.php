<?php

namespace ModulesPress\Core\Enquerer\Assets;

/**
 * The `StyleAsset` class extends the `EnqueueableAsset` class to manage CSS style assets in WordPress.
 * It allows for the registration, enqueuing, inline styles, and media query support.
 */
class StyleAsset extends EnqueueableAsset
{
    // Default media query for the style asset ('all' by default).
    private string $media = 'all';

    /**
     * Constructor for StyleAsset.
     *
     * @param string $assetUrl The URL of the style asset.
     * @param string $version The version of the style.
     */
    public function __construct(
        private readonly string $assetUrl,
        protected string $version
    ) {
        parent::__construct($assetUrl, $version);
    }

    /**
     * Registers the style with WordPress.
     *
     * @param string|null $handle The handle for the style. If not provided, the asset's default handle is used.
     * @return StyleAsset The current instance for method chaining.
     */
    public function register(?string $handle = null): StyleAsset
    {
        if ($handle) $this->handle($handle);  // Set the handle if provided.
        
        // Register the style with WordPress.
        wp_register_style(
            $this->handle,  // Handle for the style.
            $this->getUrl(), // URL of the style.
            $this->dependencies, // Style dependencies.
            $this->version, // Version of the style.
            media: $this->media // Media query for the style.
        );

        return $this;
    }

    /**
     * Deregisters the style.
     *
     * @return StyleAsset The current instance for method chaining.
     */
    public function deregister(): StyleAsset
    {
        wp_deregister_style($this->handle);  // Deregister the style.
        return $this;
    }

    /**
     * Enqueues the style.
     *
     * @param string|null $handle The handle for the style. If not provided, the asset's default handle is used.
     * @return StyleAsset The current instance for method chaining.
     */
    public function enqueue(?string $handle = null): StyleAsset
    {
        if ($handle) $this->handle($handle);  // Set the handle if provided.
        
        // Register the style if it is not registered.
        if (!wp_style_is($this->handle, 'registered')) {
            $this->register();
        }
        
        // Enqueue the style.
        wp_enqueue_style($this->handle);
        return $this;
    }

    /**
     * Adds inline CSS to the style.
     *
     * @param string $style The inline CSS to add.
     * @return StyleAsset The current instance for method chaining.
     */
    public function inline(string $style): StyleAsset
    {
        wp_add_inline_style($this->handle, $style);  // Add the inline CSS.
        return $this;
    }

    /**
     * Sets the media query for the style.
     *
     * @param string $media The media query (e.g., 'all', 'screen', 'print').
     * @return StyleAsset The current instance for method chaining.
     */
    public function media(string $media): StyleAsset
    {
        $this->media = $media;  // Set the media query.
        return $this;
    }
}
