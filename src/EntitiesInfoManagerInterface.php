<?php

namespace Drupal\entities_info;

use Drupal\field\Entity\FieldConfig;

/**
 * Provides an interface for entities info manager.
 */
interface EntitiesInfoManagerInterface {

  /**
   * Get FieldConfigs created from entity and items count.
   *
   * @param string $entity_id
   *   Entity id.
   * @param string $bundle
   *   Bundle id.
   *
   * @return array|bool
   *   Array with fields by entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getEntityFields(string $entity_id, string $bundle): array|bool;

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
