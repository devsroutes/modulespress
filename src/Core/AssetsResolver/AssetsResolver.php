<?php

namespace ModulesPress\Core\AssetsResolver;

use ModulesPress\Core\Core;
use ModulesPress\Core\Vite\Vite;

/**
 * Handles the resolution and management of asset paths and URLs within the framework.
 */
final class AssetsResolver
{
    private const RESOURCES_DIR = 'resources';
    private const SCRIPTS_DIR = 'scripts';
    private const STYLES_DIR = 'styles';
    private const STATIC_DIR = 'static';
    private const APPS_DIR = 'apps';
    private const BUILD_DIR = 'build/assets';

    private readonly string $manifestPath;

    private string $buildDirPath;
    private string $buildDirUrl;

    private string $resourcesPath;
    private string $scriptsPath;
    private string $appsPath;
    private string $stylesPath;
    private string $staticPath;

    /**
     * Constructs a new instance of AssetsResolver.
     *
     * @param Core $core The core framework instance.
     * @param Vite $vite The Vite instance for handling HMR and asset URLs.
     * @param string $rootDirPath The root directory path for the project.
     * @param string $rootDirUrl The root URL for the project.
     */
    public function __construct(
        private readonly Core $core,
        private readonly Vite $vite,
        private readonly string $rootDirPath,
        private readonly string $rootDirUrl
    ) {
        $this->buildDirPath = $this->rootDirPath . '/' . self::BUILD_DIR;
        $this->buildDirUrl = $this->rootDirUrl . '/' . self::BUILD_DIR;
        $this->manifestPath = $this->buildDirPath . '/vite/manifest.json';
        $this->resourcesPath = self::RESOURCES_DIR . '/';
        $this->scriptsPath = $this->resourcesPath . self::SCRIPTS_DIR . '/';
        $this->appsPath = $this->resourcesPath . self::APPS_DIR . '/';
        $this->stylesPath = $this->resourcesPath . self::STYLES_DIR . '/';
        $this->staticPath = self::STATIC_DIR . '/';
    }

    // --- Build Directory Methods ---

    /**
     * Retrieves the build directory path.
     *
     * @return string The absolute path to the build directory.
     */
    public function getBuildDirPath(): string
    {
        return $this->buildDirPath;
    }

    /**
     * Retrieves the build directory URL.
     *
     * @return string The URL to the build directory.
     */
    public function getBuildDirUrl(): string
    {
        return $this->buildDirUrl;
    }

    // --- Resource Methods ---

    /**
     * Retrieves the resource directory path.
     *
     * @return string The path to the resources directory.
     */
    public function getResourcesPath(): string
    {
        return $this->resourcesPath;
    }

    /**
     * Resolves the URL for a resource asset.
     *
     * @param string $path The relative path to the asset within the resources directory.
     * @return string The resolved URL to the asset.
     */
    public function getResourceAssetUrl(string $path): string
    {
        return $this->resolveAssetUrl($this->getResourcesPath() . $path);
    }

    /**
     * Retrieves the applications directory path.
     *
     * @return string The path to the applications directory.
     */
    public function getAppsPath(): string
    {
        return $this->appsPath;
    }

    /**
     * Resolves the URL for an application asset.
     *
     * @param string $path The relative path to the asset within the applications directory.
     * @return string The resolved URL to the asset.
     */
    public function getAppAssetUrl(string $path): string
    {
        return $this->resolveAssetUrl($this->getAppsPath() . $path);
    }

    // --- Script Methods ---

    /**
     * Retrieves the scripts directory path.
     *
     * @return string The path to the scripts directory.
     */
    public function getScriptsPath(): string
    {
        return $this->scriptsPath;
    }

    /**
     * Resolves the URL for a script asset.
     *
     * @param string $path The relative path to the asset within the scripts directory.
     * @return string The resolved URL to the asset.
     */
    public function getScriptAssetUrl(string $path): string
    {
        return $this->resolveAssetUrl($this->getScriptsPath() . $path);
    }

    // --- Style Methods ---

    /**
     * Retrieves the styles directory path.
     *
     * @return string The path to the styles directory.
     */
    public function getStylesPath(): string
    {
        return $this->stylesPath;
    }

    /**
     * Resolves the URL for a style asset.
     *
     * @param string $path The relative path to the asset within the styles directory.
     * @return string The resolved URL to the asset.
     */
    public function getStyleAssetUrl(string $path): string
    {
        return $this->resolveAssetUrl($this->getStylesPath() . $path);
    }

    // --- Static Methods ---

    /**
     * Retrieves the static assets directory path.
     *
     * @return string The path to the static directory.
     */
    public function getStaticPath(): string
    {
        return $this->staticPath;
    }

    /**
     * Retrieves the base URL for static assets.
     *
     * @return string The base URL for static assets, either from Vite or the build directory.
     */
    public function getStaticUrl(): string
    {
        return $this->vite->isActive() ? $this->vite->getHostURL() . '/' : $this->getBuildDirUrl() . '/';
    }

    /**
     * Resolves the URL for an asset based on the given path.
     *
     * @param string $path The relative path to the asset.
     * @return string The resolved URL to the asset.
     */
    public function resolveAssetUrl(string $path): string
    {
        if ($this->vite->isActive()) {
            return $this->vite->getHostURL() . '/' . $path;
        }

        $manifest = $this->getManifest();
        return $this->getBuildDirUrl() . '/' . $manifest[$path]['file'];
    }

    public function getAppStylesPaths(string $path)
    {
        if ($this->vite->isActive()) {
            return [];
        } else {
            $manifest = $this->getManifest();
            return $manifest[$this->getAppsPath() . $path]['css'] ?? [];
        }
    }

    public function onStaticViewDirective($path): void
    {
        echo $this->getStaticUrl() . $path;
    }

    public function injectStaticPathResolver(string $functionName): string
    {
        $inline = "window.{$functionName} = function(path){\n";
        $inline .= "return '" . $this->getStaticUrl() . "' + path; }\n";
        return $inline;
    }

    /**
     * Retrieves the manifest data for built assets.
     *
     * @return array The manifest data parsed from the JSON file.
     */
    private function getManifest(): array
    {
        return json_decode(file_get_contents($this->manifestPath), true);
    }

    /**
     * Checks if the manifest file exists.
     *
     * @return bool True if the manifest file exists, false otherwise.
     */
    private function manifestExists(): bool
    {
        return file_exists($this->manifestPath);
    }
}
