<?php

namespace ModulesPress\Core\Enquerer\Assets;

/**
 * The `ScriptAsset` class is an extension of the `EnqueueableAsset` class for handling JavaScript assets.
 * It allows for the registration, enqueuing, and customization of script assets.
 */
class ScriptAsset extends EnqueueableAsset
{
    // Determines whether the script should be loaded in the footer (default is true)
    protected bool $inFooter = true;
    
    // Whether the script should be treated as a module (default is true)
    protected bool $isModule = true;

    /**
     * Constructor for ScriptAsset.
     *
     * @param string $assetUrl The URL of the script asset.
     * @param string $version The version of the script.
     */
    public function __construct(
        private readonly string $assetUrl,
        protected string $version
    ) {
        parent::__construct($assetUrl, $version);
    }

    /**
     * Adds the `type="module"` attribute to the script tag if it is a module.
     *
     * @param string $tag The HTML script tag.
     * @param string $handle The script handle.
     * @param string $src The source URL of the script.
     * @return string The modified script tag.
     */
    public function _AddScriptModuleTag(string $tag, string $handle, string $src): string
    {
        // Check if the handle matches and the script tag doesn't already contain the module type.
        if ($handle === $this->handle && false === strpos($tag, '<script type="module')) {
            $tag = str_replace('<script ', '<script type="module" ', $tag);
        }
        return $tag;
    }

    /**
     * Registers the script with WordPress.
     *
     * @param string|null $handle The handle for the script. If not provided, the asset's default handle is used.
     * @return static The current instance for method chaining.
     */
    public function register(?string $handle = null): static
    {
        if ($handle) $this->handle($handle);  // Set the handle if provided.
        
        // Register the script with WordPress.
        wp_register_script(
            $this->handle, // Handle of the script.
            $this->getUrl(), // URL of the script.
            $this->dependencies, // Script dependencies.
            $this->version, // Version of the script.
            args: [
                'in_footer' => $this->inFooter // Load the script in the footer if true.
            ]
        );
        
        // If the script is a module, add a filter to modify the script tag.
        if ($this->isModule) {
            add_filter('script_loader_tag', array($this, '_AddScriptModuleTag'), 10, 3);
        }

        return $this;
    }

    /**
     * Deregisters the script.
     *
     * @return static The current instance for method chaining.
     */
    public function deregister(): static
    {
        wp_deregister_script($this->handle);  // Deregister the script.
        return $this;
    }

    /**
     * Enqueues the script.
     *
     * @param string|null $handle The handle for the script. If not provided, the asset's default handle is used.
     * @return static The current instance for method chaining.
     */
    public function enqueue(?string $handle = null): static
    {
        if ($handle) $this->handle($handle);  // Set the handle if provided.
        
        // Register the script if it is not registered.
        if (!wp_script_is($this->handle, 'registered')) {
            $this->register();
        }
        
        // Enqueue the script.
        wp_enqueue_script($this->handle);
        return $this;
    }

    /**
     * Adds inline JavaScript to the script.
     *
     * @param string $script The inline JavaScript code.
     * @param string $position The position where the script should be added ('before' or 'after').
     * @return static The current instance for method chaining.
     */
    public function inline(string $script, $position = 'before'): static
    {
        wp_add_inline_script($this->handle, $script, $position);
        return $this;
    }

    /**
     * Localizes a script with data.
     *
     * @param string $objectName The name of the JavaScript object to localize.
     * @param array $data The data to localize.
     * @return static The current instance for method chaining.
     */
    public function localize(string $objectName, array $data): static
    {
        wp_localize_script($this->handle, $objectName, $data);
        return $this;
    }

    /**
     * Sets whether the script should be loaded in the footer.
     *
     * @param bool $inFooter Whether the script should be loaded in the footer.
     * @return static The current instance for method chaining.
     */
    public function inFooter(bool $inFooter): static
    {
        $this->inFooter = $inFooter;
        return $this;
    }

    /**
     * Sets whether the script is a module.
     *
     * @param bool $isModule Whether the script is a module.
     * @return static The current instance for method chaining.
     */
    public function module(bool $isModule = true): static
    {
        $this->isModule = $isModule;
        return $this;
    }
}
