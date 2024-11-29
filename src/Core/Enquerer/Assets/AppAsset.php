<?php

namespace ModulesPress\Core\Enquerer\Assets;

/**
 * Class AppAsset
 *
 * Represents an application asset consisting of a JavaScript file (typically for modern JavaScript frameworks like React)
 * along with associated stylesheets. It manages the script and its styles for efficient enqueuing and loading within WordPress.
 *
 */
class AppAsset extends ScriptAsset
{
    /**
     * AppAsset constructor.
     *
     * @param string $assetUrl The URL to the app's main JavaScript asset.
     * @param string $version The version of the asset for cache busting and version control.
     * @param StyleAsset[] $styleAssets An array of StyleAsset objects representing stylesheets related to the app.
     */
    public function __construct(
        private readonly string $assetUrl, // The main JavaScript file (e.g., React app JS)
        protected string $version,         // Version number (helps with cache busting)
        protected readonly array $styleAssets = [], // Stylesheets related to the app
    ) {
        parent::__construct($assetUrl, $version); // Inherit from ScriptAsset to handle the script asset
    }

    /**
     * Get the associated style assets.
     *
     * Returns the list of `StyleAsset` objects associated with this app asset.
     * These represent the styles that will be loaded alongside the JavaScript file.
     *
     * @return StyleAsset[] Array of StyleAsset objects.
     */
    public function getStyles(): array
    {
        return $this->styleAssets;
    }

    /**
     * Enqueue the styles associated with this app asset.
     *
     * This method enqueues all the stylesheets associated with the app asset.
     * It ensures that the required CSS files are loaded before the JavaScript file.
     *
     * @return $this Returns the current AppAsset instance for method chaining.
     */
    public function withStyles(): static
    {
        foreach ($this->styleAssets as $styleAsset) {
            $styleAsset->enqueue(); // Enqueue each style asset
        }
        return $this; // Return the current instance for chaining
    }
}
