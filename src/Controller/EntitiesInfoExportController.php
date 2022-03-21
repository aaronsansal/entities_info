<?php

namespace Drupal\entities_info\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\field\Entity\FieldConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EntitiesExportController.
 */
class EntitiesInfoExportController extends ControllerBase {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Drupal\Core\Entity\EntityFieldManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Drupal\Core\TempStore\PrivateTempStoreFactory definition.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  private $tempStoreFactory;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
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
   */
  public function createTables(array $entities): array {
    return array_map(function ($index, $entity) {

      $header = [
        'field_name' => t('Field name'),
        'label' => t('Label'),
        'field_type' => t('Field type'),
        'required' => t('Required'),
        'description' => t('Description'),
      ];

      $rows = [];
      foreach ($entity as $field) {
        $rows[] = [
          $field['field_name'],
          $field['label'],
          $field['field_type'],
          $field['required'],
          $field['description'],
        ];
      }

      [$bundle, $entity_id] = explode('-', $index);
      $label = $this->entityTypeManager->getStorage($entity_id)->load($bundle)->label();

      return [
        '#type' => 'table',
        '#header' => $header,
        '#rows' => $rows,
        '#name' => $label,
      ];
    }, array_keys($entities), $entities);
  }

}
