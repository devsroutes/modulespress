<?php

namespace ModulesPress\Core\Resolver;

use ModulesPress\Foundation\Http\Attributes\RestController;
use ModulesPress\Foundation\Module\Attributes\Module;
use ModulesPress\Foundation\Entity\CPT\Attributes\CustomPostType;
use ModulesPress\Common\Exceptions\FrameworkException\ModuleResolutionException;
use ModulesPress\Common\Provider\Provider;
use ModulesPress\Core\AttributesScanner\AttributesScanner;
use ModulesPress\Foundation\Module\Attributes\GlobalModule;
use ModulesPress\Foundation\Module\Contracts\DynamicModule;
use ModulesPress\Foundation\Module\ModulesPressModule;

/**
 * Class ResolvedModule
 * 
 * Resolves and manages a module instance in the plugin. This class handles
 * the validation of module attributes, imports, exports, controllers, entities, and providers.
 * It ensures that all required dependencies are met and validates their existence.
 */
final class ResolvedModule
{
    /**
     * @var bool Indicates whether the module is global.
     */
    private bool $isGlobal;

    /**
     * ResolvedModule constructor.
     * 
     * @param Module $module The module metadata (e.g., controllers, providers).
     * @param string $moduleClass The class name of the resolved module.
     * @param ResolvedModule|null $loadedByModule The module that loaded this module, if applicable.
     * @param ModulesPressModule $instance The actual module instance.
     */
    public function __construct(
        private readonly Module $module,
        private string $moduleClass,
        private readonly ResolvedModule | null $loadedByModule,
        private readonly ModulesPressModule $instance
    ) {
        $this->isGlobal = AttributesScanner::haveAttribute(GlobalModule::class, $this->moduleClass);
        $this->validateModuleAttributes();
    }

    /**
     * Checks if the module is marked as global.
     * 
     * @return bool True if the module is global, false otherwise.
     */
    public function isGlobal(): bool
    {
        return $this->isGlobal;
    }

    /**
     * Retrieves the class name of the resolved module.
     * 
     * @return string The class name of the module.
     */
    public function getClass(): string
    {
        return $this->moduleClass;
    }

    /**
     * Retrieves the instance of the resolved module.
     * 
     * @return ModulesPressModule The resolved module instance.
     */
    public function getInstance(): ModulesPressModule
    {
        return $this->instance;
    }

    /**
     * Retrieves the module that loaded this module, if any.
     * 
     * @return ResolvedModule|null The module that loaded this one, or null if not applicable.
     */
    public function getLoadedByModule(): ResolvedModule | null
    {
        return $this->loadedByModule;
    }

    /**
     * Retrieves the list of controllers defined in the module.
     * 
     * @return string[] Array of controller class names.
     */
    public function getControllers(): array
    {
        return $this->module->controllers;
    }

    /**
     * Retrieves the list of providers defined in the module.
     * 
     * @return Provider[] Array of provider instances.
     */
    public function getProviders(): array
    {
        return $this->module->providers;
    }

    /**
     * Retrieves the list of entities defined in the module.
     * 
     * @return string[] Array of entity class names.
     */
    public function getEntites(): array
    {
        return $this->module->entities;
    }

    /**
     * Retrieves the list of imports defined in the module.
     * 
     * @return string[]|DynamicModule[] Array of imported modules (either class names or dynamic module instances).
     */
    public function getImports(): array
    {
        return $this->module->imports;
    }

    /**
     * Retrieves the imports as class names for further processing.
     * 
     * @return string[] Array of class names of imported modules.
     */
    public function getImportsAsClasses(): array
    {
        return array_map(
            function ($import) {
                return is_a($import, DynamicModule::class) ? $import::class : $import;
            },
            $this->getImports()
        );
    }

    /**
     * Retrieves the list of exports defined in the module.
     * 
     * @return string[] Array of exported provider tokens.
     */
    public function getExports(): array
    {
        return $this->module->exports;
    }

    /**
     * Retrieves the tokens of all provided providers in the module.
     * 
     * @return string[] Array of provider tokens.
     */
    public function getProvidedTokens(): array
    {
        return array_map(fn(Provider $provider) => $provider->getProvidedToken(), $this->getProviders());
    }

    /**
     * Validates the module attributes, including its imports, providers, exports, controllers, and entities.
     * Ensures that all references are valid, and throws exceptions if there are issues with any attributes.
     */
    private function validateModuleAttributes()
    {
        foreach ($this->module->imports as $import) {

            // Check if import is a string (class name) and validate
            if (is_string($import)) {
                if (!class_exists($import)) {
                    $this->exceptionWithClass("Imported module '" . $import . "' class does not exist", $this->moduleClass);
                }
                if (!AttributesScanner::haveAttribute(Module::class, $import)) {
                    $this->exceptionWithClass("Imported module '" . $import . "' must have a 'Module' attribute in class '" . $this->moduleClass . "'", $this->moduleClass);
                }
            }
            // Validate dynamic module imports
            else if (is_a($import, DynamicModule::class)) {
                if (!AttributesScanner::haveAttribute(Module::class, $import::class)) {
                    $this->exceptionWithClass("Imported module '" . $import::class . "' must have a 'Module' attribute in class '" . $this->moduleClass . "'", $this->moduleClass);
                }
            } else {
                $this->exceptionWithClass("Imported module must be a class string or an instance of dynamic module.", $this->moduleClass);
            }
        }
        // Verify duplicates in imports
        $this->verifyDuplicates($this->getImportsAsClasses(), $this->moduleClass, "imports");

        // Validate providers in the module
        foreach ($this->module->providers as &$provider) {

            if (!$provider instanceof Provider) {
                if (!is_string($provider)) {
                    $this->exceptionWithClass("Provider must be a class string or an instance of Provider.", $this->moduleClass);
                }
            }

            if (is_string($provider)) {
                $provider = new Provider($provider, $provider);
            }

            if (
                !$provider->hasUsableClass() &&
                !$provider->hasUsableFactory() &&
                !$provider->hasUsableValue()
            ) {
                $this->exceptionWithClass("Provider must be provided a class, factory or value.", $this->moduleClass);
            }

            if ($provider->hasUsableClass()) {
                $useClass = $provider->getUsableClass();
                if (!class_exists($useClass)) {
                    $this->exceptionWithClass("Provided dependency '" . $useClass . "' class does not exist.", $this->moduleClass);
                }
            }

            if ($provider->hasUsableFactory()) {
                $useFactory = $provider->getUsableFactory();
                if (!is_callable($useFactory)) {
                    $this->exceptionWithClass("Provided dependency factory '" . $provider->getProvidedToken() . "' is not callable.", $this->moduleClass);
                }
            }
        }
        unset($provider);
        // Verify duplicates in providers
        $this->verifyDuplicates($this->getProvidedTokens(), $this->moduleClass, "providers");

        // Validate exports in the module
        foreach ($this->module->exports as $export) {
            if (!in_array($export, $this->getProvidedTokens())) {
                $this->exceptionWithClass("Exported provider '" . $export . "' must be present in provider list of module '" . $this->moduleClass . "'", $this->moduleClass);
            }
        }
        // Verify duplicates in exports
        $this->verifyDuplicates($this->module->exports, $this->moduleClass, "exports");

        // Validate controllers in the module
        foreach ($this->module->controllers as $controller) {
            if (!class_exists($controller)) {
                $this->exceptionWithClass("Controller '" . $controller . "' class does not exist", $this->moduleClass);
            }
            if (!AttributesScanner::haveAttribute(RestController::class, $controller)) {
                $this->exceptionWithClass("Controller '" . $controller . "' must have a 'Controller' attribute in class '" . $this->moduleClass . "'", $controller);
            }
        }
        // Verify duplicates in controllers
        $this->verifyDuplicates($this->module->controllers, $this->moduleClass, "controllers");

        // Validate entities in the module
        foreach ($this->module->entities as $entity) {
            if (!class_exists($entity)) {
                $this->exceptionWithClass("Entity '" . $entity . "' class does not exist", $this->moduleClass);
            }
            if (!AttributesScanner::haveAttribute(CustomPostType::class, $entity)) {
                $this->exceptionWithClass("Entity '" . $entity . "' must have a 'CPT' attribute in class '" . $this->moduleClass . "'", $entity);
            }
            // Verify duplicates in entities
            $this->verifyDuplicates($this->module->entities, $this->moduleClass, "entities");
        }
    }

    /**
     * Verifies if there are duplicate entries in the given array.
     * 
     * @param array $array The array to check for duplicates.
     * @param string $module_class The module class name for the error message.
     * @param string $attribute_type The type of attribute (e.g., imports, providers) for error context.
     */
    private function verifyDuplicates(array $array, string $module_class, string $attribute_type): void
    {
        if (count($array) !== count(array_unique($array))) {
            $this->exceptionWithClass("Duplicate '" . $attribute_type . "' found in module class '" . $module_class . "'", $module_class);
        }
    }

    /**
     * Throws a `ModuleResolutionException` with a custom error message.
     * 
     * @param string $message The error message.
     * @param string $class The class where the error occurred.
     * 
     * @throws ModuleResolutionException The exception thrown with the error message.
     */
    private function exceptionWithClass(string $message, string $class)
    {
        throw (new ModuleResolutionException(reason: $message))->forClass($class);
    }
}
