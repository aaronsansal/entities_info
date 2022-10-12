<?php

namespace Drupal\entities_info;

/**
 * Provides an interface for entities info manager.
 */
interface EntitiesInfoGenerateTablesInterface {

  /**
   * Create table render array for every entity.
   *
   * @param array $fields
   *   Entities with fields.
   *
   * @return array|array[]
   *   Table render array for every entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function createTableEntityFields(array $fields): array;

}
