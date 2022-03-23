<?php

namespace Drupal\entities_info\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\field\Entity\FieldConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EntitiesExportController.
 */
class EntitiesInfoExportController extends ControllerBase {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Drupal\Core\Entity\EntityFieldManagerInterface definition.
   */
  protected EntityFieldManagerInterface $entityFieldManager;

  /**
   * Drupal\Core\TempStore\PrivateTempStoreFactory definition.
   */
  private PrivateTempStoreFactory $tempStoreFactory;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): EntitiesInfoExportController {
    $instance = parent::create($container);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->entityFieldManager = $container->get('entity_field.manager');
    $instance->tempStoreFactory = $container->get('tempstore.private');
    return $instance;
  }

  /**
   * Export entities info.
   *
   * @return array
   *   Return render array.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function export(): array {

    $tempstore = $this->tempStoreFactory->get('entities_info_export');
    $params = $tempstore->get('values');

    $entities_fields = $this->getEntitiesFields($params);
    $tables = $this->createTables($entities_fields);

    return [
      '#theme' => 'entities_info',
      '#tables' => $tables,
    ];

  }

  /**
   * Get FieldConfigs from entities.
   *
   * @param array $params
   *   Entities.
   *
   * @return array
   *   Array with fields by entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getEntitiesFields(array $params): array {
    return array_map(function ($item) {

      [$bundle, $entity_id] = explode('-', $item);
      $entity_id_of = $this->entityTypeManager->getDefinition($entity_id)->getBundleOf();
      $entity_id = $entity_id_of != FALSE ? $entity_id_of : $entity_id;

      $fields = $this->entityFieldManager->getFieldDefinitions($entity_id, $bundle);
      $fields = array_filter($fields, fn($field) => $field instanceof FieldConfig);

      if (!$fields) {
        return FALSE;
      }

      return array_map(function ($field) {
        return [
          'field_name' => $field->getName(),
          'label' => $field->getLabel(),
          'field_type' => $field->getType(),
          'required' => $field->isRequired() == 1 ? 'Yes' : 'No',
          'description' => $field->getDescription(),
        ];
      }, $fields);

    }, $params);
  }

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
  public function createTables(array $entities): array {
    return array_map(function ($index, $entity) {

      [$bundle, $entity_id] = explode('-', $index);
      $label = $this->entityTypeManager->getStorage($entity_id)->load($bundle)->label();

      if ($entity == FALSE) {
        return [
          '#name' => $label,
          '#markup' => '<p>'.$this->t('There is not fields created.').'</p>'
        ];
      }

      $header = [
        'field_name' => t('Field name'),
        'label' => t('Label'),
        'field_type' => t('Field type'),
        'required' => t('Required'),
        'description' => t('Description'),
      ];

      $rows = array_map(function ($field) {
        return [
          $field['field_name'],
          $field['label'],
          $field['field_type'],
          $this->t($field['required']),
          $field['description'],
        ];
      }, $entity);

      return [
        '#type' => 'table',
        '#header' => $header,
        '#rows' => $rows,
        '#name' => $label,
      ];
    }, array_keys($entities), $entities);
  }

}
