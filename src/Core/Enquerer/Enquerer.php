<?php

namespace ModulesPress\Core\Enquerer;

use ModulesPress\Core\AssetsResolver\AssetsResolver;
use ModulesPress\Core\Core;
use ModulesPress\Core\Enquerer\Assets\AppAsset;
use ModulesPress\Core\Enquerer\Assets\Asset;
use ModulesPress\Core\Enquerer\Assets\ScriptAsset;
use ModulesPress\Core\Enquerer\Assets\StaticAsset;
use ModulesPress\Core\Enquerer\Assets\StyleAsset;
use ModulesPress\Foundation\ModulesPressPlugin;

/**
 * The `Enquerer` class handles the registration and management of static and dynamic assets in the system.
 * It simplifies asset URL resolution and enqueuing for styles, scripts, static files, and app resources.
 */
final class Enquerer
{
    /**
     * @param Core $core The core system instance.
     * @param AssetsResolver $assetsResolver The service that resolves asset URLs.
     * @param ModulesPressPlugin $plugin The plugin instance to get versioning info.
     */
    public function __construct(
        private readonly Core $core,
        private readonly AssetsResolver $assetsResolver,
        private readonly ModulesPressPlugin $plugin
    ) {}

    /**
     * Resolves a generic asset from the path, returning an Asset instance.
     *
     * @param string $path The asset path to resolve.
     * @return Asset The resolved asset.
     */
    public function resource(string $path): Asset
    {
        return new Asset($this->assetsResolver->getResourceAssetUrl($path));
    }

    /**
     * Resolves a static asset path and returns a StaticAsset instance.
     *
     * @param string $path The static asset path.
     * @return StaticAsset The resolved static asset.
     */
    public function static(string $path): StaticAsset
    {
        return new StaticAsset($this->assetsResolver->getStaticUrl() . $path);
    }

    /**
     * Resolves a script asset path and returns a ScriptAsset instance.
     *
     * @param string $path The script asset path.
     * @return ScriptAsset The resolved script asset.
     */
    public function script(string $path): ScriptAsset
    {
        return new ScriptAsset($this->assetsResolver->getScriptAssetUrl($path), $this->plugin->getVersion());
    }

    /**
     * Resolves an app asset path and returns an AppAsset instance, including associated style assets.
     *
     * @param string $path The app asset path.
     * @return AppAsset The resolved app asset along with style assets.
     */
    public function app(string $path): AppAsset
    {
        return new AppAsset(
            $this->assetsResolver->getAppAssetUrl($path),
            version: $this->plugin->getVersion(),
            styleAssets: $this->getAppStyles($path),
        );
    }

    /**
     * Resolves a style asset path and returns a StyleAsset instance.
     *
     * @param string $path The style asset path.
     * @return StyleAsset The resolved style asset.
     */
    public function style(string $path): StyleAsset
    {
        return new StyleAsset($this->assetsResolver->getStyleAssetUrl($path), $this->plugin->getVersion());
    }

    /**
     * Resolves the full URL of a given asset path.
     *
     * @param string $path The asset path.
     * @return string The resolved URL of the asset.
     */
    public function resolveAssetUrl(string $path): string
    {
        return $this->assetsResolver->resolveAssetUrl($path);
    }

    /**
     * Injects a path resolver function for static assets, allowing flexible usage in templates.
     *
     * @param string $functionName The function name to use for resolving static assets (default is 'static').
     * @return string The injected static asset path resolver.
     */
    public function injectStaticPathResolver(string $functionName = 'static'): string
    {
        return $this->assetsResolver->injectStaticPathResolver($functionName);
    }

    /**
     * Resolves the style assets associated with a given app path, returning an array of StyleAsset instances.
     *
     * @param string $path The app path for which to resolve style assets.
     * @return StyleAsset[] An array of resolved style assets.
     */
    public function getAppStyles(string $path): array
    {
        return array_map(function ($path) {
            return new StyleAsset($this->assetsResolver->getBuildDirUrl() . '/' . $path, $this->plugin->getVersion());
        }, $this->assetsResolver->getAppStylesPaths($path));
    }
}
