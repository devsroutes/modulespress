<?php

namespace ModulesPress\Foundation\Entity\CPT;

use ModulesPress\Foundation\Entity\CPT\Attributes\CustomPostType;
use ModulesPress\Foundation\Entity\CPT\Attributes\MetaField;
use ModulesPress\Foundation\Entity\BaseEntity;
use ModulesPress\Foundation\Entity\CPT\Attributes\TaxonomyField;
use ReflectionClass;
use ReflectionProperty;
use WP_Post;

/**
 * CPTEntity serves as a base class for entities representing WordPress custom post types (CPT).
 * This class provides methods to retrieve and manage CPT-specific fields such as title, content, excerpt, 
 * meta fields, and taxonomy fields. It also includes methods for interacting with the WordPress database 
 * for custom post type data.
 *
 * Subclasses of CPTEntity will represent specific custom post types and should define specific behavior
 * for those post types.
 */
abstract class CPTEntity extends BaseEntity
{
    /**
     * The title of the custom post type entity.
     *
     * @var string
     */
    public string $title = "";

    /**
     * The content of the custom post type entity.
     *
     * @var string
     */
    public string $content = "";

    /**
     * The excerpt of the custom post type entity.
     *
     * @var string
     */
    public string $excerpt = "";

    /**
     * Retrieves the custom post type name associated with this entity.
     *
     * This method uses reflection to check for the `CustomPostType` attribute on the class and retrieves the 
     * name of the post type. If no `CustomPostType` attribute is found, it throws an exception.
     * 
     * @return string The custom post type name.
     * @throws \Exception If the `CustomPostType` attribute is missing.
     */
    public static function getPostType(): string
    {
        // Get the class reflection for the current entity
        $reflection = new ReflectionClass(static::class);
        
        // Check if the `CustomPostType` attribute exists on the class
        $attributes = $reflection->getAttributes(CustomPostType::class);

        // If the attribute is not found, throw an exception
        if (empty($attributes)) {
            throw new \Exception("CustomPostType attribute is missing");
        }

        // Retrieve the post type name from the attribute
        $postTypeAttr = $attributes[0]->newInstance();
        return $postTypeAttr->name;
    }

    /**
     * Retrieves the WordPress post object associated with this entity.
     *
     * This method returns the full `WP_Post` object by using the entity's ID.
     * The `WP_Post` object can be used to access other properties and methods provided by WordPress.
     * 
     * @return WP_Post The WordPress post object.
     */
    public function getWpPost(): WP_Post
    {
        return get_post($this->id);
    }

    /**
     * Retrieves the meta fields associated with this custom post type entity.
     *
     * This method uses reflection to find properties on the class that are annotated with the `MetaField` attribute.
     * The meta fields are returned as an array of `MetaField` objects.
     * 
     * @return MetaField[] An array of `MetaField` attributes.
     */
    public static function getMetaFields(): array
    {
        return self::getMappedAttributes(MetaField::class);
    }

    /**
     * Retrieves the taxonomy fields associated with this custom post type entity.
     *
     * Similar to the meta fields, this method uses reflection to find properties that are annotated with the 
     * `TaxonomyField` attribute. The taxonomy fields are returned as an array of `TaxonomyField` objects.
     * 
     * @return TaxonomyField[] An array of `TaxonomyField` attributes.
     */
    public static function getTaxonomyFields(): array
    {
        return self::getMappedAttributes(TaxonomyField::class);
    }

    /**
     * Helper method to retrieve mapped attributes for a given class type.
     *
     * This method uses reflection to get public properties of the class and checks for the specified attribute class
     * on each property. The matched attributes are returned as an array.
     * 
     * @param string $class The fully qualified class name of the attribute to look for (e.g., `MetaField` or `TaxonomyField`).
     * @return array An associative array of property names and their associated attributes.
     */
    private static function getMappedAttributes(string $class): array
    {
        // Reflect on the current class
        $reflection = new ReflectionClass(static::class);

        // Get all public properties of the class
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
        $mappedAttributes = [];

        // Loop through properties and check for the specified attribute
        foreach ($properties as $property) {
            // Get any attributes that match the provided class
            $attributesFound = $property->getAttributes($class);

            // If the attribute exists, add it to the result
            if (!empty($attributesFound)) {
                $mappedAttributes[$property->getName()] = $attributesFound[0]->newInstance();
            }
        }

        return $mappedAttributes;
    }
}
