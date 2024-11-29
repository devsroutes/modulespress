<?php

namespace ModulesPress\Core\Vite;

/**
 * Vite Class
 * 
 * This class integrates the Vite development server with WordPress, enabling
 * hot module replacement (HMR) and efficient asset management during development.
 */
final class Vite
{
    /**
     * URL to the Vite client script.
     * 
     * @var string
     */
    private readonly string $clientURL;

    /**
     * Indicates whether the Vite development server is active.
     * 
     * @var bool
     */
    private readonly bool $active;

    /**
     * Constructor
     * 
     * Initializes the Vite integration with the provided host URL and environment mode.
     * 
     * @param string $hostURL The base URL of the Vite development server.
     * @param bool $isDevelopment Whether the plugin is running in development mode.
     */
    public function __construct(
        private readonly string $hostURL,
        private readonly bool $isDevelopment
    ) {
        // Set the URL for the Vite client script
        $this->clientURL = $this->hostURL . "/@vite/client";

        // Determine if the Vite development server is active
        $this->active = $isDevelopment && $this->isViteDevelopmentServerActive();
    }

    /**
     * Get the Vite server host URL.
     * 
     * @return string The host URL.
     */
    public function getHostURL(): string
    {
        return $this->hostURL;
    }

    /**
     * Registers required WordPress hooks for Vite integration.
     * 
     * If the Vite server is active, it adds hooks to inject Vite client scripts
     * into the front-end and admin pages.
     * 
     * @return Vite The current instance for method chaining.
     */
    public function registerRequiredHooks(): Vite
    {
        if ($this->active) {
            // Add Vite client scripts to front-end and admin headers
            add_action('wp_head', [$this, 'injectViteClient'], 1);
            add_action('admin_head', [$this, 'injectViteClient'], 1);

            // Optional: Transform script tags to module type
            // add_filter('script_loader_tag', [$this, 'transformScriptToModule'], 1, 3);
        }
        return $this;
    }

    /**
     * Injects Vite client scripts into the page header.
     * 
     * This adds the Vite client script and React Refresh runtime script (if React is used).
     */
    public function injectViteClient(): void
    {
        echo $this->getReactRefreshScript(); // Add React refresh script for HMR
        echo $this->getClientScript();      // Add Vite client script
    }

    /**
     * Transforms script tags to use the "module" type.
     * 
     * This ensures compatibility with Vite when serving JavaScript modules.
     * 
     * @param string $tag The original script tag.
     * @param string $handle The script handle.
     * @param string $url The script URL.
     * 
     * @return string The modified script tag with type="module".
     */
    public function transformScriptToModule(string $tag, string $handle, string $url): string
    {
        if (false !== strpos($url, $this->hostURL)) {
            $tag = str_replace('<script ', '<script type="module" ', $tag);
        }
        return $tag;
    }

    /**
     * Checks if the Vite development server is active.
     * 
     * @return bool True if active, otherwise false.
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * Checks whether the Vite development server is reachable.
     * 
     * @return bool True if the server is active, otherwise false.
     */
    private function isViteDevelopmentServerActive(): bool
    {
        // For now, this always returns true. Uncomment the line below for real server checks.
        return true;

        // Uncomment this for actual server status detection:
        // return !is_wp_error(wp_remote_get($this->clientURL));
    }

    /**
     * Generates the React Refresh runtime script.
     * 
     * Required for React components to support Hot Module Replacement (HMR).
     * 
     * @return string The script tag for the React Refresh runtime.
     */
    private function getReactRefreshScript(): string
    {
        return '<script type="module">
                    import RefreshRuntime from "' . $this->hostURL . '/@react-refresh";
                    RefreshRuntime.injectIntoGlobalHook(window);
                    window.$RefreshReg$ = () => {};
                    window.$RefreshSig$ = () => (type) => type;
                    window.__vite_plugin_react_preamble_installed__ = true;
                </script>';
    }

    /**
     * Generates the Vite client script tag.
     * 
     * This script connects the browser to the Vite server for HMR.
     * 
     * @return string The script tag for the Vite client.
     */
    private function getClientScript(): string
    {
        return '<script type="module" src="' . $this->clientURL . '"></script>';
    }
}
