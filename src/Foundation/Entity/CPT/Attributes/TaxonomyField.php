<?php

namespace ModulesPress\Foundation\Entity\CPT\Attributes;

use Attribute;
use ReflectionClass;
use ModulesPress\Foundation\Entity\CPT\Field;
use ModulesPress\Foundation\Entity\CPT\Attributes\Taxonomy;

/**
 * The TaxonomyField attribute is used to define a field that is associated with a specific taxonomy.
 * This attribute can be applied to properties in a CPTEntity class to link them with a taxonomy.
 * It holds the taxonomy slug and a default value for the field.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class TaxonomyField extends Field
{
    /**
     * The slug of the taxonomy that the field is associated with.
     * This is used to link the field with a specific taxonomy.
     *
     * @var string
     */
    public string $taxonomy;

    /**
     * The default value of the field. This can be any value, including a callable.
     * The default value can be used if no explicit value is provided for the field.
     *
     * @var mixed
     */
    public mixed $default;

    /**
     * Constructor to initialize the taxonomy field attributes.
     *
     * @param string $taxonomy The slug of the taxonomy that the field is associated with.
     * @param mixed $default The default value of the field.
     */
    public function __construct(
        string $taxonomy,
        mixed $default = null
    ) {
        parent::__construct($default);
        $this->taxonomy = $taxonomy;
        $this->default = $default;
    }

    /**
     * Get the Taxonomy object associated with this field.
     * This method uses reflection to find the Taxonomy attribute applied to the taxonomy class
     * and returns an instance of that taxonomy.
     *
     * @return Taxonomy The Taxonomy instance associated with this field.
     *
     * @throws \ReflectionException If the taxonomy class is not found or does not have the Taxonomy attribute.
     */
    public function getTaxonomy(): Taxonomy
    {
        // Use reflection to get the Taxonomy attribute from the class associated with the taxonomy.
        $taxonomyReflectionClass = new ReflectionClass($this->taxonomy);
        $taxonomyAttributes = $taxonomyReflectionClass->getAttributes(Taxonomy::class);

        if (empty($taxonomyAttributes)) {
            throw new \ReflectionException("Taxonomy attribute not found for {$this->taxonomy}");
        }

        return $taxonomyAttributes[0]->newInstance();
    }
}
