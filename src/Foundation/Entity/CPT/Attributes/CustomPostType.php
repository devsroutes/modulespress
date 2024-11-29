<?php

namespace ModulesPress\Foundation\Entity\CPT\Attributes;

use Attribute;

/**
 * The CustomPostType attribute class is used to define a custom post type for a CPTEntity class.
 * This attribute stores metadata about the custom post type, such as its name, singular and plural labels,
 * and additional arguments for registering the custom post type in WordPress.
 * 
 * It can be applied to a custom post type entity class to mark it as a WordPress custom post type
 * and to define its properties when registering the post type.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class CustomPostType
{
    /**
     * The name of the custom post type.
     * Used as the internal identifier when registering the custom post type in WordPress.
     *
     * @var string
     */
    public string $name;

    /**
     * The singular label for the custom post type.
     * This label is used in the WordPress admin UI when referring to a single post of this type.
     *
     * @var string
     */
    public string $singular;

    /**
     * The plural label for the custom post type.
     * This label is used in the WordPress admin UI when referring to multiple posts of this type.
     *
     * @var string
     */
    public string $plural;

    /**
     * Additional arguments used when registering the custom post type.
     * This allows customization of the post type registration by passing extra parameters.
     *
     * @var array
     */
    public array $args;

    /**
     * Constructor to initialize the properties of the custom post type.
     *
     * @param string $name The name of the custom post type (used for registration).
     * @param string $singular The singular label for the custom post type.
     * @param string $plural The plural label for the custom post type.
     * @param array $args Optional additional arguments for registering the custom post type.
     */
    public function __construct(
        string $name,
        string $singular,
        string $plural,
        array $args = []
    ) {
        $this->name = $name;
        $this->singular = $singular;
        $this->plural = $plural;
        $this->args = $args;
    }
}
