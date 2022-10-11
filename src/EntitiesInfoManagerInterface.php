<?php

namespace Drupal\entities_info;

use Drupal\Core\TempStore\PrivateTempStore;
use Drupal\field\Entity\FieldConfig;

/**
 * Provides an interface for entities info manager.
 */
interface EntitiesInfoManagerInterface {

  /**
   * Get private tempstore for entities info export.
   *
   * @return \Drupal\Core\TempStore\PrivateTempStore
   *   PrivateTempStore of entities info export.
   */
  public function getEntitiesInfoTempstore(): PrivateTempStore;

  /**
   * Get values from tempstore entities info export.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStore $entitiesInfoTempstore
   *   PrivateTempStore of entities info export.
   *
   * @return mixed
   *   Values from PrivateTempStore entities info export.
   */
  public function getValues(PrivateTempStore $entitiesInfoTempstore): mixed;

  /**
   * Get FieldConfigs created from entities and items count.
   *
   * @param array $entitiesInfoValues
   *   Entities.
   *
   * @return array
   *   Array with fields by entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getEntitiesFields(array $entitiesInfoValues): array;

  /**
   * Create table render array for every entity.
   *
   * @param array $entities
   *   Entities with fields.
   *
   * @return array|array[]
   *   Table render array for every entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function createTables(array $entities): array;

  /**
   * Number of elements created by bundle.
   *
   * @param string $entity
   *   Entity type.
   * @param string $bundle
   *   Bundle.
   *
   * @return array|int
   *   Number of elements.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getCountBundle(string $entity, string $bundle): int|array;

  /**
   * Number of fields used.
   *
   * @param \Drupal\field\Entity\FieldConfig $field
   *   Field.
   *
   * @return string
   *   Number of elements.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getCountField(FieldConfig $field): string;

}
