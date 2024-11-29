<?php

namespace ModulesPress\Foundation\Entity\CPT\Attributes;

use Attribute;

/**
 * Taxonomy is an attribute class used to define a custom taxonomy for a CPTEntity.
 * It provides metadata for the custom taxonomy, including its slug, singular and plural labels, 
 * and additional arguments used when registering the taxonomy in WordPress.
 * 
 * This attribute can be applied to a class to mark it as a custom WordPress taxonomy
 * and to define its properties.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class Taxonomy
{
    /**
     * The slug used for the taxonomy. This is the URL-friendly identifier for the taxonomy.
     *
     * @var string
     */
    public string $slug;

    /**
     * The singular label for the taxonomy. This is used in the WordPress UI when referring to a single term of this type.
     *
     * @var string
     */
    public string $singular;

    /**
     * The plural label for the taxonomy. This is used in the WordPress UI when referring to multiple terms of this type.
     *
     * @var string
     */
    public string $plural;

    /**
     * Additional arguments used when registering the custom taxonomy.
     * This allows passing any extra parameters that WordPress might need for taxonomy registration.
     *
     * @var array
     */
    public array $args;

    /**
     * Constructor to initialize the taxonomy attributes.
     *
     * @param string $slug The slug for the taxonomy (used in registration).
     * @param string $singular The singular label for the taxonomy.
     * @param string $plural The plural label for the taxonomy.
     * @param array $args Additional arguments for registering the custom taxonomy.
     */
    public function __construct(
        string $slug,
        string $singular,
        string $plural,
        array $args = []
    ) {
        $this->slug = $slug;
        $this->singular = $singular;
        $this->plural = $plural;
        $this->args = $args;
    }
}
