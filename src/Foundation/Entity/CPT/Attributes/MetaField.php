<?php

namespace ModulesPress\Foundation\Entity\CPT\Attributes;

use Attribute;
use ModulesPress\Foundation\Entity\CPT\Field;

/**
 * The MetaField attribute class is used to define a meta field for a custom post type (CPT) entity.
 * This attribute is applied to public properties within a CPT entity class to mark them as meta fields.
 * 
 * The meta field has additional functionality to support custom serialization and deserialization
 * as well as a default value and a key for storing the meta field value in the WordPress database.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class MetaField extends Field
{
    /**
     * The default value for the meta field. This value is used if no value is provided.
     *
     * @var mixed
     */
    public mixed $default;

    /**
     * The key for the meta field. This key is used as the identifier when storing the value in the WordPress database.
     *
     * @var string
     */
    public string $key;

    /**
     * A callable or a value that determines how the meta field value should be serialized
     * before storing it in the database. If not provided, no serialization is done.
     *
     * @var mixed
     */
    public mixed $serialize;

    /**
     * A callable or a value that determines how the meta field value should be deserialized
     * when retrieving it from the database. If not provided, no deserialization is done.
     *
     * @var mixed
     */
    public mixed $deserialize;

    /**
     * Constructor to initialize the meta field attributes.
     *
     * @param mixed $default The default value for the meta field.
     * @param string $key The key for the meta field used when storing the value.
     * @param mixed $serialize A callable or value for serializing the value before saving.
     * @param mixed $deserialize A callable or value for deserializing the value when retrieving.
     */
    public function __construct(
         mixed $default = null,
         string $key = "",
         mixed $serialize = null,
         mixed $deserialize = null
    ) {
        $this->default = $default;
        $this->key = $key;
        $this->serialize = $serialize;
        $this->deserialize = $deserialize;
        parent::__construct($default);
    }

    /**
     * Get the key for the meta field.
     *
     * @return string The key for the meta field.
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Get the serialized value for the meta field. If a custom serialize function is provided,
     * it will be used to transform the value before storing it.
     *
     * @param mixed $value The value to be serialized.
     * @return mixed The serialized value.
     */
    public function getSerializedValue($value)
    {
        if (is_callable($this->serialize)) {
            return call_user_func($this->serialize, $value);
        }

        return $value;
    }

    /**
     * Get the deserialized value for the meta field. If a custom deserialize function is provided,
     * it will be used to transform the value when retrieving it from the database.
     *
     * @param mixed $value The value to be deserialized.
     * @return mixed The deserialized value.
     */
    public function getDeserializedValue($value)
    {
        if (is_callable($this->deserialize)) {
            return call_user_func($this->deserialize, $value);
        }

        return $value;
    }
}
