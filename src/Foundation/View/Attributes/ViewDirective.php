<?php

namespace ModulesPress\Foundation\View\Attributes;

use Attribute;

/**
 * Attribute ViewDirective
 *
 * Marks a method as a custom Blade directive for the view engine.
 * Blade directives can be used to introduce custom logic or syntax within templates.
 * The directive can either be compiled during template compilation (`onCompile`) 
 * or executed during runtime (`onRuntime`).
 *
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class ViewDirective
{
    /**
     * @param string $name The name of the directive as it will be used in the Blade template.
     *                     Example: `@yourDirectiveName`.
     * @param string $type The type of the directive execution. 
     *                     Possible values:
     *                     - `onCompile`: Directive is processed during template compilation.
     *                     - `onRuntime`: Directive is executed during template rendering.
     *                     Default is `onCompile`.
     */
    public function __construct(
        public string $name,
        public string $type = "onCompile"
    ) {}

    /**
     * Retrieve the name of the directive.
     *
     * @return string The directive's name used in the Blade template.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Retrieve the type of the directive execution.
     *
     * @return string The type of execution, either `onCompile` or `onRuntime`.
     */
    public function getType(): string
    {
        return $this->type;
    }
}
