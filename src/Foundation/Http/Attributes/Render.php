<?php

namespace ModulesPress\Foundation\Http\Attributes;

use Attribute;

/**
 * Class Render
 *
 * This class represents an attribute used to associate a Blade view template with a method in the WordPress REST API.
 * It is applied to methods responsible for rendering a Blade view in response to a specific request.
 * The data returned by the route handler is automatically passed to the Blade view for rendering.
 * 
 * The Blade view is rendered using the specified template, and the handler's data is injected into it for rendering
 * dynamic content in the response.
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class Render
{
    /**
     * The name of the Blade view template to render.
     *
     * This view will be rendered and returned as the response content. The view template can be a Blade file
     * located within the theme or plugin view directories.
     *
     * @var string
     */
    private readonly string $view;

    /**
     * Constructor for the Render attribute.
     *
     * This constructor initializes the `Render` attribute with the specified view name. The handler's data is passed 
     * to this view for rendering, allowing dynamic content to be returned in the response.
     *
     * @param string $view The name of the Blade view template to render.
     */
    public function __construct(
      string $view
    ) {
        $this->view = $view;
    }

    /**
     * Get the name of the Blade view associated with this render attribute.
     *
     * This method returns the name of the Blade view that is used to generate the response content.
     * The view is rendered with the data returned by the route handler.
     *
     * @return string The name of the Blade view template.
     */
    public function getView(): string
    {
        return $this->view;
    }
}
