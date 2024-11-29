<?php

namespace ModulesPress\Foundation\Entity\CPT\Repositories;

use ReflectionClass;
use ReflectionProperty;
use WP_Post;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ValidatorBuilder;

use ModulesPress\Foundation\Entity\CPT\Attributes\MetaField;
use ModulesPress\Foundation\Entity\CPT\Attributes\TaxonomyField;
use ModulesPress\Foundation\Entity\CPT\CPTEntity;
use ModulesPress\Foundation\Entity\CPT\Contracts\CPTRepositoryContract;
use ModulesPress\Common\Exceptions\FrameworkException\ModuleResolutionException;
use ModulesPress\Common\Exceptions\FrameworkException\ValidationException;

/**
 * Abstract class for Custom Post Type repositories.
 * 
 * @template T of CPTEntity
 * @implements CPTRepositoryContract<T>
 */
abstract class CPTRepository implements CPTRepositoryContract
{
    /**
     * The entity class name associated with the repository.
     * 
     * @var class-string<T>
     */
    protected string $entityClassName;

    /**
     * The post type associated with the repository.
     * 
     * @var string
     */
    protected string $postType;

    /**
     * Reflection class instance for the repository class.
     * 
     * @var ReflectionClass
     */
    protected ReflectionClass $repositoryClassReflection;

    /**
     * Meta fields associated with the entity.
     * 
     * @var MetaField[]
     */
    protected array $metaFields = [];

    /**
     * Taxonomy fields associated with the entity.
     * 
     * @var TaxonomyField[]
     */
    protected array $taxonomyFields = [];

    /**
     * Validator for validating entities.
     * 
     * @var ValidatorInterface
     */
    protected ValidatorInterface $validator;

    /**
     * Constructor for the repository class.
     * 
     * @param class-string<T> $entityClassName The class name of the entity.
     */
    public function __construct(string $entityClassName)
    {
        $this->entityClassName = $entityClassName;
        $this->postType = $entityClassName::getPostType();
        $this->repositoryClassReflection = new ReflectionClass($this);

        $this->metaFields = $entityClassName::getMetaFields();
        $this->taxonomyFields = $entityClassName::getTaxonomyFields();

        $this->validator = $this->createValidator();

        $this->validateRepositoryOnInit();
    }

    /**
     * Finds an entity by its ID.
     * 
     * @param int $id The ID of the entity to find.
     * 
     * @return T|null The entity if found, or null if not.
     */
    public function find(int $id): ?CPTEntity
    {
        $post = get_post($id);
        if (!$post || $post->post_type !== $this->postType) {
            return null;
        }

        return $this->mapEntity($post);
    }

    /**
     * Finds entities by criteria.
     * 
     * @param array $criteria The criteria to filter entities by.
     * 
     * @return T[] An array of entities that match the criteria.
     */
    public function findBy(array $criteria): array
    {
        $args = array_merge($criteria, [
            'post_type' => $this->postType,
            'posts_per_page' => -1,
        ]);

        $posts = get_posts($args);
        return array_map([$this, 'mapEntity'], $posts);
    }

    /**
     * Finds all entities with optional ordering.
     * 
     * @param string $order The order to sort the entities by (ASC or DESC).
     * @param string $orderBy The field to order the entities by.
     * 
     * @return T[] An array of all entities, sorted by the specified order and field.
     */
    public function findAll(string $order = 'ASC', string $orderBy = 'ID'): array
    {
        return $this->findBy([
            'orderby' => $orderBy,
            'order' => $order,
        ]);
    }

    /**
     * Saves an entity, either by inserting or updating it.
     * 
     * @param T $entity The entity to save.
     * 
     * @return T The saved entity.
     */
    public function save(CPTEntity $entity): CPTEntity
    {
        $postData = [
            'post_title' => $entity->title,
            'post_content' => $entity->content,
            'post_excerpt' => $entity->excerpt,
            'post_type' => $this->postType,
            'post_status' => 'publish',
        ];

        $entity = $this->stabilizeEntityBeforeSave($entity, $postData);
        $entity = $this->transformEntityBeforeSave($entity, $postData);
        $entity = $this->validateEntityBeforeSave($entity, $postData);

        if ($entity->getId()) {
            $postData['ID'] = $entity->getId();
            $postId = wp_update_post($postData);
        } else {
            $postId = wp_insert_post($postData, true);
            if ($postId instanceof \WP_Error) {
                throw new \RuntimeException($postId->get_error_message());
            }
            $entity->setId($postId);
        }

        $this->saveMetaFields($entity);
        $this->saveTaxonomyFields($entity);

        return $entity;
    }

    /**
     * Removes an entity.
     * 
     * @param T $entity The entity to remove.
     * 
     * @return void
     */
    public function remove(CPTEntity $entity): void
    {
        if ($entity->getId()) {
            wp_delete_post($entity->getId(), true);
        }
    }

    /**
     * Validates the repository on initialization.
     * 
     * @throws ModuleResolutionException If the repository or entity is invalid.
     * 
     * @return void
     */
    protected function validateRepositoryOnInit()
    {
        $repoClassName = $this->repositoryClassReflection->getName();

        if (!is_subclass_of($this->entityClassName, CPTEntity::class)) {
            throw (new ModuleResolutionException(reason: "The provided entity must be a subclass of CPTEntity."))->forClass(CPTEntity::class);
        }

        if (!post_type_exists($this->postType)) {
            throw (new ModuleResolutionException(
                reason: "The provided post type entity '{$this->postType}' does not exist. Make sure it's registered within the module's entities before using it in '$repoClassName' Repository."
            ))->forClass($this->entityClassName);
        }
    }

    /**
     * Creates and returns a new validator instance.
     * 
     * @return ValidatorInterface The validator instance.
     */
    protected function createValidator(): ValidatorInterface
    {
        $validator = new ValidatorBuilder();
        $validator = $validator->enableAttributeMapping()->getValidator();
        return $validator;
    }

    /**
     * Saves the meta fields of an entity.
     * 
     * @param T $entity The entity to save meta fields for.
     * 
     * @return void
     */
    protected function saveMetaFields(CPTEntity $entity): void
    {
        foreach ($this->metaFields as $fieldName => $metaField) {
            $value = $entity->$fieldName;
            $value = $metaField->getSerializedValue($value);
            $metaFieldKey = $this->getMetaFieldKey($metaField, $fieldName);
            update_post_meta($entity->getId(), $metaFieldKey, $value);
        }
    }

    /**
     * Saves the taxonomy fields of an entity.
     * 
     * @param T $entity The entity to save taxonomy fields for.
     * 
     * @return void
     */
    protected function saveTaxonomyFields(CPTEntity $entity): void
    {
        foreach ($this->taxonomyFields as $fieldName => $taxonomyField) {

            $taxonomy = $taxonomyField->getTaxonomy();

            $values = $entity->$fieldName;
            $terms = [];

            foreach ($values as $value) {
                $term = term_exists($value,  $taxonomy->slug);
                if ($term !== 0 && $term !== null) {
                    $terms[] = $term['term_id'];
                } else {
                    $term = wp_insert_term($value,  $taxonomy->slug);
                    $terms[] = $term['term_id'];
                }
            }
            wp_set_post_terms($entity->getId(), $terms,  $taxonomy->slug);
        }
    }

    /**
     * Retrieves the metadata for an entity.
     * 
     * @param T $entity The entity to retrieve metadata for.
     * 
     * @return array The metadata associated with the entity.
     */
    protected function getMetadata(CPTEntity $entity): array
    {
        $postMeta = get_post_meta($entity->getId(), '', true);
        $parsedMeta = array_map(function ($metaInput) {
            $meta = $metaInput[0];
            if (is_serialized($meta)) {
                $unserialized = unserialize($meta);
                return $unserialized;
            } else {
                return $meta;
            }
        }, $postMeta);
        return $parsedMeta;
    }

    /**
     * Retrieves the terms associated with a taxonomy for an entity.
     * 
     * @param T $entity The entity to retrieve terms for.
     * @param string $taxonomy The taxonomy slug.
     * 
     * @return string[] The terms associated with the taxonomy.
     */
    protected function getTaxonomyTerms(CPTEntity $entity, string $taxonomy): array
    {
        $terms = wp_get_post_terms($entity->getId(), $taxonomy);
        return array_map(function ($term) {
            return $term->name;
        }, $terms);
    }

    /**
     * Maps a WP_Post object to a CPT entity.
     * 
     * @param WP_Post $post The WP_Post object to map.
     * 
     * @return T The mapped CPT entity.
     */
    protected function mapEntity(WP_Post $post): CPTEntity
    {
        $entity = $this->mapPostDataToEntity($post);
        $entity = $this->mapMetaToEntity($post, $entity);
        $entity = $this->mapTaxonomyToEntity($post, $entity);
        return $entity;
    }

     /**
     * Maps post data to an entity.
     * 
     * @param WP_Post $post The WP_Post object to map.
     * 
     * @return T The mapped CPT entity.
     */
    protected function mapPostDataToEntity(WP_Post $post): CPTEntity
    {
        /** @var T $entity */
        $entity = new $this->entityClassName();
        $entity->setId($post->ID);
        $entity->title = $post->post_title;
        $entity->content = $post->post_content;
        $entity->excerpt = $post->post_excerpt;
        return $entity;
    }

    /**
     * Maps meta data to an entity.
     * 
     * @param WP_Post $post The WP_Post object to map.
     * @param T $entity The entity to map data to.
     * 
     * @return T The entity with mapped meta data.
     */
    protected function mapMetaToEntity(WP_Post $post, CPTEntity $entity): CPTEntity
    {
        $meta = $this->getMetadata($entity);
        foreach ($this->metaFields as $fieldName => $metaField) {
            $metaFieldKey = $this->getMetaFieldKey($metaField, $fieldName);
            $value = $this->normalizeFieldValue($metaField, $fieldName, $meta[$metaFieldKey]);
            if ($value !== "_mp_skip_") {
                $value = $metaField->getDeserializedValue($value);
                $entity->$fieldName = $value;
            }
        }
        return $entity;
    }

     /**
     * Maps taxonomy data to an entity.
     * 
     * @param WP_Post $post The WP_Post object to map.
     * @param T $entity The entity to map data to.
     * 
     * @return T The entity with mapped taxonomy data.
     */
    protected function mapTaxonomyToEntity(WP_Post $post, CPTEntity $entity): CPTEntity
    {
        foreach ($this->taxonomyFields as $fieldName => $taxonomyField) {
            $value = $this->getTaxonomyTerms($entity, $taxonomyField->getTaxonomy()->slug);
            $value = $this->normalizeFieldValue($taxonomyField, $fieldName, $value);
            if ($value !== "_mp_skip_") {
                $entity->$fieldName = $value;
            }
        }
        return $entity;
    }

    /**
     * Stabilizes an entity before saving it, ensuring all required fields are present.
     * 
     * @param T $entity The entity to stabilize.
     * @param array $postData The data to stabilize.
     * 
     * @return T The stabilized entity.
     */
    protected function stabilizeEntityBeforeSave(CPTEntity $entity, array $postData): CPTEntity
    {
        $missingFields = $this->getMissingFields($entity);
        foreach ($missingFields as $missingFieldName => $missingField) {
            $value = $this->normalizeFieldValue($missingField, $missingFieldName, null);
            if ($value !== "_mp_skip_") {
                $entity->$missingFieldName = $value;
            }
        }
        return $entity;
    }

    /**
     * Transforms an entity before saving it, allowing for modification.
     * 
     * @param T $entity The entity to transform.
     * @param array $postData The data to transform.
     * 
     * @return T The transformed entity.
     */
    protected function transformEntityBeforeSave(CPTEntity $entity, array $postData): CPTEntity
    {
        return $entity;
    }

    /**
     * Validates an entity before saving it.
     * 
     * @param T $entity The entity to validate.
     * @param array $postData The data to validate.
     * 
     * @throws ValidationException If the entity is not valid.
     * 
     * @return T The validated entity.
     */
    protected function validateEntityBeforeSave(CPTEntity $entity, array $postData): CPTEntity
    {
        $errors = $this->validator->validate($entity);
        if (count($errors) > 0) {
            $validationException = new ValidationException();
            foreach ($errors as $error) {
                $validationException->attachError($error->getPropertyPath(), $error->getMessage());
            }
            throw $validationException;
        }
        return $entity;
    }

    /**
     * Retrieves any missing fields for an entity.
     * 
     * @param T $entity The entity to check for missing fields.
     * 
     * @return array An array of missing fields.
     */
    protected function getMissingFields(CPTEntity $entity): array
    {
        $missingFields = [];
        foreach (
            [
                ...$this->metaFields,
                ...$this->taxonomyFields,
            ] as $fieldName => $field
        ) {
            if (!isset($entity->$fieldName)) {
                $missingFields[$fieldName] = $field;
            }
        }
        return $missingFields;
    }

    /**
     * Normalizes a field value, providing defaults or handling null values.
     * 
     * @param MetaField|TaxonomyField $field The field to normalize.
     * @param string $fieldName The name of the field.
     * @param mixed $fieldValue The current value of the field.
     * 
     * @return mixed The normalized field value.
     */
    protected function normalizeFieldValue(MetaField | TaxonomyField $field, string $fieldName, mixed $fieldValue)
    {
        if ($fieldValue !== null) {
            return $fieldValue;
        } else {
            if ($field->getDefaultValue()) {
                return $field->getDefaultValue();
            } else if ($this->fieldAllowsNull($fieldName)) {
                return null;
            }
        }
        return "_mp_skip_";
    }

     /**
     * Checks if a field allows null values.
     * 
     * @param string $propertyName The property name to check.
     * 
     * @return bool True if the field allows null values, false otherwise.
     */
    private function fieldAllowsNull(string $propertyName): bool
    {
        $propertyReflection = new ReflectionProperty($this->entityClassName, $propertyName);
        return $propertyReflection->getType()->allowsNull();
    }

    /**
     * Retrieves the meta field key for a given meta field.
     *
     * @param MetaField $metaField The meta field to get the key for.
     * @param string $fieldName The name of the field.
     *
     * @return string The meta field key.
     */
    private function getMetaFieldKey(MetaField $metaField, string $fieldName): string
    {
        return empty(!$metaField->getKey()) ? $metaField->getKey() : $fieldName;
    }
}
