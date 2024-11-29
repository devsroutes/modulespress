<?php

namespace ModulesPress\Core\RenderingEngine;

use eftec\bladeone\BladeOne;
use Spatie\Ignition\Ignition;
use ModulesPress\Common\Exceptions\FrameworkException\ModuleResolutionException;
use ModulesPress\Core\Core;
use ModulesPress\Foundation\ModulesPressPlugin;

/**
 * Class RenderingEngine
 * 
 * Manages the BladeOne templating engine and integrates view composers and directives for a plugin.
 * Handles the rendering logic and exception rendering configuration.
 */
final class RenderingEngine
{
    /**
     * @var BladeOne The BladeOne templating engine instance.
     */
    private BladeOne $bladeOne;

    /**
     * RenderingEngine constructor.
     * 
     * @param Core $core Core instance of the framework.
     * @param ModulesPressPlugin $plugin The plugin instance being managed.
     */
    public function __construct(
        private readonly Core $core,
        private readonly ModulesPressPlugin $plugin
    ) {
        $this->bladeOne = $this->createBladeOne();
    }

    /**
     * Registers view composers in BladeOne.
     * 
     * View composers are callbacks that bind data to views when rendering them.
     *
     * @return $this The current RenderingEngine instance for chaining.
     */
    public function registerViewComposers()
    {
        foreach ($this->core->getPluginContainer()->getViewComposers() as $viewComposerComponent) {

            $provider = $viewComposerComponent->getProvider();
            $providerMethodReflection = $viewComposerComponent->getMethodReflection();

            $viewCompose = $viewComposerComponent->getViewCompose();
            $this->bladeOne->composer($viewCompose->getViewPath(), function (BladeOne $view)
            use ($provider, $providerMethodReflection) {
                call_user_func(
                    [
                        $this->core->getPluginContainer()->get($provider->getProvidedToken()),
                        $providerMethodReflection->getName()
                    ],
                    $view,
                );
            });
        }

        return $this;
    }

    /**
     * Registers view directives in BladeOne.
     * 
     * Directives are custom extensions of the templating engine to handle dynamic expressions.
     * 
     * @return $this The current RenderingEngine instance for chaining.
     * @throws ModuleResolutionException If an invalid directive type is encountered.
     */
    public function registerViewDirectives()
    {
        foreach ($this->core->getPluginContainer()->getViewDirectives() as $viewDirectiveComponent) {

            $provider = $viewDirectiveComponent->getProvider();
            $providerMethodReflection = $viewDirectiveComponent->getMethodReflection();

            $viewDirective = $viewDirectiveComponent->getViewDirective();
            $name = $viewDirective->getName();
            $mode = $viewDirective->getType();

            if ($mode === "onCompile")
                $directivefunction = "directive";
            else if ($mode === "onRuntime")
                $directivefunction = "directiveRT";
            else
                throw (new ModuleResolutionException(
                    reason: 'Invalid directive type. Only "onCompile" and "onRuntime" are supported.'
                ))->forClassMethod(
                    $viewDirectiveComponent->getClassReflection()->getName(),
                    $viewDirectiveComponent->getMethodReflection()->getName()
                );

            $this->bladeOne->$directivefunction($name, function () use ($viewDirective, $provider, $providerMethodReflection) {
                $args = func_get_args();
                $result = call_user_func(
                    [
                        $this->core->getPluginContainer()->get($provider->getProvidedToken()),
                        $providerMethodReflection->getName()
                    ],
                    ...$args
                );
                return $result;
            });
        }

        return $this;
    }

    /**
     * Retrieves the BladeOne templating engine instance.
     * 
     * @return BladeOne The BladeOne instance.
     */
    public function getTemplatingEngine(): BladeOne
    {
        return $this->bladeOne;
    }

    /**
     * Adds a runtime directive to the BladeOne templating engine.
     * 
     * @param string $name The name of the directive.
     * @param callable $callback The callback to execute when the directive is used.
     */
    public function addViewDirectiveForRuntime(string $name, callable $callback)
    {
        $this->bladeOne->directiveRT($name, $callback);
    }

    /**
     * Creates and configures a new BladeOne instance.
     * 
     * Sets up view and cache directories for the plugin. Creates the cache directory if it doesn't exist.
     * 
     * @return BladeOne A configured BladeOne instance.
     * @throws ModuleResolutionException If the cache directory cannot be created.
     */
    private function createBladeOne(): BladeOne
    {
        $views = $this->plugin->getViewsDirPath();
        $cache = $this->plugin->getCacheDirPath() . '/views';
        if (!is_dir($cache)) {
            $created = wp_mkdir_p($cache);
            if (!$created) {
                throw new ModuleResolutionException(reason: 'Failed to create plugin cache directory for views. Please check directory permission.');
            }
        }
        return new BladeOne($views, $cache);
    }

    /**
     * Retrieves the exception renderer for the plugin.
     * 
     * Uses Spatie Ignition if available to provide enhanced exception rendering in development environments.
     * 
     * @return Ignition|null The Ignition instance if available, or null otherwise.
     */
    public function getExceptionRenderer()
    {
        if (class_exists(Ignition::class)) {
            $ignition = new Ignition();
            $ignition->setTheme('dark');
            $ignition->applicationPath($this->plugin->getRootDirPath());
            return $ignition;
        }
        return null;
    }
}
