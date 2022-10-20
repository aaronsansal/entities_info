<?php

namespace Drupal\entities_info;

use Drupal\field\Entity\FieldConfig;

/**
 * Provides an interface for entities info manager.
 */
interface EntitiesInfoManagerInterface {

  /**
   * @return array
   */
  public function getFieldConfigEntities(): array;

  /**
   * @param string $entity_id
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getEntityBundles(string $entity_id): array;

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
  public function getEntityFields(string $entity_id, string $bundle): array;

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

  /**
   * Number of elements created by bundle.
   *
   * @param string $entity
   *   Entity type.
   * @param string $bundle
   *   Bundle.
   *
   * @return array
   *   Number of elements.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getCountBundle(string $entity, string $bundle): string;

}
