<?php

namespace ModulesPress\Core\Enquerer\Assets;

/**
 * The `EnqueueableAsset` class is an abstract class that adds functionality 
 * for handling enqueueable assets (e.g., scripts and styles).
 * It extends the base `Asset` class and adds properties like handle, version, and dependencies.
 */
abstract class EnqueueableAsset extends Asset
{
    // Handle for the asset (unique identifier in WordPress)
    protected string $handle;
    
    // List of dependencies for this asset (e.g., other scripts/styles this asset depends on)
    protected array $dependencies = [];

    /**
     * Constructor to initialize the enqueueable asset with URL and version.
     *
     * @param string $assetUrl The URL of the asset.
     * @param string $version The version of the asset.
     */
    public function __construct(
        private readonly string $assetUrl,
        protected string $version,
    ) {
        // Call the parent constructor to initialize the asset URL
        parent::__construct($assetUrl);
        
        // The handle is initially set to the asset URL
        $this->handle = $this->getUrl();
    }

    /**
     * Sets the handle for the asset.
     *
     * @param string $handle The handle for the asset (unique identifier in WordPress).
     * @return static The current instance for method chaining.
     */
    public function handle(string $handle): static
    {
        $this->handle = $handle;
        return $this;
    }

    /**
     * Sets the dependencies for the asset.
     *
     * @param array $dependencies An array of handles for assets that must be loaded before this one.
     * @return static The current instance for method chaining.
     */
    public function dependencies(array $dependencies): static
    {
        $this->dependencies = $dependencies;
        return $this;
    }

    /**
     * Sets the version for the asset.
     *
     * @param string $version The version of the asset.
     * @return static The current instance for method chaining.
     */
    public function version(string $version): static
    {
        $this->version = $version;
        return $this;
    }

    /**
     * Gets the handle for the asset.
     *
     * @return string The handle (unique identifier) of the asset.
     */
    public function getHandle(): string
    {
        return $this->handle;
    }

    /**
     * Gets the dependencies of the asset.
     *
     * @return array The dependencies (handles) of the asset.
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * Gets the version of the asset.
     *
     * @return string The version of the asset.
     */
    public function getVersion(): string
    {
        return $this->version;
    }
}
