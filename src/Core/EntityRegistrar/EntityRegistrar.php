<?php

namespace ModulesPress\Core\EntityRegistrar;

use ModulesPress\Foundation\Entity\CPT\Attributes\CustomPostType;
use ModulesPress\Foundation\Entity\CPT\Attributes\MetaField;
use ModulesPress\Foundation\Entity\CPT\Attributes\Taxonomy;
use ModulesPress\Foundation\Entity\CPT\Attributes\TaxonomyField;
use ModulesPress\Foundation\Entity\CPT\CPTEntity;
use ModulesPress\Common\Exceptions\FrameworkException\ModuleResolutionException;
use ModulesPress\Core\Core;

/**
 * The `EntityRegistrar` class is responsible for registering and managing custom post types (CPTs) 
 * and taxonomies in WordPress based on defined attributes within modules.
 * It handles initializing and registering entities during the WordPress lifecycle.
 */
final class EntityRegistrar
{
    /**
     * @var CustomPostType[] An array of custom post type entities.
     */
    private array $cpts = [];

    /**
     * @var array<string, Taxonomy[]> An associative array mapping custom post type names to taxonomies.
     */
    private array $taxonomies = [];

    /**
     * Constructor for the `EntityRegistrar` class, accepts the core system for dependency injection.
     *
     * @param Core $core The core system instance.
     */
    public function __construct(
        private readonly Core $core,
    ) {}

    /**
     * Registers required hooks for initializing entities.
     * 
     * @return EntityRegistrar The current instance for method chaining.
     */
    public function registerRequiredHooks(): EntityRegistrar
    {
        add_action('init', array($this, 'entitiesInitialize'), 1);
        return $this;
    }

    /**
     * Registers entities (CPTs and taxonomies) from resolved modules.
     *
     * @return EntityRegistrar The current instance for method chaining.
     * @throws ModuleResolutionException If an entity class is not a subclass of `CPTEntity`.
     */
    public function registerEntities(): EntityRegistrar
    {
        foreach ($this->core->getPluginContainer()->getResolvedModules() as $resolvedModule) {
            foreach ($resolvedModule->getEntites() as $entityClassName) {
                if (is_subclass_of($entityClassName, CPTEntity::class)) {

                    $cpt = $this->createCPTInstanceFromClass($entityClassName);
                    $taxonomies = array_map(fn($taxonomyField) => $taxonomyField->getTaxonomy(), $entityClassName::getTaxonomyFields());

                    $this->cpts[] = $cpt;

                    foreach ($taxonomies as $taxonomy) {
                        $this->taxonomies[$cpt->name][] = $taxonomy;
                    }
                } else {
                    throw (new ModuleResolutionException(reason: "Entity class '$entityClassName' must be subclass of CPTEntity"))->forClass($entityClassName);
                }
            }
        }
        return $this;
    }

    /**
     * Initializes entities (CPTs and taxonomies) by calling the appropriate registration methods.
     */
    public function entitiesInitialize()
    {
        foreach ($this->cpts as $cpt) {
            $this->registerCPT($cpt);
        }

        foreach ($this->taxonomies as $cptName => $taxonomies) {
            $this->registerTaxonomiesForCPT($cptName, $taxonomies);
        }
    }

    /**
     * Registers a custom post type (CPT).
     *
     * @param CustomPostType $cpt The CPT to register.
     */
    private function registerCPT(CustomPostType $cpt)
    {
        // Extracting singular and plural names for labels
        $labels = [
            'name'               => _x($cpt->plural, 'post type general name', 'textdomain'),
            'singular_name'      => _x($cpt->singular, 'post type singular name', 'textdomain'),
            'menu_name'          => _x($cpt->plural, 'admin menu', 'textdomain'),
            'name_admin_bar'     => _x($cpt->singular, 'add new on admin bar', 'textdomain'),
            'add_new'            => __('Add New', 'textdomain'),
            'add_new_item'       => __('Add New ' . $cpt->singular, 'textdomain'),
            'new_item'           => __('New ' . $cpt->singular, 'textdomain'),
            'edit_item'          => __('Edit ' . $cpt->singular, 'textdomain'),
            'view_item'          => __('View ' . $cpt->singular, 'textdomain'),
            'all_items'          => __('All ' . $cpt->plural, 'textdomain'),
            'search_items'       => __('Search ' . $cpt->plural, 'textdomain'),
            'parent_item_colon'  => __('Parent ' . $cpt->plural . ':', 'textdomain'),
            'not_found'          => __('No ' . $cpt->plural . ' found.', 'textdomain'),
            'not_found_in_trash' => __('No ' . $cpt->plural . ' found in Trash.', 'textdomain'),
        ];

        // Merge default args with custom args passed to CustomPostType
        $args = array_merge([
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => ['slug' => $cpt->name],
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => ['title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments']
        ], $cpt->args);

        // Register the post type
        register_post_type($cpt->name, $args);
    }

    /**
     * Registers taxonomies for a given CPT.
     *
     * @param string $cptName The custom post type name.
     * @param Taxonomy[] $taxonomies An array of taxonomies to register.
     */
    private function registerTaxonomiesForCPT(string $cptName, array $taxonomies)
    {
        foreach ($taxonomies as $taxonomy) {
            if (!taxonomy_exists($taxonomy->slug)) {
                // Taxonomy does not exist, so register it
                $labels = [
                    'name'              => _x($taxonomy->plural, 'taxonomy general name', 'textdomain'),
                    'singular_name'     => _x($taxonomy->singular, 'taxonomy singular name', 'textdomain'),
                    'search_items'      => __('Search ' . $taxonomy->plural, 'textdomain'),
                    'all_items'         => __('All ' . $taxonomy->plural, 'textdomain'),
                    'parent_item'       => __('Parent ' . $taxonomy->singular, 'textdomain'),
                    'parent_item_colon' => __('Parent ' . $taxonomy->singular . ':', 'textdomain'),
                    'edit_item'         => __('Edit ' . $taxonomy->singular, 'textdomain'),
                    'update_item'       => __('Update ' . $taxonomy->singular, 'textdomain'),
                    'add_new_item'      => __('Add New ' . $taxonomy->singular, 'textdomain'),
                    'new_item_name'     => __('New ' . $taxonomy->singular . ' Name', 'textdomain'),
                    'menu_name'         => _x($taxonomy->plural, 'admin menu', 'textdomain'),
                ];

                $args = array_merge([
                    'labels'            => $labels,
                    'public'            => true,
                    'hierarchical'      => true, // Hierarchical or flat taxonomy
                    'show_ui'           => true,
                    'show_admin_column' => true,
                    'query_var'         => true,
                    'rewrite'           => ['slug' => $taxonomy->slug],
                ], $taxonomy->args);

                // Register the taxonomy
                register_taxonomy($taxonomy->slug, [$cptName], $args);
            } else {
                // Taxonomy exists, ensure it is associated with the CPT
                $foundTaxonomies = get_object_taxonomies($cptName, 'object');
                if (count($foundTaxonomies) === 0) {
                    // Taxonomy is not associated with this post type, add it
                    register_taxonomy_for_object_type($taxonomy->slug, $cptName);
                }
            }
        }
    }

    /**
     * Creates an instance of a Custom Post Type (CPT) from a class.
     *
     * @param string $entity_class_name The entity class name.
     * @return CustomPostType The created CPT instance.
     * @throws ModuleResolutionException If the class is not valid or does not have the proper attributes.
     */
    private function createCPTInstanceFromClass(string $entity_class_name): CustomPostType
    {
        $entity_reflection_class = new \ReflectionClass($entity_class_name);
        $entity_attributes = $entity_reflection_class->getAttributes(CustomPostType::class);

        if (empty($entity_attributes)) {
            throw (new ModuleResolutionException(reason: "Entity class '$entity_class_name' must have a 'CustomPostType' attribute"))->forClass($entity_class_name);
        }
        if (!is_subclass_of($entity_class_name, CPTEntity::class)) {
            throw (new ModuleResolutionException(reason: "Entity class '" . $entity_class_name . "' must extend '" . CPTEntity::class . "'"))->forClass($entity_class_name);
        }

        foreach ($entity_reflection_class->getProperties() as $property) {

            $defaults = ["id", "title", "content", "excerpt"];
            if (in_array($property->getName(), $defaults)) {
                continue;
            }

            $attributes = $property->getAttributes();

            if (empty($attributes)) {
                throw (new ModuleResolutionException(reason: "Entity class '" . $entity_class_name . "' Property '" . $property->getName() . "' must have a atleast one valid attribute"))->forClass($entity_class_name);
            }

            foreach ($attributes as $attribute) {
                switch ($attribute->getName()) {
                    case MetaField::class:
                        break;
                    case TaxonomyField::class:
                        $taxonomy_class_name = $attribute->newInstance()->taxonomy;
                        $taxonomyAttributes = (new \ReflectionClass($taxonomy_class_name))->getAttributes(Taxonomy::class);
                        if (empty($taxonomyAttributes)) {
                            throw (new ModuleResolutionException(reason: "Taxonomy class '" . $taxonomy_class_name . "' must have a 'Taxonomy' attribute"))->forClass($taxonomy_class_name);
                        }
                        break;
                }
            }
        }

        $entity_instance = $entity_attributes[0]->newInstance();
        return $entity_instance;
    }
}
