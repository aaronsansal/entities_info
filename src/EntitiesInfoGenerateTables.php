<?php

namespace Drupal\entities_info;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Service description.
 */
class EntitiesInfoGenerateTables implements EntitiesInfoGenerateTablesInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a GenerateTables object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function createTable(string $entity_id, string $bundle, array $fields): array {
    $label = $this->entityTypeManager->getStorage($entity_id)->load($bundle)->label();
    $count = t('Count items:') . $fields['count'];

    if (count($fields) === 1 && array_key_exists('count', $fields)) {
      return [
        '#name' => $label,
        '#count' => $count,
        '#markup' => '<p>' . t('There is not fields created.') . '</p>',
      ];
    }

    unset($fields['count']);
    $rows = $this->getTableRows($fields);

    return [
      '#type' => 'table',
      '#header' => $this->getTableHeaders(),
      '#rows' => $rows,
      '#name' => $label,
      '#count' => $count,
    ];
  }

  /**
   * Return table rows with field info.
   *
   * @param array $entity
   *   Entity with field configs.
   *
   * @return array
   *   Array with field info.
   */
  protected function getTableRows(array $entity): array {
    return array_map(function ($field) {
      return [
        $field['field_name'],
        $field['label'],
        $field['field_type'],
        $field['required'],
        $field['description'],
        $field['count_used'],
      ];
    }, $entity);
  }

  /**
   * Table headers.
   *
   * @return array
   *   Field information labels.
   */
  protected function getTableHeaders(): array {
    return [
      'field_name' => t('Field name'),
      'label' => t('Label'),
      'field_type' => t('Field type'),
      'required' => t('Required'),
      'description' => t('Description'),
      'count_used' => t('Count field use'),
    ];
  }

}
