<?php

namespace ModulesPress\Foundation\View\Attributes;

use Attribute;

/**
 * Attribute ViewCompose
 *
 * Marks a method as a view composer, associating it with a specific view path.
 * View composers allow methods to prepare data or logic that will be passed to the view before rendering.
 *
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class ViewCompose
{
    /**
     * @param string $viewPath The view path to associate with the composer method. 
     *                         The path should correspond to the Blade template file.
     */
    public function __construct(
        public string $viewPath,
    ) {}

    /**
     * Retrieve the associated view path.
     *
     * @return string The path of the view that the composer is linked to.
     */
    public function getViewPath(): string
    {
        return $this->viewPath;
    }
}
