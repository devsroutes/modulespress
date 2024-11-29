<?php

namespace ModulesPress\Foundation\Entity\CPT\Contracts;

use ModulesPress\Foundation\Entity\CPT\CPTEntity;

/**
 * The CPTRepositoryContract defines the interface for the repository responsible
 * for managing and interacting with instances of `CPTEntity` and its derived classes.
 * This contract provides methods for CRUD operations and querying custom post types (CPT) entities.
 */
interface CPTRepositoryContract
{
    /**
     * Find a custom post type entity by its unique ID.
     * 
     * @param int $id The ID of the CPT entity to find.
     * 
     * @return CPTEntity|null The found CPT entity, or null if not found.
     */
    public function find(int $id): ?CPTEntity;

    /**
     * Find custom post type entities based on given criteria.
     * 
     * @param array $criteria An associative array of criteria to filter the results.
     * 
     * @return CPTEntity[] An array of CPT entities matching the criteria.
     */
    public function findBy(array $criteria): array;

    /**
     * Find all custom post type entities, with optional sorting.
     * 
     * @param string $order The order in which to retrieve the entities (ASC or DESC).
     * @param string $orderBy The field by which to order the results.
     * 
     * @return CPTEntity[] An array of all CPT entities.
     */
    public function findAll(string $order = 'ASC', string $orderBy = 'ID'): array;

    /**
     * Save a custom post type entity, either creating or updating it.
     * 
     * @param CPTEntity $entity The CPT entity to save.
     * 
     * @return CPTEntity The saved CPT entity.
     */
    public function save(CPTEntity $entity): CPTEntity;

    /**
     * Remove a custom post type entity from the repository.
     * 
     * @param CPTEntity $entity The CPT entity to remove.
     * 
     * @return void
     */
    public function remove(CPTEntity $entity): void;
}
