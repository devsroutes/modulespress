<?php

namespace ModulesPress\Foundation;

use ModulesPress\Core\Core;
use ModulesPress\Foundation\Module\ModulesPressModule;

/**
 * Base class for creating WordPress plugins using the ModulesPress framework.
 * Provides methods for plugin initialization, activation, deactivation, and utility functions
 * to manage the plugin lifecycle and configurations. It uses dependency injection and 
 * modular architecture of the framework.
 */
abstract class ModulesPressPlugin
{
    /**
     * Plugin name.
     */
    public const NAME = "";

    /**
     * Plugin slug used in WordPress.
     */
    public const SLUG = "";

    /**
     * Plugin prefix.
     */
    public const PREFIX = "";

    /**
     * Plugin version.
     */
    protected string $version = "1.0.0";

    /**
     * Path to the root directory of the plugin.
     */
    protected string $rootDirPath = "";

    /**
     * URL to the root directory of the plugin.
     */
    protected string $rootDirUrl = "";

    /**
     * Path to the views directory.
     */
    protected string $viewsDirPath = "";

    /**
     * Path to the cache directory.
     */
    protected string $cacheDirPath = "";

    /**
     * Whether the plugin is in development mode.
     */
    protected bool $isDevelopment;

    /**
     * Whether the plugin is in debug mode.
     */
    protected bool $isDebug;

    /**
     * REST API namespace for the plugin.
     */
    protected string $restNamespace = "";

    /**
     * Core instance that manages the plugin.
     */
    private Core $core;

    /**
     * Constructor to initialize the plugin with a root module, root directory, and root file.
     * It sets up basic plugin properties and paths and registers activation and deactivation hooks.
     * 
     * @param class-string<ModulesPressModule> $rootModule Class name of the root module to be used by the plugin.
     * @param string $rootDir Path to the root directory of the plugin.
     * @param string $rootFile Path to the main plugin file.
     */
    public function __construct(
        protected string $rootModule,
        string $rootDir,
        string $rootFile
    ) {
        $this->init($rootDir, $rootFile);
    }

    /**
     * Initializes plugin settings like directory paths and sets up the core system.
     * Registers activation and deactivation hooks for the plugin.
     * 
     * @param string $rootDir Path to the root directory of the plugin.
     * @param string $rootFile Path to the main plugin file.
     */
    protected function init(string $rootDir, string $rootFile): void
    {
        if (!$this->rootDirPath) {
            $this->rootDirPath = $rootDir;
        }

        if (!$this->rootDirUrl) {
            $this->rootDirUrl = plugins_url("", $rootFile);
        }

        if (!$this->viewsDirPath) {
            $this->viewsDirPath = $this->rootDirPath . "/views";
        }

        if (!$this->cacheDirPath) {
            $this->cacheDirPath = $this->rootDirPath . "/.cache";
        }

        $this->core = new Core($this->rootModule, $this);

        register_activation_hook($rootFile, [$this, 'onActivate']);
        register_deactivation_hook($rootFile, [$this, 'onDeactivate']);
    }

    /**
     * Boots up the plugin and prepares it for use. Initializes the core system
     * and calls the onPluginReady method to perform additional setup if needed.
     */
    public function bootstrap(): void
    {
        $this->core->bootstrap();
        $this->onPluginReady($this);
    }

    /**
     * Called when the plugin is ready for use. This method can be overridden in the plugin 
     * subclass to perform custom setup.
     * 
     * @param ModulesPressPlugin $plugin The plugin instance.
     */
    protected function onPluginReady(ModulesPressPlugin $plugin): void {}

    /**
     * Called when the plugin is activated. Fires an action hook after the plugin is activated.
     * 
     * @param bool $networkWide Whether the activation is for a network-wide install.
     */
    public function onActivate(bool $networkWide): void
    {
        do_action(static::SLUG . '/activate', $networkWide);
    }

    /**
     * Called when the plugin is deactivated. Fires an action hook after the plugin is deactivated.
     */
    public function onDeactivate(): void
    {
        do_action(static::SLUG . '/deactivate');
    }

    /**
     * Gets the version of the plugin.
     * 
     * @return string The version of the plugin.
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Gets the root directory path of the plugin.
     * 
     * @return string The root directory path.
     */
    public function getRootDirPath(): string
    {
        return $this->rootDirPath;
    }

    /**
     * Gets the URL to the root directory of the plugin.
     * 
     * @return string The root directory URL.
     */
    public function getRootDirUrl(): string
    {
        return $this->rootDirUrl;
    }

    /**
     * Gets the views directory path of the plugin.
     * 
     * @return string The views directory path.
     */
    public function getViewsDirPath(): string
    {
        return $this->viewsDirPath;
    }

    /**
     * Gets the cache directory path of the plugin.
     * 
     * @return string The cache directory path.
     */
    public function getCacheDirPath(): string
    {
        return $this->cacheDirPath;
    }

    /**
     * Checks if the plugin is in development mode.
     * 
     * @return bool True if the plugin is in development mode, false otherwise.
     */
    public function isDevelopmentMode(): bool
    {
        return $this->isDevelopment;
    }

    /**
     * Checks if the plugin is in debug mode.
     * 
     * @return bool True if the plugin is in debug mode, false otherwise.
     */
    public function isDebugMode(): bool
    {
        return $this->isDebug;
    }

    /**
     * Gets the root module class name of the plugin.
     * 
     * @return string The class name of the root module.
     */
    public function getRootModule(): string
    {
        return $this->rootModule;
    }

    /**
     * Gets the REST API namespace for the plugin.
     * 
     * @return string The REST API namespace.
     */
    public function getRestNamespace(): string
    {
        return $this->restNamespace;
    }
}
