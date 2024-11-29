<?php

namespace ModulesPress\Core;

use Closure;
use ModulesPress\Common\Exceptions\FrameworkException\ModuleResolutionException;
use ModulesPress\Core\AssetsResolver\AssetsResolver;
use ModulesPress\Core\AttributesScanner\AttributesScanner;
use ModulesPress\Foundation\Module\Attributes\Module;
use ModulesPress\Foundation\DI\Enums\Token;
use ModulesPress\Core\DiscoveryService\DiscoveryService;
use ModulesPress\Core\Enquerer\Enquerer;
use ModulesPress\Core\EntityRegistrar\EntityRegistrar;
use ModulesPress\Core\ExceptionHandler\ExceptionHandler;
use ModulesPress\Core\ExecutionContext\ExecutionContext;
use ModulesPress\Core\HooksRegistrar\HooksRegistrar;
use ModulesPress\Core\Http\Http;
use ModulesPress\Core\ModulesVisualizer\ModulesVisualizer;
use ModulesPress\Core\PluginContainer\PluginContainer;
use ModulesPress\Core\RenderingEngine\RenderingEngine;
use ModulesPress\Core\Renderer\Renderer;
use ModulesPress\Core\Resolver\Resolver;
use ModulesPress\Core\Vite\Vite;
use ModulesPress\Foundation\ModulesPressPlugin;

/**
 * Core Class
 * 
 * This class serves as the backbone of the ModulesPress framework.
 * It handles bootstrapping, dependency mapping, and initialization
 * of key components required by the framework.
 */
final class Core
{
    // Core services and dependencies
    private DiscoveryService $discoveryService;
    private PluginContainer $pluginContainer;
    private ExecutionContext $executionContext;
    private ExceptionHandler $exceptionHandler;
    private Resolver $resolver;
    private Http $http;
    private HooksRegistrar $hooksRegistrar;
    private EntityRegistrar $entityRegistrar;
    private Renderer $renderer;
    private RenderingEngine $renderingEngine;
    private Vite $vite;
    private AssetsResolver $assetsResolver;
    private Enquerer $enquerer;

    // Host URL for Vite development server
    const HOST = "http://localhost:5173/includes/network";

    /**
     * @var array<string, mixed> $frameworkDependencies
     * 
     * Framework-level dependencies that can be resolved via the DI container.
     */
    private array $frameworkDependencies = [];

    /**
     * Constructor
     * 
     * @param string $rootModuleClass Fully qualified name of the root module class.
     * @param ModulesPressPlugin $plugin Instance of the main plugin class.
     */
    public function __construct(
        private readonly string $rootModuleClass,
        private readonly ModulesPressPlugin $plugin,
    ) {}

    /**
     * Validates the provided root module class.
     * 
     * Ensures that the root module is a valid class and has the @Module attribute.
     * 
     * @throws ModuleResolutionException If validation fails.
     */
    private function validateAppModule(): void
    {
        if (!is_string($this->rootModuleClass)) {
            throw new ModuleResolutionException(reason: "Root module class must be a string.");
        }
        if (!AttributesScanner::haveAttribute(Module::class, $this->rootModuleClass)) {
            throw (new ModuleResolutionException(reason: "Root Module '" . $this->rootModuleClass . "' must have a 'Module' attribute."))->forClass($this->rootModuleClass);
        }
    }

    /**
     * Bootstrap the ModulesPress framework.
     * 
     * Initializes and configures all core services and components required for the framework
     * to function, including exception handling, dependency injection, asset management,
     * and HTTP routing.
     */
    public function bootstrap(): void
    {
        try {
            // Initialize execution context
            $this->executionContext = new ExecutionContext();

            // Initialize rendering engine
            $this->renderingEngine = new RenderingEngine($this, $this->plugin);

            // Initialize renderer
            $this->renderer = new Renderer($this->renderingEngine);

            // Initialize exception handler
            $this->exceptionHandler = new ExceptionHandler($this, $this->executionContext, $this->renderer);

            // Configure Vite for asset management
            $this->vite = new Vite(
                hostURL: "http://localhost:5173",
                isDevelopment: $this->plugin->isDevelopmentMode()
            );

            // Resolve assets for use in the framework
            $this->assetsResolver = new AssetsResolver(
                core: $this,
                vite: $this->vite,
                rootDirPath: $this->plugin->getRootDirPath(),
                rootDirUrl: $this->plugin->getRootDirUrl(),
            );

            // Enqueue scripts and styles
            $this->enquerer = new Enquerer(
                core: $this,
                assetsResolver: $this->assetsResolver,
                plugin: $this->plugin
            );

            // Discover modules and services
            $this->discoveryService = new DiscoveryService($this);

            // Initialize the DI resolver
            $this->resolver = new Resolver($this);

            // Create plugin container
            $this->pluginContainer = new PluginContainer(
                $this,
                $this->rootModuleClass,
                $this->discoveryService
            );

            // Register custom entities
            $this->entityRegistrar = new EntityRegistrar($this);

            // Register hooks
            $this->hooksRegistrar = new HooksRegistrar($this, $this->exceptionHandler, $this->executionContext);

            // Set up HTTP routing
            $this->http = new Http(
                $this,
                $this->exceptionHandler,
                $this->executionContext,
                $this->plugin->getRestNamespace()
            );

            // Map framework dependencies
            $this->frameworkDependencies = $this->mapFrameworkDependencies();

            // Add dependencies to the plugin container
            foreach ($this->frameworkDependencies as $token => $dependency) {
                $this->pluginContainer->set($token, $dependency);
            }

            // Validate and bootstrap the root module
            $this->validateAppModule($this->rootModuleClass);
            $this->pluginContainer->buildDependencySystem();

            // Register hooks and entities
            $this->vite->registerRequiredHooks();
            $this->entityRegistrar->registerRequiredHooks()->registerEntities();
            $this->hooksRegistrar->registerHookables();
            $this->http->registerRequiredHooks();

            // Configure rendering directives
            $this->renderingEngine
                ->registerViewComposers()
                ->registerViewDirectives()
                ->addViewDirectiveForRuntime("static", [$this->assetsResolver, "onStaticViewDirective"]);
        } catch (\Throwable $th) {
            $this->exceptionHandler->handleException($th);
        }
    }

    /**
     * Map framework-level dependencies for use in the DI container.
     * 
     * @return array<string, mixed> An associative array of dependencies.
     */
    private function mapFrameworkDependencies(): array
    {
        return [
            ModulesPressPlugin::class => $this->plugin,
            Renderer::class => $this->renderer,
            Enquerer::class => $this->enquerer,
            Token::SAFE_EXECUTION => function () {
                return function (Closure | callable $call) {
                    try {
                        return $call();
                    } catch (\Throwable $th) {
                        $this->exceptionHandler->handleException($th);
                    }
                };
            },
        ];
    }

    /**
     * Get the plugin container instance.
     * 
     * @return PluginContainer The plugin container.
     */
    public function getPluginContainer(): PluginContainer
    {
        return $this->pluginContainer;
    }

    /**
     * Get the DI resolver instance.
     * 
     * @return Resolver The resolver instance.
     */
    public function getResolver(): Resolver
    {
        return $this->resolver;
    }

    /**
     * Get the renderer instance.
     * 
     * @return Renderer The renderer instance.
     */
    public function getRenderer(): Renderer
    {
        return $this->renderer;
    }

    /**
     * Get the HTTP handler instance.
     * 
     * @return Http The HTTP handler.
     */
    public function getHTTP(): Http
    {
        return $this->http;
    }

    /**
     * Get the plugin instance.
     * 
     * @return ModulesPressPlugin The plugin instance.
     */
    public function getPlugin(): ModulesPressPlugin
    {
        return $this->plugin;
    }

    /**
     * Get all framework dependencies.
     * 
     * @return array<string, mixed> Framework dependencies.
     */
    public function getFrameworkDependencies(): array
    {
        return $this->frameworkDependencies;
    }
}
