<?php

namespace Drupal\entities_info;

/**
 * Interface EntitiesInfoManagerInterface.
 */
interface EntitiesInfoManagerInterface {

  /**
   * Get private tempstore for entities info export.
   *
   * @return \Drupal\Core\TempStore\PrivateTempStore
   *   PrivateTempStore of entities info export.
   */
  public function getEntitiesInfoTempstore();

  /**
   * Get values from tempstore entities info export.
   *
   * @param $entitiesInfoTempstore
   *   PrivateTempStore of entities info export.
   *
   * @return mixed
   *   Values from PrivateTempStore entities info export.
   */
  public function getValues($entitiesInfoTempstore);

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
   * @param $entity
   *   Entity.
   * @param $bundle
   *   Bundle.
   *
   * @return array|int
   *   Number of elements.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getCountBundle($entity, $bundle);

}
