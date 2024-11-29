<?php

namespace ModulesPress\Foundation\Entity;

/**
 * BaseEntity provides common functionality for entities in the plugin.
 * It includes methods for handling the entity's ID, converting the entity to an array,
 * and other utility methods that can be extended by specific entity classes.
 *
 * Entities are typically used to represent data that is persistent or associated with custom post types,
 * taxonomies, or other data structures within the plugin.
 */
abstract class BaseEntity
{
    /**
     * The unique identifier for the entity.
     *
     * This ID typically corresponds to the ID of a custom post type or another plugin-specific entity.
     * It is nullable because entities may not have an ID until they are created or persisted.
     *
     * @var int|null
     */
    public ?int $id = null;

    /**
     * Gets the ID of the entity.
     *
     * This method retrieves the unique identifier for the entity.
     * If the entity has not been assigned an ID (e.g., it has not been persisted), it returns null.
     * 
     * @return int|null The entity's ID, or null if it hasn't been set.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Sets the ID of the entity.
     *
     * This method allows you to assign or update the entity's ID. 
     * It is typically used when the entity is being saved or updated in the database.
     * 
     * @param int|null $id The ID to set for the entity. It may be null to signify a new entity without a set ID.
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * Converts the entity into an associative array.
     *
     * This method serializes the entity’s properties into an array format. This is useful for 
     * purposes such as rendering the entity in templates, API responses, or when interacting with 
     * the WordPress database or other external systems.
     * 
     * @return array An associative array representation of the entity's public properties.
     */
    public function toArray(): array
    {
       // Use JSON encoding/decoding to convert the object into an array
       // This approach ensures all properties, even those with private visibility, are serialized.
       $array = json_decode(json_encode($this), true);
       return $array;
    }
}
